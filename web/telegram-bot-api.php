<?php
    define("TelegramUrl", "https://api.telegram.org/bot".Token.'/');

    // Needs to "caption_entities"
    function SendMessage($chatId, string $text, int $replyToMessageId = 0, string $replyMarkup = "", string $parseMode = "HTML", bool $disableWebpagePreview = false, bool $disableNotification = false, bool $allowSendingWithoutReply = true)
    {
        $params = 
        [
            "chat_id" => $chatId,
            "text" => $text,
            "reply_to_message_id" => $replyToMessageId,
            "reply_markup" => $replyMarkup,
            "parse_mode" => $parseMode,
            "disable_web_page_preview" => $disableWebpagePreview,
            "disable_notification" => $disableNotification,
            "allow_sending_without_reply" => $allowSendingWithoutReply
        ];
        return ProcessMethod("sendMessage", $params);
    }
    
    function ForwardMessage($fromChatId, int $messageId, $chatId, bool $disableNotification = false)
    {
        $params = 
        [
            "chat_id" => $chatId,
            "from_chat_id" => $fromChatId,
            "message_id" => $messageId,
            "disable_notification" => $disableNotification
        ];
        return ProcessMethod("forwardMessage", $params);
    }
    function CopyMessage($fromChatId, int $messageId, $chatId, string $caption = "", string $parseMode = 'HTML', string $captionEntities = '', int $replyToMessageId = 0, bool $disableNotification = false, bool $allowSendingWithoutReply = true, string $replyMarkup = "")
    {
        $params = 
        [
            "chat_id" => $chatId,
            "from_chat_id" => $fromChatId,
            "message_id" => $messageId,
            "reply_to_message_id" => $replyToMessageId,
            "allow_sending_without_reply" => $allowSendingWithoutReply,
            "caption" => $caption,
            "parse_mode" => $parseMode,
            "caption_entities" => $captionEntities,
            "disable_notification" => $disableNotification,
            "reply_markup" => $replyMarkup
        ];
        return ProcessMethod("copyMessage", $params);
    }

    # Non-offical Telegarm Bot API method
    function FullCopyForMessage($msg, $chatId, int $replyToMessageId = 0, string $replyMarkup = '')
    {
        $caption = "";
        $captionEntities = "";
        if (property_exists($msg, "caption"))
        {
            $caption = $msg->caption;
        }
        if (property_exists($msg, "caption_entities"))
        {
            $captionEntities = json_encode($msg->caption_entities);
        }
        
        # Allow notification, Allow sending without reply.
        return CopyMessage($msg->chat->id, $msg->message_id, $chatId, $caption, $parseMode = "", $captionEntities, $replyToMessageId, false, true, $replyMarkup); 
    }

    function PinChatMessage($chatId, $messageId, $disableNotification = false)
    {
        $params = 
        [
            "chat_id" => $chatId,
            "message_id" => $messageId,
            "disable_notification" => $disableNotification
        ];
        return ProcessMethod("pinChatMessage", $params);
    }

    // Identifier of a message to unpin. If not specified, the most recent pinned message (by sending date) will be unpinned.
    function UnpinChatMessage($chatId, int $messageId = 0)
    {
        $params = 
        [
            "chat_id" => $chatId,
            "message_id" => $messageId,
        ];
        return ProcessMethod("unpinChatMessage", $params);
    }

    function UnpinAllChatMessages($chatId)
    {
        $params = 
        [
            "chat_id" => $chatId
        ];
        return ProcessMethod("unpinAllChatMessages", $params);
    }

    function GetFile($fileId)
    {
        $params = 
        [
            "file_id" => $fileId
        ];
        return ProcessMethod("getFile", $params);
    }

    function GetMe()
    {
        return ProcessMethod("getMe");
    }

    function AnswerInlineQuery(string $inlineQueryId, string $results, int $cacheTime = 300, bool $isPersonal = false, string $nextOffset = "", string $switchPmText = "", string $switchPmParameter = "")
    {
        $params = 
        [
            "inline_query_id" => $inlineQueryId,
            "results" => $results,
            "cache_time" => $cacheTime,
            "is_personal" => $isPersonal,
            "next_offset" => $nextOffset,
            "switch_pm_text" => $switchPmText,
            "switch_pm_parameter" => $switchPmParameter
        ];
        return ProcessMethod("answerInlineQuery", $params);
    }

    function AnswerCallbackQuery(string $id, string $text = '', bool $showAlert = false, string $url = '', int $cacheTime = 0)
    {
        $params = 
        [
            'callback_query_id' => $id,
            'text' => $text,
            'show_alert' => $showAlert,
            'url' => $url,
            'cache_time' => $cacheTime
        ];
        return ProcessMethod('answerCallbackQuery', $params);
    }

    function EditMessageText($chatId, int $messageId, string $inlineMessageId = '', string $text, string $parseMode = 'HTML', string $entities = '', bool $disableWebPagePreview = false, string $replyMarkup = '')
    {
        $params = 
        [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'entities' => $entities,
            'disable_web_page_preview' => $disableWebPagePreview,
            'reply_markup' => $replyMarkup
        ];
        return ProcessMethod('editMessageText', $params);
    }

    function EditMessageCaption($chatId, int $messageId, string $caption, string $inlineMessageId = '', string $parseMode = 'HTML', string $entities = '', string $replyMarkup = '')
    {
        $params = 
        [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'caption' => $caption,
            'parse_mode' => $parseMode,
            'entities' => $entities,
            'reply_markup' => $replyMarkup
        ];
        return ProcessMethod('editMessageCaption', $params);
    }

    function EditMessageReplyMarkup($chatId, int $messageId, string $inlineMessageId = '', string $replyMarkup = '')
    {
        $params = 
        [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'reply_markup' => $replyMarkup
        ];
        return ProcessMethod('editMessageReplyMarkup', $params);
    }

    function EditMessageMedia($chatId, int $messageId, string $inlineMessageId = '', string $media, string $replyMarkup = '')
    {
        $params = 
        [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'media' => $media,
            'reply_markup' => $replyMarkup
        ];
        return ProcessMethod('editMessageMedia', $params);
    }

    function StopPoll($chatId, int $messageId, string $replyMarkup = '')
    {
        $params = 
        [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup
        ];
        return ProcessMethod('stopPoll', $params);
    }

    function DeleteMessage($chatId, int $messageId)
    {
        $params = 
        [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];
        return ProcessMethod('deleteMessage', $params);
    }

    function GetChat($chat_id)
    {
        $params = 
        [
            'chat_id' => $chat_id
        ];
        return ProcessMethod('getChat', $params);
    }

    function GetUserProfilePhotos($chat_id, int $offset = 0, int $limit = 100)
    {
        $params = 
        [
            'chat_id' => $chat_id,
            'offset' => $offset,
            'limit' => $limit
        ];
        return ProcessMethod('getUserProfilePhotos', $params);
    }

    function GetStickerSet(string $name)
    {
        $params = 
        [
            'name' => $name,
        ];
        return ProcessMethod('getStickerSet', $params);
    }

    // Use this after method 'AddStickerToSet' or 'CreateStickerSet'
    function UploadStickerFile(int $userId, string $pngSticker)
    {
        $params = 
        [
            'user_id' => $userId,
            'png_sticker' => $pngSticker
        ];
        return ProcessMethod('uploadStickerFile', $params);
    }

    # Webhook methods
    function SetWebhook(string $url, $certificate = '', string $ipAddress = '', int $maxConnections = 40, array $allowedUpdates = ['message', 'message_edited', 'channel_post', 'channel_post_edited', 'inline_query', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll', 'poll_answer', 'my_chat_member'])
    {
        $params =
        [
            'url' => $url,
            'certificate' => $certificate,
            'ip_address' => $ipAddress,
            'max_connections' => $maxConnections,
            'allowed_updates' => json_encode($allowedUpdates)
        ];
        return ProcessMethod('setWebhook', $params);
    }

    function GetWebhookInfo()
    {
        return ProcessMethod('getWebhookInfo');
    }

    function DeleteWebhook(bool $dropPendingUpdates = false)
    {
        $params =
        [
            'drop_pending_updates' => $dropPendingUpdates
        ];
        return ProcessMethod('deleteWebhook', $params);
    }

    function SendInvoice($chatId, string $title, string $description, string $payload, string $providerToken, string $currency, string $prices, int $maxTipAmount = 0,
                         string $suggestedTipAmounts = '', string $startParameter = '', string $providerData = '', string $photoUrl = '', int $photoSize = 0,
                         int $photoWidth = 0, int $photoHeight = 0, bool $needName = true, bool $needPhoneNumber = true, bool $needEmail = true,
                         bool $needShippingAddress = true, bool $sendPhoneNumberToProvider = true, bool $sendEmailToProvider = true, bool $isFlexible = true,
                         int $replyToMessageId = 0, bool $allowSendingWithoutReply = true, string $replyMarkup = '')
    {
        $params =
        [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'provider_token' => $providerToken,
            'currency' => $currency,
            'prices' => $prices,
            'max_tip_amount' => $maxTipAmount,                             # Payments 2.0
            'suggested_tip_amounts' => $suggestedTipAmounts,               # Payments 2.0
            'start_parameter' => $startParameter,
            'provider_data' => $providerData,
            'photo_url' => $photoUrl,
            'photo_size' => $photoSize,
            'photo_width' => $photoWidth,
            'photo_height' => $photoHeight,
            'need_name' => $needName,
            'need_phone_number' => $needPhoneNumber,
            'need_email' => $needEmail,
            'need_shipping_address' => $needShippingAddress,
            'send_phone_number_to_provider' => $sendPhoneNumberToProvider, # Payments 2.0
            'send_email_to_provider' => $sendEmailToProvider,
            'is_flexible' => $isFlexible,
            'reply_to_message_id' => $replyToMessageId,
            'allow_sending_without_reply' => $allowSendingWithoutReply,
            'reply_markup' => $replyMarkup,
        ];
        return ProcessMethod('sendInvoice', $params);
    }

    function AnswerShippingQuery(string $shippingQueryId, bool $ok, string $shippingOptions = '', string $errorMessage = '')
    {
        $params =
        [
            'shipping_query_id' => $shippingQueryId,
            'ok' => $ok,
            'shipping_options' => $shippingOptions,
            'error_message' => $errorMessage
        ];
        return ProcessMethod('answerShippingQuery', $params);
    }

    function AnswerPreCheckoutQuery(string $preCheckoutQueryId, bool $ok, string $errorMessage = '')
    {
        $params =
        [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok' => $ok,
            'error_message' => $errorMessage
        ];
        return ProcessMethod('answerPreCheckoutQuery', $params);
    }

    function LeaveChat($chatId)
    {
        $params =
        [
            'chat_id' => $chatId
        ];
        return ProcessMethod('leaveChat', $params);
    }

    function ProcessMethod(string $method, array $params = [])
    {
        if(!$curld = curl_init())
        {
            exit;
        }
        
        $url_encoded = http_build_query($params, '&');
        
        curl_setopt($curld, CURLOPT_POST, true);
        curl_setopt($curld, CURLOPT_POSTFIELDS, $url_encoded);
        curl_setopt($curld, CURLOPT_URL, TelegramUrl.$method);
        curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curld, CURLOPT_FAILONERROR, false);
        
        $output = curl_exec($curld);
        curl_close($curld);
        
        $output = json_decode($output);
        if ($output->ok == true)
        {
            $output = $output->result;
        }
        else
        {
            //throw new Exception($output->description);
        }
        
        return $output;
    }
