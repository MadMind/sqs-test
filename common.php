<?php

use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;

require 'vendor/autoload.php';

$config = require __DIR__.'/config.php';

function _log(string $message)
{
    echo sprintf("%s\t%s\r\n", microtime(true), $message);
}

function createSqsClient(array $config): SqsClient
{
    $client = new SqsClient($config['sqs_config']);

    return $client;
}

function getQueueUrl(SqsClient $client, string $queueName): ?string
{
    $url = null;

    try {

        $response = $client->getQueueUrl(['QueueName' => $queueName]);
        $url = $response->get('QueueUrl');

        _log('Queue URL: '.$url);

        return $url;
    } catch (SqsException $e) {
        _log('Queue does not exist');
    }

    _log('Create queue: '.$queueName);
    $response = $client->createQueue(['QueueName' => $queueName]);
    $url = $response->get('QueueUrl');
    _log('Created queue: '.$url);

    return $url;
}


function removeSqsMessage(SqsClient $client, string $queueUrl, array $message)
{
    $client->deleteMessage(
        [
            'QueueUrl' => $queueUrl,
            'ReceiptHandle' => $message['ReceiptHandle'],
        ]
    );
    _log('Message deleted');
}
