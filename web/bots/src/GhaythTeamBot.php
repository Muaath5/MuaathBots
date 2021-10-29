<?php

namespace MuaathBots;

# Reuire autoload.php
require '/app/vendor/autoload.php';

use SimpleBotAPI\UpdatesHandler;

class GhaythTeamBot extends UpdatesHandler
{
    protected int|float|string $MessagesChatID;
    private array $BotAdmins;

    public function __construct(int|float|string $messages_chat_id, array $bot_admins)
    {
        $this->MessagesChatID = $messages_chat_id;
        $this->BotAdmins = $bot_admins;
    }

    # Write the handler for updates that your bot needs
    public function MessageHandler(object $message): bool
    {
        # The bot class will be stored in $this->Bot
        $is_admin = ($message->chat->type == 'channel' && $message->chat->id == $this->MessagesChatID);
        if (property_exists($message, 'from'))
        {
            $is_admin = $is_admin || (in_array($message->from->id, $this->BotAdmins) && $message->chat->id == $this->MessagesChatID);
        }
        
        # If admin
        if ($is_admin)
        {
            if (property_exists($message, 'reply_to_message'))
            {
                if (property_exists($message->reply_to_message, 'reply_markup'))
                {

                    $reply_chat_id = intval($message->reply_to_message->reply_markup->inline_keyboard[0][0]->text);
                    $reply_message_id = intval($message->reply_to_message->reply_markup->inline_keyboard[1][0]->text);

                    $this->Bot->CopyMessage([
                        'from_chat_id' => $message->chat->id,
                        'message_id' => $message->message_id,
                        'chat_id' => $reply_chat_id,
                        'allow_sending_without_reply' => true,
                        'caption' => $message->caption ?? null,
                        'caption_entities' => $message->caption_entities ?? null,
                        'reply_to_message_id' => $reply_message_id
                    ]);
                }
                else
                {
                    $this->Bot->SendMessage([
                        'chat_id' => $this->MessagesChatID,
                        'text' => 'You should reply on on of the existing messages from users that <b>Contains</b> Buttons!',
                        'parse_mode' => 'HTML'
                    ]);    
                }
            }
            else
            {
                $this->Bot->SendMessage([
                    'chat_id' => $this->MessagesChatID,
                    'text' => 'You should reply on on of the existing messages from users!'
                ]);
            }
        }

        if (property_exists($message, 'text'))
        {
            # Commands
            if ($message->text[0] == '/')
            {
                switch ($message->text)
                {
                    case '/start':
                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => 'السلام عليكم، هذا بوت تواصل مع فريق غيث.\nأرسل رسالتك وسيرد عليها مشرفو فريق غيث في أسرع وقت',
                            'reply_to_message_id' => $message->message_id,
                            'reply_markup' => json_encode([
                                'force_reply' => true,
                                'input_field_placeholder' => 'أرسل الرسالة :)',
                                'selective' => true
                            ])
                        ]);
                        break;

                    default:
                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => 'عذرًا، الأمر غير معروف'
                        ]);
                        break;
                }
            }
            else if ($message->chat->id != $this->MessagesChatID)
            {
                
                # Copy message to the owner of the bot
                $this->Bot->CopyMessage([
                    'chat_id' => $this->MessagesChatID,
                    'from_chat_id' => $message->chat->id,
                    'message_id' => $message->message_id,

                    # The bot will store user Data on the buttons
                    'reply_markup' => json_encode(['inline_keyboard' => [
                        [
                            [
                                'text' => $message->from->id,
                                'callback_data' => "info_{$message->from->id}"
                            ]
                        ],
                        [
                            [
                                'text' => $message->message_id,
                                'url' => 'https://google.com'
                            ]
                        ],
                        [
                            [
                                'text' => property_exists($message->chat, 'title') ? $message->chat->title : $message->chat->first_name . ' ' . $message->chat->last_name,
                                'url' => property_exists($message->chat, 'username') ? "https://t.me/{$message->chat->username}" : 'https://google.com/'
                            ]
                        ]
                    ]])
                ]);

                
                $this->Bot->SendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => 'تم إرسال الرسالة إلى الفريق بنجاح!',
                    'reply_to_message_id' => $message->message_id,
                    'reply_markup' => json_encode([
                        'force_reply' => true,
                        'input_field_placeholder' => 'إرسال رسالة أخرى لفريق غيث؟',
                        'selective' => true
                    ])
                ]);
            }
        }
        return true;
    }

    public function ChannelPostHandler(object $channel_post): bool
    {
        return $this->MessageHandler($channel_post);
    }

    public function CallbackQueryHandler(object $callback_query): bool
    {
        if (str_starts_with($callback_query->data, 'info_'))
        {
            $chat_id = intval(substr($callback_query->data, 5));
            $chat = $this->Bot->GetChat([
                'chat_id' => $chat_id
            ]);
            $name = $chat->title ?? $chat->first_name . ' ' . $chat->last_name;
            $description = $chat->description ?? $chat->bio;
            $userinfo = "معلومات المستخدم:
{$name} : {$chat->id}
{$description}";

            $this->Bot->AnswerCallbackQuery([
                'callback_query_id' => $callback_query->id,
                'text' => $userinfo,
                'show_alert' => true
            ]);
        }
        return true;
    }
}