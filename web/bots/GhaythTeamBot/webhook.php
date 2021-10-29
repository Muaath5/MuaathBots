<?php
declare(strict_types=1);

namespace MuaathBots;

require_once '/app/vendor/autoload.php';

use MuaathBots\GhaythTeamBot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$Bot = new TelegramBot(getenv('GHAYTH_TEAM_BOT_TOKEN'), new GhaythTeamBot(getenv('GHAYTH_TEAM_MESSAGES_CHAT_ID'), json_decode(getenv('GHAYTH_TEAM_ADMINS'))), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}