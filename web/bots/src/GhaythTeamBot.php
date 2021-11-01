<?php

namespace MuaathBots;

# Reuire autoload.php
require '/app/vendor/autoload.php';

use SimpleBotAPI\Exceptions\TelegramException;
use SimpleBotAPI\UpdatesHandler;

class GhaythTeamBot extends UpdatesHandler
{
    protected int|float|string $MessagesChatID;

    private string $ADD_ADMIN_CMD = '!أضف مشرفًا ➕';

    public function __construct(int|float|string $messages_chat_id)
    {
        $this->MessagesChatID = $messages_chat_id;
    }

    # Write the handler for updates that your bot needs
    public function MessageHandler(object $message): bool
    {
        $is_admin = false;
        # The bot class will be stored in $this->Bot
        if (property_exists($message, 'from'))
        {
            $is_admin = in_array($message->from->id, $this->BotAdmins);
        }
        
        # First, Reply on commands
        if (property_exists($message, 'text'))
        {
            # Commands
            if ($message->text[0] == '/' || $message->text[0] == '!')
            {
                switch ($message->text)
                {
                    case '/start':
                    case '/start@GhaythTeamBot':
                        $reply = '😀 السلام عليكم، هذا بوت تواصل مع فريق غيث.

أرسل رسالتك وسيرد عليها مشرفو فريق غيث في أسرع وقت 😉';

                        $reply_markup = json_encode([
                            'force_reply' => true,
                            'input_field_placeholder' => 'أرسل الرسالة :)',
                            'selective' => true
                        ]);
                        
                        if ($is_admin)
                        {
                            $reply = '😀 السلام عليكم، هذا بوت تواصل مع فريق غيث.

<b>أنت مشرف في البوت!</b>';
                            $reply_markup = json_encode([
                                'keyboard' => [
                                    [['text' => $this->ADD_ADMIN_CMD]]
                                ],
                                'one_time_keyboard' => false,
                                'resize_keyboard' => false, # Only currently, Because only we have one keyboard
                            ]);
                        }

                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => $reply,
                            'reply_to_message_id' => $message->message_id,
                            'parse_mode' => 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        return true;
                    
                    case '/help':
                    case '/help@GhaythTeamBot':
                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => 'فريق غيث، هم مجموعة من طلاب جذور وغراس يقومون على عمل الملخصات والأسئلة الخاصة بجذور وغراس في أكاديمية الجيل الصاعد

استخدم هذا البوت للتواصل معهم، في أمان الله :)',
                            'reply_to_message_id' => $message->message_id
                        ]);
                        return true;

                    case $this->ADD_ADMIN_CMD:
                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => '#1 الآن أرسل لي رقم المعرف (ID) الخاص بالشحص الذي تريد إضافته، تأكد من أن الشخص موجود في مجموعة البوت وأنه قد راسل البوت سابقًا',
                            'reply_markup' => json_encode([
                                'force_reply' => true,
                                'selective' => true
                            ])
                        ]);
                        return true;

                    default:
                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => 'عذرًا، الأمر غير معروف'
                        ]);
                        return true;
                }
            }
        }

        # Check if it was a response to a query
        if (property_exists($message, 'reply_to_message'))
        {
            if ($message->reply_to_message->text[0] === '#')
            {
                // ADD_ADMIN_CMD
                if ($message->reply_to_message->text[1] == '1')
                {
                    $new_admin_id = intval($message->text);
                    # Error, Not number was sent
                    if ($new_admin_id == 0)
                    {
                        $this->Bot->SendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => '#1 يجب أن ترسل <b>رقم المعرف(ID)</b> الخاص بالشحص الذي تريد إضافته، تأكد من أن الشخص موجود في مجموعة البوت',
                            'reply_markup' => json_encode([
                                'force_reply' => true,
                                'selective' => true
                            ])
                        ]);
                        return true;
                    }

                    try
                    {
                        $this->Bot->GetChat(['chat_id' => $new_admin_id]);
                    }
                    catch (TelegramException $ex)
                    {
                        if ($ex->getCode() == 400)
                        {
                            $this->Bot->SendMessage([
                                'chat_id' => $message->chat->id,
                                'text' => '#1 تأكد من أن الشخص قد أرسل إلى البوت رسالةً واحدةً بحدٍ أدنى، ثم أعد إرسال ID الخاص به',
                                'reply_markup' => json_encode([
                                    'force_reply' => true,
                                    'selective' => true
                                ])
                            ]);
                            return true;
                        }
                    }

                    # Another thing, Bot should check if user is in the group, But not a priority

                    # Adding the admin
                    array_push($this->Bot->Settings->BotAdmins, $new_admin_id);
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => '',
                    ]);
                }
            }
        }

        # Check if it was an admin response
        if ($is_admin)
        {
            if ($message->chat->id == $this->MessagesChatID)
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
                }
            }
            
        }
        else
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
                            'url' => (property_exists($message->chat, 'username') ? "https://t.me/{$message->chat->username}" : 'https://google.com/')
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
        return true;
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