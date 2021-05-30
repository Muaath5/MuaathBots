<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/UpdatesHandler.php';

    include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/ErrorParameters.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/BotAPIException.php';


    class TelegramBot
    {
        private ?string $TelegramUrl = null;
        private ?string $Token = null;
        private ?string $TelegramFileUrl = null;
        
        private ?UpdatesHandler $UpdatesHandler = null;

        public function __constructor(string $token, UpdatesHandler $updates_handler = null)
        {
            $this->Token = $token;
            $this->TelegramUrl = 'https://api.telegram.org/bot' . $this->Token . '/';
            $this->TelegramFileUrl = 'https://api.telegram.org/file/bot' . $this->Token . '/';
            $this->UpdatesHandler = $updates_handler;
        }

        public function SetUpdatesHandler(UpdatesHandler $new_updates_handler)
        {
            $this->UpdatesHandler = $new_updates_handler;
        }

        public function OnUpdate(object $update) : bool
        {
            switch ($update)
            {
                case property_exists($update, 'message'):
                    return $this->UpdatesHandler->MessageHandler($update->message);
                
                case property_exists($update, 'edited_message'):
                    return $this->UpdatesHandler->EditedMessageHandler($update->edited_message);


                case property_exists($update, 'channel_post'):
                    return $this->UpdatesHandler->ChannelPostHandler($update->channel_post);

                case property_exists($update, 'edited_channel_post'):
                    return $this->UpdatesHandler->EditedChannelPostHandler($update->edited_channel_post);


                case property_exists($update, 'inline_query'):
                    return $this->UpdatesHandler->InlineQueryHandler($update->message);

                case property_exists($update, 'chosen_inline_query'):
                    return $this->UpdatesHandler->ChosenInlineQueryHandler($update->chosen_inline_query);


                case property_exists($update, 'callback_query'):
                    return $this->UpdatesHandler->CallbackQueryHandler($update->callback_query);


                case property_exists($update, 'my_chat_member'):
                    return $this->UpdatesHandler->MyChatMemberHandler($update->my_chat_member);

                case property_exists($update, 'chat_member'):
                    return $this->UpdatesHandler->ChatMemberHandler($update->chat_member);


                case property_exists($update, 'shipping_query'):
                    return $this->UpdatesHandler->ShippingQueryHandler($update->shipping_query);

                case property_exists($update, 'pre_checkout_query'):
                    return $this->UpdatesHandler->PreCheckoutQueryHandler($update->pre_checkout_query);

                default:
                    # Don't do anything, Only when Bot API version in later
                    return false;
            }
        }

        // Needs to "caption_entities"
        public function SendMessage(int|string $chatId, string $text, int $replyToMessageId = 0, string $replyMarkup = "", string $parseMode = "HTML", bool $disableWebpagePreview = false, bool $disableNotification = false, bool $allowSendingWithoutReply = true)
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
            return $this->ProcessBotAPIMethod("sendMessage", $params);
        }

        public function ForwardMessage($fromChatId, int $messageId, $chatId, bool $disableNotification = false)
        {
            $params = 
            [
                "chat_id" => $chatId,
                "from_chat_id" => $fromChatId,
                "message_id" => $messageId,
                "disable_notification" => $disableNotification
            ];
            return $this->ProcessBotAPIMethod("forwardMessage", $params);
        }
        public function CopyMessage($fromChatId, int $messageId, $chatId, string $caption = "", string $parseMode = 'HTML', string $captionEntities = '', int $replyToMessageId = 0, bool $disableNotification = false, bool $allowSendingWithoutReply = true, string $replyMarkup = "")
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
            return $this->ProcessBotAPIMethod("copyMessage", $params);
        }

        # Non-offical Telegarm Bot API method
        public function FullCopyForMessage(object $msg, string|int $chatId, int $replyToMessageId = 0, string $replyMarkup = '')
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
            return $this->CopyMessage($msg->chat->id, $msg->message_id, $chatId, $caption, $parseMode = "", $captionEntities, $replyToMessageId, false, true, $replyMarkup); 
        }

        public function PinChatMessage($chatId, $messageId, $disableNotification = false)
        {
            $params = 
            [
                "chat_id" => $chatId,
                "message_id" => $messageId,
                "disable_notification" => $disableNotification
            ];
            return $this->ProcessBotAPIMethod("pinChatMessage", $params);
        }

        // Identifier of a message to unpin. If not specified, the most recent pinned message (by sending date) will be unpinned.
        public function UnpinChatMessage($chatId, int $messageId = 0)
        {
            $params = 
            [
                "chat_id" => $chatId,
                "message_id" => $messageId,
            ];
            return $this->ProcessBotAPIMethod("unpinChatMessage", $params);
        }

        public function UnpinAllChatMessages($chatId)
        {
            $params = 
            [
                "chat_id" => $chatId
            ];
            return $this->ProcessBotAPIMethod("unpinAllChatMessages", $params);
        }

        public function GetFile($fileId)
        {
            $params = 
            [
                "file_id" => $fileId
            ];
            return $this->ProcessBotAPIMethod("getFile", $params);
        }

        public function GetMe()
        {
            $params = [];
            return $this->ProcessBotAPIMethod("getMe", $params);
        }

        public function AnswerInlineQuery(string $inlineQueryId, string $results, int $cacheTime = 300, bool $isPersonal = false, string $nextOffset = "", string $switchPmText = "", string $switchPmParameter = "")
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
            return $this->ProcessBotAPIMethod("answerInlineQuery", $params);
        }

        public function AnswerCallbackQuery(string $id, string $text = '', bool $showAlert = false, string $url = '', int $cacheTime = 0)
        {
            $params = 
            [
                'callback_query_id' => $id,
                'text' => $text,
                'show_alert' => $showAlert,
                'url' => $url,
                'cache_time' => $cacheTime
            ];
            return $this->ProcessBotAPIMethod('answerCallbackQuery', $params);
        }

        public function EditMessageText(int|string $chatId, int $messageId, string $inlineMessageId, string $text, string $parseMode = 'HTML', string $entities = '', bool $disableWebPagePreview = false, string $replyMarkup = '')
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
            return $this->ProcessBotAPIMethod('editMessageText', $params);
        }

        public function EditMessageCaption($chatId, int $messageId, string $caption, string $inlineMessageId = '', string $parseMode = 'HTML', string $entities = '', string $replyMarkup = '')
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
            return $this->ProcessBotAPIMethod('editMessageCaption', $params);
        }

        public function EditMessageReplyMarkup($chatId, int $messageId, string $inlineMessageId = '', string $replyMarkup = '')
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'inline_message_id' => $inlineMessageId,
                'reply_markup' => $replyMarkup
            ];
            return $this->ProcessBotAPIMethod('editMessageReplyMarkup', $params);
        }

        public function EditMessageMedia($chatId, int $messageId, string $inlineMessageId = '', $media, string $replyMarkup = '', bool $isFile = false)
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'inline_message_id' => $inlineMessageId,
                'media' => $media,
                'reply_markup' => $replyMarkup
            ];
            return $this->ProcessBotAPIMethod('editMessageMedia', $params, $isFile);
        }

        public function StopPoll($chatId, int $messageId, string $replyMarkup = '')
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'reply_markup' => $replyMarkup
            ];
            return $this->ProcessBotAPIMethod('stopPoll', $params);
        }

        function DeleteMessage($chatId, int $messageId)
        {
            $params = 
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ];
            return $this->ProcessBotAPIMethod('deleteMessage', $params);
        }

        function GetStickerSet(string $name)
        {
            $params = 
            [
                'name' => $name,
            ];
            return $this->ProcessBotAPIMethod('getStickerSet', $params);
        }

        // Use this after method 'AddStickerToSet' or 'CreateStickerSet'
        public function UploadStickerFile(int $userId, $pngSticker, bool $isFile = false)
        {
            $params = 
            [
                'user_id' => $userId,
                'png_sticker' => $pngSticker
            ];
            return $this->ProcessBotAPIMethod('uploadStickerFile', $params, $isFile);
        }

        public function SendInvoice($chatId, string $title, string $description, string $payload, string $providerToken, string $currency, string $prices, int $maxTipAmount = 0,
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
            return $this->ProcessBotAPIMethod('sendInvoice', $params);
        }

        public function AnswerShippingQuery(string $shippingQueryId, bool $ok, string $shippingOptions = '', string $errorMessage = '')
        {
            $params =
            [
                'shipping_query_id' => $shippingQueryId,
                'ok' => $ok,
                'shipping_options' => $shippingOptions,
                'error_message' => $errorMessage
            ];
            return $this->ProcessBotAPIMethod('answerShippingQuery', $params);
        }

        public function AnswerPreCheckoutQuery(string $preCheckoutQueryId, bool $ok, string $errorMessage = '')
        {
            $params =
            [
                'pre_checkout_query_id' => $preCheckoutQueryId,
                'ok' => $ok,
                'error_message' => $errorMessage
            ];
            return $this->ProcessBotAPIMethod('answerPreCheckoutQuery', $params);
        }
        
        public function DownloadFile(object $file, string $writeFilePath) : int|bool
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
        
        public function SetWebhook(string $url)
        {
            $params =
            [
                'url' => $url
            ];
            return $this->ProcessBotAPIMethod('setWebhook', $params);
        }

        public function DeleteWebhook(bool $drop_pending_updates = false)
        {
            $params =
            [
                'drop_pending_updates' => $drop_pending_updates
            ];
            return $this->ProcessBotAPIMethod('deleteWebhook', $params);
        }

        public function GetWebhookInfo()
        {
            return $this->ProcessBotAPIMethod('getWebhookInfo');
        }

        public function ProcessBotAPIMethod(string $method, array $params = [], bool $uploading_file = false) : object
        {
            if (is_null($this->TelegramUrl))
            {
                http_response_code(401);
            }
            return $this->ProcessRequest($this->TelegramUrl.$method, $params, $uploading_file);
        }

        protected function ProcessRequest(string $method, array $params = [], bool $uploading_file = false) : object
        {
            if(!$ch = curl_init())
            {
                exit;
            }
        
            $url_encoded = http_build_query($params, '&');
        
            if ($uploading_file)
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type:multipart/form-data"
                ));
            }
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $url_encoded);
            curl_setopt($ch, CURLOPT_URL, $this->TelegramUrl . $method);
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
                // Error flood
                if ($output->error_code === 429)
                {
                    // Sleep, Then rerequest
                    usleep($output->parameters->retry_after * 1000000);
                    return $this->ProcessRequest($method, $params, $uploading_file);
                }
                else
                {
                    $error = new BotAPIException($output->description, $output->error_code);
                    if (property_exists($output, 'parameters'))
                    {
                        if (get_class_vars('ErrorParameters') === get_object_vars($output->parameters))
                        {
                            $error->parameters = $output->parameters;
                        }
                    }
                    throw $error;
                }
            }
        
            return $output;
        }
    }
