<?php
include 'common.php';

$client = createSqsClient($config);
$queue = getQueueUrl($client, $config['queue_name']);

_log('Send 1 message and sleep 30 sec');
$client->sendMessage(
    [
        'QueueUrl' => $queue,
        'MessageBody' => sprintf('Message 1/1 - %s', microtime(true)),
    ]
);
_log('Sent');
sleep(30);

_log('Send 5 messages and sleep 30 sec');
$result = $client->sendMessageBatch(
    [
        'Entries' => createBatch(5),
        'QueueUrl' => $queue,
    ]
);
_log('Sent');
sleep(30);

_log('Send 50 messages');
// SQS limits to 10 messages per batch send
for ($i = 0; $i < 5; $i++) {
    $result = $client->sendMessageBatch(
        [
            'Entries' => createBatch(50, $i * 10 + 1, ($i + 1) * 10),
            'QueueUrl' => $queue,
        ]
    );
}
_log('Done');

function createBatch(int $count, int $from = 1, int $to = null)
{
    if ($to === null) {
        $to = $count;
    }

    return array_map(
        function ($i) use ($count) {
            $time = microtime(true);

            return [
                'Id' => md5(sprintf('%s-%d-%d', str_replace(' ', '_', $time), $i, $count)),
                'MessageBody' => sprintf('Message %d/%d - %s', $i, $count, $time),
            ];
        },
        range($from, $to)
    );
}
