<?php
include 'common.php';

$client = createSqsClient($config);
$queue = getQueueUrl($client, $config['queue_name']);

while (true) {
    _log('Start reading messages');
    $response = $client->receiveMessage(
        [
            'QueueUrl' => $queue,
            'WaitTimeSeconds' => 20,
            'MaxNumberOfMessages' => 10,
        ]
    );
    _log('Response from SQS');

    if (!$response->hasKey('Messages')) {
        _log('No messages');
        continue;
    }

    $messages = $response->get('Messages');
    foreach ($messages as $message) {
        _log('Message: '.$message['Body']);
        removeSqsMessage($client, $queue, $message);
    }
    _log('---');
}
