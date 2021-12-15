<?php
declare(strict_types=1);

namespace MuaathBots;

require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

use SimpleBotAPI\UpdatesHandler;
use SimpleBotAPI\Exceptions\TelegramException;

/**
 * Test bot for Telegram Payments 2.0, Can be used for real payments
 * @version Bot API 5.3
 */
class RemoveInlineButtonsBot extends UpdatesHandler
{
    private float|int|string $LogsChatID;
    public $Settings;

    public function __construct(float|int|string $logs_chat_id = null)
    {
        $this->LogsChatID = $logs_chat_id ?? getenv('REMOVE_INLINE_BUTTONS_BOT_LOGS_CHAT_ID');

        $class = explode('\\', get_class($this));
        $class = $class[count($class) - 1];
        $this->Settings = json_decode(file_get_contents(dirname(__DIR__) . "/$class/translations.json"));
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

        // Default language
        return 'en';
    }

    # Update handlers
    public function MessageHandler($message) : bool
    {
        try
        {
            # Main feature of the bot
            if (property_exists($message, 'reply_markup'))
            {
                $this->Bot->DeleteMessage([
                    'chat_id' => $message->chat->id,
                    'message_id' => $message->message_id
                ]);
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
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => "<b>Telegram Error {$tgex->getCode()}</b>\n{$tgex}",
                'parse_mode' => 'HTML'
            ]);
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
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(['inline_keyboard' => [[
                        ['text' => $this->Settings->$lang->buttons->subscribe_bot_channel, 'url' => 'https://t.me/MuaathBots']
                    ]]])
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
        catch (TelegramException $tgex)
        {
            if ($tgex->getCode() == 403)
            {
                http_response_code(200);
                return true;
            }

            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => "<b>Telegram Error {$tgex->getCode()}</b>
    {$tgex}
                
    User language: <code>{$lang}</code>
    ID: <code>{$message->from->id}</code>
    Username: {$message->from->username}",
                'parse_mode' => 'HTML'
            ]);
        }
        catch (\Exception $ex)
        {
            http_response_code(500);
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
        if ($channel_post->chat->username == 'Naqel6' || $channel_post->chat->id == $this->LogsChatID)
        {
            if (property_exists($channel_post, 'text'))
            {
                if (str_contains($channel_post->text, '-----------')) $deleteIt = true;
            }
            if (property_exists($channel_post, 'caption')) 
            {
                if (str_contains($channel_post->caption, '-----------')) $deleteIt = true;
            }
        }
        if (property_exists($channel_post, 'reply_markup') || ($deleteIt == true && !property_exists($channel_post, 'media_group_id')))
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
                }
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "<b>Telegram Error {$tgex->getCode()}</b>
    {$tgex}",
                    'parse_mode' => 'HTML'
                ]);
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
        if ($my_chat_member->new_chat_member->status === 'member')
        {
            if ($my_chat_member->from->id === $my_chat_member->chat->id)
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "User {$my_chat_member->from->first_name} [@{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] #Unblocked the bot.",
                    'parse_mode' => 'HTML'
                ]);
            }
            else
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "User {$my_chat_member->from->first_name} [@{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] #Added the bot to chat:
    {$my_chat_member->chat->title} [@{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]",
                    'parse_mode' => 'HTML'
                ]);
            }
        }
        else if ($my_chat_member->new_chat_member->status === 'kicked')
        {
            if ($my_chat_member->chat->id == $my_chat_member->from->id)
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "<b>Bot was #blocked by:</b> <code>{$my_chat_member->from->id}</code> {$my_chat_member->from->first_name} @{$my_chat_member->from->username}",
                    'parse_mode' => 'HTML'
                ]);
            }
            else
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "User {$my_chat_member->from->first_name} [@{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] #Kicked/Blocked the bot from chat:
    {$my_chat_member->chat->title} [@{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]",
                    'parse_mode' => 'HTML'
                ]);
            }
        }
        return true;
    }

    public function EditedChannelPostHandler(object $edited_channel_post): bool
    {
        $deleteIt = false;
        if ($edited_channel_post->chat->username == 'naqel3' || $edited_channel_post->chat->id == $this->LogsChatID)
        {
            if (property_exists($edited_channel_post, 'text'))
            {
                if (str_contains($edited_channel_post->text, '-----------')) $deleteIt = true;
            }
            if (property_exists($edited_channel_post, 'caption')) 
            {   
                if (str_contains($edited_channel_post->caption, '-----------')) $deleteIt = true;
            }
        }
        if (property_exists($edited_channel_post, 'reply_markup') || ($deleteIt == true && !property_exists($edited_channel_post, 'media_group_id')))
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
                // Bad request
                if ($tgex->getCode() == 400)
                {
                    $this->Bot->SendMessage([
                        'chat_id' => $edited_channel_post->chat->id,
                        'text' => $this->Settings->$lang->errors->bad_request
                    ]);
                   
                }
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => "<b>Telegram Error {$tgex->getCode()}</b>
    {$tgex}",
                    'parse_mode' => 'HTML'
                ]);
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