<?php
declare(strict_types=1);

namespace MuaathBots;

require '/app/vendor/autoload.php';

use SimpleBotAPI\TelegramBot;
use SimpleBotAPI\UpdatesHandler;
use SimpleBotAPI\TelegramException;
use SimpleBotAPI\TelegramChatMigrated;
use SimpleBotAPI\TelegramFloodWait;

/**
 * Test bot for Telegram Payments 2.0, Can be used for real payments
 * @version Bot API 5.3
 */
class RemoveInlineButtonsBot extends UpdatesHandler
{
    private int|string $LogsChatID;
    public $Settings;

    public function __construct(float|string $logs_chat_id)
    {
        $this->LogsChatID = $logs_chat_id;

        $class = explode('\\', get_class($this));
        $class = $class[count($class) - 1];
        $this->Settings = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bots/' . $class . '/settings.json'));
    }

    # Helper methods
    public function GetLanguage(object $user) : string
    {
        if (property_exists($user, 'language_code'))
        {
            if (property_exists($this->Settings, $user->language_code))
            {
                return $user->language_code;
            }
        }
        return 'en';
    }

    # Update handlers
    public function MessageHandler($message) : bool
    {
        try
        {
            if (in_array($message->from->id, $this->Settings->bot_admins))
            {
                # Nothing here now..
            }
            else
            {
                if (property_exists($message, 'reply_markup'))
                {
                    $this->Bot->DeleteMessage([
                        'chat_id' => $message->chat->id,
                        'message_id' => $message->message_id
                    ]);
                }
            }
            
            if (property_exists($message, 'text'))
            {    
                if ($message->text[0] === '/')
                {
                    return $this->CommandsHandler($message);
                }
            }
        }
        catch (TelegramException $tgex)
        {
            # Bot will get owner language
            $lang = $this->GetLanguage($this->Bot->GetChatAdministrators([
                'chat_id' => $message->chat->id
            ])->user);
            if ($tgex->getCode() == 400)
            {
                $this->Bot->SendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => $this->Settings->$lang->errors->bad_request
                ]);
            }
            else
            {

            }
        }
        catch (\Exception $ex)
        {
            // Log error in logs channel
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => "<b>Error:</b>\n$ex",
                'parse_mode' => 'HTML'
            ]);
            return false;
        }
        return true;
    }
        
    private function CommandsHandler(object $message) : bool
    {
        $lang = $this->GetLanguage($message->from);

        try
        {
            $commands = get_object_vars($this->Settings->$lang->commands);
            $sent_command = (explode('@', substr($message->text, 1)))[0];
            if (isset($commands[$sent_command]))
            {
                $this->Bot->SendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => $this->Settings->$lang->commands->$sent_command,
                    'parse_mode' => 'HTML'
                ]);
            }
            else
            {
                $this->Bot->SendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => $this->Settings->$lang->errors->command_not_found,
                    'parse_mode' => 'HTML'
                ]);
            }
        }
        catch (\Exception $ex)
        {
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => "<b>Error:</b>\n$ex",
                'parse_mode' => 'HTML'
            ]);
            return false;
        }
        return true;
    }

    public function ChannelPostHandler($channel_post) : bool
    {
        $deleteIt = false;
        if ($channel_post->chat->username == 'naqel3' || $channel_post->chat->id == $this->LogsChatID && property_exists($channel_post, 'text'))
        {
            $deleteIt = str_contains($channel_post->text, '-----------');
        }
        if (property_exists($channel_post, 'reply_markup') || $deleteIt == true)
        {
            try
            {
                $this->Bot->DeleteMessage([
                    'chat_id' => $channel_post->chat->id,
                    'message_id' => $channel_post->message_id
                ]);
            }
            catch (TelegramException $tgex)
            {
                # Bot will get owner language
                $admins = $this->Bot->GetChatAdministrators([
                    'chat_id' => $channel_post->chat->id
                ]);
                foreach ($admins as $admin)
                {
                    if ($admin->status === 'creator')
                    {
                        $lang = $this->GetLanguage($admin->user);
                        break;
                    }
                }
                if ($tgex->getCode() == 400)
                {
                    $this->Bot->SendMessage([
                        'chat_id' => $channel_post->chat->id,
                        'text' => $this->Settings->$lang->errors->bad_request
                    ]);
                    $this->Bot->SendMessage([
                        'chat_id' => $this->LogsChatID,
                        'text' => "<b>Error:</b>\n$tgex",
                        'parse_mode' => 'HTML'
                    ]);
                }
                else
                {
                    $this->Bot->SendMessage([
                        'chat_id' => $this->LogsChatID,
                        'text' => "<b>Error:</b>\n$tgex",
                        'parse_mode' => 'HTML'
                    ]);
                }
            }
            catch (\Exception $ex)
            {
                // Log error in logs channel
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "<b>Error:</b>\n$ex",
                    'parse_mode' => 'HTML'
                ]);
                return false;
            }
        }
        return true;
    }

    public function MyChatMemberHandler(object $my_chat_member) : bool
    {
        if ($my_chat_member->new_chat_member === 'member')
        {
            if ($my_chat_member->from->id === $my_chat_member->chat->id)
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Started conversion with the bot."
                ]);
            }
            else
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Added the bot to chat:
    {$my_chat_member->chat->title} [{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]"
                ]);
            }
        }
        else if ($my_chat_member->new_chat_member === 'kicked')
        {
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Kicked the bot from chat:
    {$my_chat_member->chat->title} [{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]"
            ]);
        }
        return true;
    }

    public function EditedChannelPostHandler(object $edited_channel_post): bool
    {
        $deleteIt = false;
        if ($edited_channel_post->chat->username == 'naqel3' || $edited_channel_post->chat->id == $this->LogsChatID && property_exists($edited_channel_post, 'text'))
        {
            if (str_contains($edited_channel_post->text, '-----------')) $deleteIt = true;
        }
        if (property_exists($edited_channel_post, 'reply_markup') || $deleteIt == true)
        {
            try
            {
                $this->Bot->DeleteMessage([
                    'chat_id' => $edited_channel_post->chat->id,
                    'message_id' => $edited_channel_post->message_id
                ]);
            }
            catch (TelegramException $tgex)
            {
                # Bot will get owner language
                $admins = $this->Bot->GetChatAdministrators([
                    'chat_id' => $edited_channel_post->chat->id
                ]);
                foreach ($admins as $admin)
                {
                    if ($admin->status === 'creator')
                    {
                        $lang = $this->GetLanguage($admin->user);
                        break;
                    }
                }
                if ($tgex->getCode() == 400)
                {
                    $this->Bot->SendMessage([
                        'chat_id' => $edited_channel_post->chat->id,
                        'text' => $this->Settings->$lang->errors->bad_request
                    ]);
                    $this->Bot->SendMessage([
                        'chat_id' => $this->LogsChatID,
                        'text' => "<b>Error:</b>\n$tgex",
                        'parse_mode' => 'HTML'
                    ]);
                }
                else
                {
                    $this->Bot->SendMessage([
                        'chat_id' => $this->LogsChatID,
                        'text' => "<b>Error:</b>\n$tgex",
                        'parse_mode' => 'HTML'
                    ]);
                }
            }
            catch (\Exception $ex)
            {
                // Log error in logs channel
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "<b>Error:</b>\n$ex",
                    'parse_mode' => 'HTML'
                ]);
                return false;
            }
        }
        return true;   
    }
}