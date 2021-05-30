<?php
$test_channel_id = getenv('TEST_CHANNEL_ID');
$test_supergroup_id = getenv('TEST_SUPERGROUP_ID');
$test_group_id = getenv('TEST_GROUP_ID');
$test_user_id = getenv('TEST_USER_ID');

$update_channel_post = json_encode(
[
    'update_id' => 123,
    'channel_post' =>
    [
        'message_id' => 123,
        'chat' =>
        [
            'id' => -100,
            'type' => 'channel',
            'title' => 'Muaath bots tests channel'
        ],
        'text' => '/start',
    ]
]);

$update_message = json_decode(json_encode(
[
    'update_id' => 123,
    'channel_post' =>
    [
        'message_id' => 123,
        'chat' =>
        [
            'id' => -100,
            'type' => 'channel',
            'title' => 'Muaath bots tests channel'
        ],
        'text' => '/start',
    ]
]));

include '/bot-api/UpdatesHandler.php';
include '/bot-api/TelegramBotAPI.php';
include '/bots/TestPaymentV2Bot/bot.php';
$Bot = new TelegramBot(getenv('TestPayment2Bot_Token'));
// Test methods, Should run and log in logs channel
$PaymentHandler = new TestPaymentV2Bot();

// Test a Telegram payment:
// 1. Request invoice
$PaymentHandler->MessageHandler($update_message);
// 2. Send a shipping query

// 3. Send a precheckout query