<?php
declare(strict_types=1);


# Configurations
define('ProviderToken', getenv('TestPayment2Bot_ProviderToken'));
define('LogsChatID', getenv('TestPayment2Bot_LogsChatID'));
define('DevUsername', getenv('DevUsername'));

$bot_admins = json_decode(getenv('TestPayment2Bot_Admins'));

# Bot settings
define('SettingsFilePath', __DIR__ . '/settings.json');
$settings = json_decode(file_get_contents(SettingsFilePath));


# A Telegram Bot library (Contains functions like SendMessage, SendInvoice, etc.)
include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/TelegramBotAPI.php';
include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/UpdatesHandler.php';

$Bot = new TelegramBot(getenv('TestPayment2Bot_Token'));
include __DIR__ . '/bot.php';

$UpdatesHandler = new TestPaymentV2Bot();
$Bot->SetUpdatesHandler($UpdatesHandler);


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
    $Bot->OnUpdate($update);
}
else
{
    include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-settings.php';
}