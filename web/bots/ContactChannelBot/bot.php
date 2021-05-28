<?php
declare(strict_types=1);

function MessageHandler($message)
{
    global $settings;
    global $bot_admins;

    # Admin mode
    if ($message->chat->id === AdminsGroupID)
    {
        if (property_exists($message, 'reply_to_message'))
        {
            if (array_search($message->from->id, $bot_admins) !== false)
            {
                $user_id = $message->reply_to_message->reply_markup->inline_keyboard[1][0]->text;
                $reply_to_message_id = $message->reply_to_message->reply_markup->inline_keyboard[1][0]->callback_data;
                FullCopyForMessage($message, $user_id, $reply_to_message_id);
            }
            else
            {
                SendMessage(AdminsGroupID, $settings->error_not_admin, $message->message_id);
            }
        }
    }
    else
    {
        if (array_search($message->from->id, $bot_admins) !== false)
        {
            SendMessage($message->chat->id, $settings->go_to_admins_group, $message->message_id);
        }
        else
        {
            $msg_keyboard =
            [
                'inline_keyboard' =>
                [
                    # Row
                    [
                        [
                            'text' => $message->chat->title || $message->chat->first_name . $message->chat->last_name,
                            'callback_data' => $message->chat->id
                        ]
                    ],
                    [
                        [
                            'text' => $message->message_id,
                            'callback_data' => $message->message_id
                        ]
                    ]
                ]
            ];
            $msg_keyboard = json_encode($msg_keyboard);

            if (property_exists($message, 'text'))
            {
                if ($message->text[0] === '/')
                {
                    CommandsHandler($message->text, $message->chat->id);
                }
                else if ($settings->permissions->allow_text)
                {
                    FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
                }
            }
            else if (property_exists($message, 'animation') && $settings->permissions->allow_animation)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'audio') && $settings->permissions->allow_audio)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'contact') && $settings->permissions->allow_contact)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'document') && $settings->permissions->allow_document)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'photo') && $settings->permissions->allow_photo)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'poll') && $settings->permissions->allow_poll)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'sticker') && $settings->permissions->allow_sticker)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'video') && $settings->permissions->allow_video)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if (property_exists($message, 'video_note') && $settings->permissions->allow_video_note)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else if ((property_exists($message, 'location') || property_exists($message, 'venue')) && $settings->permissions->allow_location_and_venue)
            {
                FullCopyForMessage($message->chat->id, $message->message_id, AdminsGroupID, $msg_keyboard);
            }
            else
            {
                SendMessage($message->chat->id, $settings->error->not_allowed_type, $message->message_id);
                return;
            }
            SendMessage($message->chat->id, $settings->responses->sent_successfully);
        }
    }
}

function CommandsHandler(string $command, $chat_id)
{
    global $settings;
    $bot_username = dirname(__FILE__);
    switch ($command)
    {
        case '/start' || '/start@'.$bot_username:
            SendMessage($chat_id, $settings->start_message);
            break;
        
        case '/project' || '/project@'.$bot_username || '/start project':
            SendMessage($chat_id, $settings->project_message);
            break;

        case '/help' || '/help@'.$bot_username || '/start help':
            SendMessage($chat_id, $settings->help_message);
            break;

        default:
            # Unknown command
            SendMessage($chat_id, $settings->unknown_command);
            break;
    }
}

function MyChatMemberHandler($my_chat_member)
{
    global $settings;

    # Leave any channel or group, except AdminsGroup
    if ($my_chat_member->new_chat_member->status === 'member' && $my_chat_member->chat->id !== AdminsGroupID)
    {
        LeaveChat($my_chat_member->chat->id);
    }
    else
    {
        SendMessage(AdminsGroupID, $settings->logs->bot_status_changed);
    }
}