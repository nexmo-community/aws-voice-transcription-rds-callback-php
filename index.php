<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db.global.php';

use Aws\TranscribeService\TranscribeServiceClient;
use Doctrine\DBAL\Connection;

Dotenv\Dotenv::create(__DIR__)->load();

return function ($event) use ($conn) {

    // Create Amazon Transcribe Client
    $awsTranscribeClient = new TranscribeServiceClient([
        'region' => $_ENV['AWS_REGION'],
        'version' => $_ENV['AWS_VERSION']
    ]);

    // Retrieve the transcription job
    $transcriptionJob = $awsTranscribeClient->getTranscriptionJob([
        'TranscriptionJobName' => $event['detail']['TranscriptionJobName']
    ]);

    // parse the job to get the File Uri
    $transcriptionRawResult = $transcriptionJob->toArray();

    // get the result file from S3 URL
    $resultFile = file_get_contents($transcriptionRawResult['TranscriptionJob']['Transcript']['TranscriptFileUri']
    );

    $result = json_decode($resultFile, true);
    $startTime = '000.00';
    $endTime = '000.00';
    $conversation_uuid = '';
    $record_date = date('Y-m-d H:i:s');

    // Loop over the channels to get to items
    foreach ($result['results']['channel_labels']['channels'] as $channel) {
        $conversation_uuid = str_replace('nexmo_voice_', '', $result['jobName']);

        // loop over the items within the channel to get transcribed items
        foreach ($channel['items'] as $item) {

            // some records don't have start_time or end_items, so use the previous
            if (isset($item['start_time'])) {
                $startTime = getTimestamp($item['start_time']);
            }

            if (isset($item['end_time'])) {
                $endTime = getTimestamp($item['end_time']);
            }

            // Add contents to DB
            $conn->insert('transcriptions', [
                'conversation_uuid' => $conversation_uuid,
                'channel' => ($channel['channel_label'] == 'ch_0' ? 'caller' : 'recipient'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'content' => $item['alternatives'][0]['content'],
                'created' => $record_date,
                'modified' => $record_date
            ]);

        } // end $items

    } // end $channel

    // Save the full conversation to DB
    try {
        saveConversation($conn, $conversation_uuid);
    } catch (Exception $exception) {
        return $exception->getMessage();
    }

    echo $result['results']['transcripts'][0]['transcript'];

    return $result['results']['transcripts'][0]['transcript'];
};

/**
 * Create standard time output as 000.00 format
 *
 * @param $time
 * @return string
 */
function getTimestamp($time) {
    $startTimePieces = explode('.', $time);

    $fix_start_seconds = str_pad($startTimePieces[0], 3, '0', STR_PAD_LEFT);
    $fix_start_microseconds = str_pad($startTimePieces[1], 2, '0', STR_PAD_RIGHT);

    return $fix_start_seconds . '.' . $fix_start_microseconds;
}

function saveConversation(Connection $conn, $conversationUuid) {

    $records = $conn->fetchAll("SELECT * FROM `transcriptions` WHERE `conversation_uuid` = ? ORDER BY `start_time`, `channel`, `id` ASC", [$conversationUuid]);

    $conversation = [];
    $conversation['channel'] = '';
    $conversation['conversation_uuid'] = $conversationUuid;
    $conversation['created'] = date('Y-m-d H:i:s');
    $conversation['modified'] = date('Y-m-d H:i:s');

    foreach ($records as $record) {

        if ($conversation['channel'] != $record['channel']) {

            if (!empty($conversation['channel'])) {

                // Add contents to DB
                try {
                    $conn->insert('conversations', [
                        'conversation_uuid' => $conversationUuid,
                        'channels' => $conversation['channel'],
                        'start_time' => $conversation['start_time'],
                        'end_time' => $conversation['end_time'],
                        'content' => $conversation['content'],
                        'created' => $conversation['created'],
                        'modified' => $conversation['modified']
                    ]);
                } catch (Exception $exception) {
                    echo $exception->getMessage();
                }

            }

            $conversation['content'] = '';
            $conversation['channel'] = $record['channel'];
            $conversation['start_time'] = $record['start_time'];
        }

        $conversation['end_time'] = $record['end_time'];
        $conversation['content'] .= ' ' . $record['content'];
    }

    return true;
}
