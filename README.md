# aws-voice-transcription-rds-callback-php
AWS Lambda function callback script to be used by CloudWatch as a callback to store Voice Transcriptions to an RDS MySQL DB

## Prerequisites

* PHP 7.4 (update `serverless.yml` for other versions)
* Composer installed [globally](https://getcomposer.org/doc/00-intro.md#globally)
* [Node.js](https://nodejs.org/en/) and npm
* [Serverless Framework](https://serverless.com/framework/docs/getting-started/)
* [AWS account](https://aws.amazon.com/)
* [Vonage account](https://vonage.com)

## Setup Instructions

Clone this repo from GitHub, and navigate into the newly created directory to proceed.

### Use Composer to install dependencies

This example requires the use of Composer to install dependencies and set up the autoloader.

Assuming a Composer global installation. [https://getcomposer.org/doc/00-intro.md#globally](https://getcomposer.org/doc/00-intro.md#globally)

```
composer install
```

### AWS Setup

You will need to create [AWS credentials](https://www.serverless.com/framework/docs/providers/aws/guide/credentials/) as indicated by `Serverless`.

Also, create a new [RDS Instance](https://aws.amazon.com/rds/) using the default settings. Make note of the ARN for later use. Use the `/data/schema.sql` contents to set up the database and table.

### Update Environment

Rename the provided `.env.default` file to `.env` and update the values as needed from `AWS`.

```env
AWS_VERSION=latest
AWS_REGION=us-east-1
AWS_VERSION=latest
AWS_RDS_URL=
AWS_RDS_DATABASE_NAME=
AWS_RDS_TABLE_NAME=
AWS_RDS_USER=
AWS_RDS_PASSWORD=
```

### Serverless Plugin

Install the [serverless-dotenv-plugin](https://www.serverless.com/plugins/serverless-dotenv-plugin/) with the following command.

```bash
npm i -D serverless-dotenv-plugin
```

### Deploy to Lambda

With all the above updated successfully, you can now use `Serverless` to deploy the app to [AWS Lambda](https://aws.amazon.com/lambda/).

```bash
serverless deploy
```

### Create Cloudwatch Trigger

After deploying this function, you can navigate to [CloudWatch](https://aws.amazon.com/cloudwatch/) in your [AWS Console](https://console.aws.amazon.com/) and select `Events` and `Get Started` to create a new Event Rule.

Set the Rule as follows:

* Event Pattern
* Build event pattern to match events by service
* Service Name = Transcribe
* Event Type = Transcribe Job State Change
* Specific status(es) = COMPLETED
* As the Target select the Lambda function #2 created above
* Scroll down and click `Configure Details`.
* Give the rule a meaningful name and description, and enable it.
* Click `Create rule` to complete it.

Now you're ready to test.

The next time a Transcribe job finishes, this function will be ran.

## Contributing

We love questions, comments, issues - and especially pull requests. Either open an issue to talk to us, or reach us on twitter: <https://twitter.com/VonageDev>.
