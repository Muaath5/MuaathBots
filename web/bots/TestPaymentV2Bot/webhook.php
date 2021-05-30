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

# A Telegram Bot library (Contains functions like SendMessage, SendInvoice, etc.)
include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/TelegramBotAPI.php';
$Bot = new TelegramBot(Token);

include __DIR__ . '/bot.php';
$BotUpdatesHandler = new TestPaymentV2Bot();


# Reading the update
$Bot->SetUpdatesHandler($BotUpdatesHandler);
$update = json_decode(file_get_contents('php://input'));

# Check auth
define('BotDirectory', basename(__DIR__));
if ($_GET['token'] != Token)
{
    var_dump(include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-unauthorized.php');
    exit;
}

if (!empty($update))
{
    $Bot->OnUpdate($update);
}
else
{
    var_dump(include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-settings.php');
}