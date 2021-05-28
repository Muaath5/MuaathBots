<?php
    class TelegramBot
    {
        private ?string $TelegramUrl = null;
        private ?string $Token = null;
        private ?string $TelegramFileUrl = null;
        
        public function __constructor(string $token)
        {
            $this->Token = $token;
            $this->TelegramUrl = 'https://api.telegram.org/bot' . $this->Token . '/';
            $this->TelegramFileUrl = 'https://api.telegram.org/file/bot' . $this->Token . '/';
        }
        // Needs to "caption_entities"
        function SendMessage(int|string $chatId, string $text, int $replyToMessageId = 0, string $replyMarkup = "", string $parseMode = "HTML", bool $disableWebpagePreview = false, bool $disableNotification = false, bool $allowSendingWithoutReply = true)
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
            return $this->ProcessTelegramBotMethod("sendMessage", $params);
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
            return $this->ProcessTelegramBotMethod("forwardMessage", $params);
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
            return $this->ProcessTelegramBotMethod("copyMessage", $params);
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
            return $this->ProcessTelegramBotMethod("pinChatMessage", $params);
        }

        // Identifier of a message to unpin. If not specified, the most recent pinned message (by sending date) will be unpinned.
        function UnpinChatMessage($chatId, int $messageId = 0)
        {
            $params = 
            [
                "chat_id" => $chatId,
                "message_id" => $messageId,
            ];
            return $this->ProcessTelegramBotMethod("unpinChatMessage", $params);
        }

        function UnpinAllChatMessages($chatId)
        {
            $params = 
            [
                "chat_id" => $chatId
            ];
            return $this->ProcessTelegramBotMethod("unpinAllChatMessages", $params);
        }

        function GetFile($fileId)
        {
            $params = 
            [
                "file_id" => $fileId
            ];
            return $this->ProcessTelegramBotMethod("getFile", $params);
        }

        function GetMe()
        {
            $params = [];
            return $this->ProcessTelegramBotMethod("getMe", $params);
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
            return $this->ProcessTelegramBotMethod("answerInlineQuery", $params);
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
            return $this->ProcessTelegramBotMethod('answerCallbackQuery', $params);
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
            return $this->ProcessTelegramBotMethod('editMessageText', $params);
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
            return $this->ProcessTelegramBotMethod('editMessageCaption', $params);
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
            return $this->ProcessTelegramBotMethod('editMessageReplyMarkup', $params);
        }

        function EditMessageMedia($chatId, int $messageId, string $inlineMessageId = '', $media, string $replyMarkup = '', bool $isFile = false)
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'inline_message_id' => $inlineMessageId,
                'media' => $media,
                'reply_markup' => $replyMarkup
            ];
            return $this->ProcessTelegramBotMethod('editMessageMedia', $params, $isFile);
        }

        function StopPoll($chatId, int $messageId, string $replyMarkup = '')
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'reply_markup' => $replyMarkup
            ];
            return $this->ProcessTelegramBotMethod('stopPoll', $params);
        }

        function DeleteMessage($chatId, int $messageId)
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ];
            return $this->ProcessTelegramBotMethod('deleteMessage', $params);
        }

        function GetStickerSet(string $name)
        {
            $params = 
            [
                'name' => $name,
            ];
            return $this->ProcessTelegramBotMethod('getStickerSet', $params);
        }

        // Use this after method 'AddStickerToSet' or 'CreateStickerSet'
        function UploadStickerFile(int $userId, $pngSticker, bool $isFile = false)
        {
            $params = 
            [
                'user_id' => $userId,
                'png_sticker' => $pngSticker
            ];
            return $this->ProcessTelegramBotMethod('uploadStickerFile', $params, $isFile);
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
            return $this->ProcessTelegramBotMethod('sendInvoice', $params);
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
            return $this->ProcessTelegramBotMethod('answerShippingQuery', $params);
        }

        function AnswerPreCheckoutQuery(string $preCheckoutQueryId, bool $ok, string $errorMessage = '')
        {
            $params =
            [
                'pre_checkout_query_id' => $preCheckoutQueryId,
                'ok' => $ok,
                'error_message' => $errorMessage
            ];
            return $this->ProcessTelegramBotMethod('answerPreCheckoutQuery', $params);
        }
        
        public function DownloadFile($file, string $writeFilePath) : int|bool
        {
            if (property_exists($file, "file_path"))
            {
                return file_put_contents($writeFilePath, file_get_contents($this->TelegramFileUrl.$file->file_path));
            }
            else
            {
                return false;
            }
        }
        
        public function ProcessTelegramBotMethod(string $method, $params) : object
        {
            if (is_null($this->TelegramUrl))
            {
                http_response_code(401);
            }
            return $this->ProcessRequest($this->TelegramUrl.$method, $params);
        }

        protected function ProcessRequest(string $method, $params, bool $uploadingFile = false) : object
        {
            if(!$ch = curl_init())
            {
                exit;
            }
        
            $url_encoded = http_build_query($params, '&');
        
            if ($uploadingFile)
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type:multipart/form-data"
                ));
            }
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $url_encoded);
            curl_setopt($ch, CURLOPT_URL, TelegramUrl.$method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
        
            $output = curl_exec($ch);
            curl_close($ch);
        
            $output = json_decode($output);
            if ($output->ok === true)
            {
                $output = $output->result;
            }
            else
            {
                throw new Exception($output->description, $output->error_code);
            }
        
            return $output;
        }
    }
