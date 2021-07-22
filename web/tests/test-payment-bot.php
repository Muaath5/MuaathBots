<?php
$update_channel_post = json_encode(
[
    'update_id' => -1,
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
    'update_id' => -1,
    'message' =>
    [
        'message_id' => 123,
        'chat' =>
        [
            'id' => -100,
            'type' => 'channel',
            'title' => 'Muaath bots tests channel'
        ],
        'text' => '/invoice',
    ]
]));

$update_shipping_query = json_decode(json_encode(
[
    'update_id' => -1,
    'shipping_query' =>
    [
        'id' => -1,
        'payload' => 'PHP-Tests'
    ]
]));

$update_pre_checkout_query - json_decode(json_encode(
[
    'update_id' => -1,
    'pre_checkout_query' =>
    [
        'id' => -1,
        'currency' => 'SAR',
        'payload' => 'PHP-Tests',
        'total_amount' => -1
    ]
]));

include $_SERVER['DOCUMENT_ROOT'] . '/bots/TestPaymentV2Bot/bot.php';
include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/TelegramBotAPI.php';

$token = getenv('TestPayment2Bot_Token');
$Bot = new TelegramBot($token);

// Test methods, Should run and log in logs channel
$PaymentHandler = new TestPaymentV2Bot($Bot, getenv('TestPayment2Bot_Token'), getenv('TestPayment2Bot_LogsChatID'));


// Test a Telegram payment:
// 1. Request invoice
$PaymentHandler->MessageHandler($update_message);
// 2. Send a shipping query
$PaymentHandler->ShippingQueryHandler($update_shipping_query);
// 3. Send a precheckout query
$PaymentHandler->PreCheckoutQueryHandler($update_pre_checkout_query);