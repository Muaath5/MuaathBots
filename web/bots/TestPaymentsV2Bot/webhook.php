<?php
declare(strict_types=1);


# Configurations
define('Token', getenv('TestPayment2Bot_Token'));
define('ProviderToken', getenv('TestPayment2Bot_ProviderToken'));
define('LogsChatID', getenv('TestPayment2Bot_LogsChatID'));
define('DevUsername', getenv('DevUsername'));

$bot_admins = json_decode(getenv('TestPayment2Bot_Admins'));

# Bot settings
define('SettingsFilePath', __DIR__ . '/settings.json');
$settings = json_decode(file_get_contents(SettingsFilePath));

$contactTheDev =
[
    'inline_kayboard' =>
    [
        [
            [
                'text' => 'Contact the developer',
                'url' => "https://t.me/" . DevUsername
            ]
        ]
    ]
];
$contactTheDev = json_encode($contactTheDev);

# A Telegram library (Contains functions like SendMessage, SendInvoice, etc.)
include $_SERVER['DOCUMENT_ROOT'] . '/telegram-bot-api.php';

# Bot handlers
$bot_include_result = include __DIR__ . '/bot.php';

# Reading the update
$update = json_decode(file_get_contents('php://input'));

define('BotDirectory', basename(__DIR__));

# Check auth
if ($_GET['token'] != Token)
{
    include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-unauthorized.php';
    exit;
}

if (!empty($update))
{
    if (property_exists($update, 'message'))
    {
        MessageHandler($update->message);
    }
    else if (property_exists($update, 'channel_post'))
    {
        ChannelPostHandler($update->channel_post);
    }
    else if (property_exists($update, 'shipping_query'))
    {
        ShippingQueryHandler($update->shipping_query);
    }
    else if (property_exists($update, 'inline_query'))
    {
        InlineQueryHandler($update->inline_query);
    }
    else if (property_exists($update, 'pre_checkout_query'))
    {
        PreCheckoutQueryHandler($update->pre_checkout_query);
    }
    else if (property_exists($update, 'my_chat_member'))
    {
        MyChatMemberHandler($update->my_chat_member);
    }
    else if (property_exists($update, 'callback_query'))
    {
        CallbackQueryHandler($update->callback_query);
    }
}
else
{
    include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-settings.php';
}
