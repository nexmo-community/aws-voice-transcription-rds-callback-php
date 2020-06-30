
CREATE DATABASE IF NOT EXISTS `voice_transcriptions` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

# Table to hold transcriptions, where each word is a record
CREATE TABLE `transcriptions` (
    `id` int NOT NULL AUTO_INCREMENT,
    `conversation_uuid` varchar(255) NOT NULL,
    `channel` varchar(255) NOT NULL,
    `start_time` varchar(10) NOT NULL,
    `end_time` varchar(10) NOT NULL,
    `content` text COLLATE utf8mb4_general_ci NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `conversation_uuid` (`conversation_uuid`),
    KEY `start_time` (`start_time`),
    KEY `channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Table to hold assembled transcriptions, where each speaker instance/sentence is a record
CREATE TABLE `conversations` (
    `id` int NOT NULL AUTO_INCREMENT,
    `conversation_uuid` varchar(255) NOT NULL,
    `channel` varchar(255) NOT NULL,
    `start_time` varchar(10) NOT NULL,
    `end_time` varchar(10) NOT NULL,
    `content` text COLLATE utf8mb4_general_ci NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `conversation_uuid` (`conversation_uuid`),
    KEY `start_time` (`start_time`),
    KEY `channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
