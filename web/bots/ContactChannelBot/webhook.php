<?php
declare(strict_types=1);

# Configurations
define('Token', getenv('ContactChannel_Token'));
define('AdminsGroupID', getenv('ContactChannel_GroupID'));

$bot_admins = json_decode(getenv('ContactChannel_Admins'));


# Bot settings
define('SettingsFilePath', __DIR__ . '/settings.json');
$settings = json_decode(file_get_contents(SettingsFilePath));


# A Telegram library (Contains functions like SendMessage, SendInvoice, etc.)
include $_SERVER['DOCUMENT_ROOT'] . '/telegram-bot-api.php';


# Bot update handlers
$bot_include_result = include __DIR__ . '/bot.php';

# Reading the update
$update = json_decode(file_get_contents('php://input'));

define('BotDirectory', basename(__DIR__));

# Check authintecation
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
    else if (property_exists($update, 'my_chat_member'))
    {
        MyChatMemberHandler($update->my_chat_member);
    }
    else if (property_exists($update, 'callback_query'))
    {
        # Don't do anything

        //CallbackQueryHandler($update->callback_query);
    }
}
else
{
    include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-settings.php';
}
