<?php
declare(strict_types=1);

include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/UpdatesHandler.php';

class TestPaymentV2Bot extends UpdatesHandler
{
    # Functions
    private function GetInvoiceByPayload(string $payload, $settings, bool $returnIndexInArray = false) : array|object
    {
        for ($i = 0; $i < count($settings->invoices); $i++)
        {
            if ($payload === $settings->invoices[$i]->payload)
            {
                if ($returnIndexInArray)
                {
                    return array($settings->invoices[$i], $i);
                }
                else
                {
                    return $settings->invoices[$i];
                }
            }
        }
        return false;
    }

    private string $contact_the_dev = json_encode(
    [
        'inline_keyboard' =>
        [
            [
                ['text' => 'Contanct the developer', 'url' => 'https://t.me/' . DevUsername] # @Muaath_5 is the main owner of this bot.
            ]
        ]
    ]);

    # Update handlers
    public function MessageHandler($message) : bool
    {
        global $settings;
        global $contact_the_dev;
        global $bot_admins;
        global $Bot;

        if (in_array($message->from->id, $bot_admins))
        {
            if (false)
            {
                # Nothing here...
            }
        }

        $senderChat = $message->from;
        if (property_exists($message, 'text'))
        {    
            if ($message->text[0] === '/')
            {
                $this->CommandsHandler($message->text, $settings);
            }
        }
        else if (property_exists($message, 'successful_payment'))
        {
            $resultInvoice = $this->GetInvoiceByPayload($message->successful_payment->invoice_payload, $settings, true);
            $Bot->SendMessage(LogsChatID, "JSON for resultInvoice is: <code>" . json_encode($resultInvoice) . '</code>');
            $successedInvoice = $resultInvoice[0];
            # Handle limits
            if ($resultInvoice[0]->limit > 0 && $resultInvoice[0]->limit != -1)
            {
                $settings->invoices[$resultInvoice[1]]->limit--;
                //file_put_contents(SettingsFilePath, json_encode($settings));
            }
            else if ($resultInvoice[0]->limit != -1)
            {
                $Bot->SendMessage(LogsChatID, "Error, {$message->successful_payment->invoice_payload} was sold out over the limit", 0, $contact_the_dev);
            }
            
            $floatTotalAmount = $message->successful_payment->total_amount / 100;
            $info = "<b>Successful payment info 😃:</b>

    <b>User info:</b>
    Telegram User: {$message->from->first_name} @{$message->from->username} (<code>{$message->from->id}</code>).
    Name: {$message->successful_payment->order_info->name}
    Phone number: {$message->successful_payment->order_info->phone_number}
    Email: {$message->successful_payment->order_info->email}


    <b>Shipping:</b>
    Shipping option: <code>{$message->successful_payment->shipping_option_id}</code>.
    Shipping address:
        Country code: {$message->successful_payment->order_info->shipping_address->country_code}.
        State: {$message->successful_payment->order_info->shipping_address->state}.
        City: {$message->successful_payment->order_info->shipping_address->city}.
        Street line 1: {$message->successful_payment->order_info->shipping_address->street_line1}.
        Street line 2: {$message->successful_payment->order_info->shipping_address->street_line2}.
        Post code: {$message->successful_payment->order_info->shipping_address->post_code}.

    <b>Invoice result</b>
    Invoice payload: <code>{$message->successful_payment->invoice_payload}</code>.    
    Total amount: {$floatTotalAmount} {$message->successful_payment->currency}.";

            $Bot->SendMessage(LogsChatID, $info);
                
            $Bot->SendMessage($senderChat->id, $settings->successful_payment_message, $message->message_id);
        }
        return true;
    }

    function CommandsHandler($message, $settings)
    {
        global $contact_the_dev;
        global $Bot;

        $senderChat = $message->chat;

        if ($message->text === '/start')
        {      
            $Bot->SendMessage($message->chat->id, $settings->start_message, $message->message_id);
        }
        else if ($message->text === '/project' || $message->text === '/start project')
        {
            $Bot->SendMessage($message->chat->id, $settings->project_message, $message->message_id);
        }
        else if ($message->text === '/help' || $message->text === '/start help')
        {
            $Bot->SendMessage($message->chat->id, $settings->help_message, $message->message_id);
        }
        else if ($message->text === '/inline' || $message->text === '/start inline')
        {
            $inlineQueryKeyboard =
            [
                'inline_keyboard' =>
                [
                    [[
                        'text' => $settings->inline_chat_button,
                        'switch_inline_query' => 'payment'
                    ]],
                    [[
                        'text' => $settings->inline_current_chat_button,
                        'switch_inline_query_current_chat' => 'payment'
                    ]]
                ]
            ];
            $Bot->SendMessage($message->chat->id, $settings->inline_message, $message->message_id, json_encode($inlineQueryKeyboard));
        }
        else if ($message->text === '/invoice' || $message->text === '/start invoice')
        {   
            
                
                
            $providerData =
            [
                'payload' => $settings->invoices[0]->payload,
                'user_id' => $senderChat->id,
                'prices' => $settings->invoices[0]->prices
            ];
            
                
            # $invoice Should be Message object, If an error occurd 
            $photoWidth = $settings->invoices[0]->photo_width;
            $photoHeight = $settings->invoices[0]->photo_height;
            $Bot->SendMessage($senderChat->id, $settings->warning_message);
            
            $mainInvoice = $Bot->SendInvoice($senderChat->id, $settings->invoices[0]->title, $settings->invoices[0]->description, $settings->invoices[0]->payload,
                                ProviderToken, $settings->invoices[0]->currency, json_encode($settings->invoices[0]->prices), $settings->invoices[0]->max_tip_amount,
                                json_encode($settings->invoices[0]->suggested_tip_amounts), $settings->invoices[0]->start_param, json_encode($providerData), $settings->invoices[0]->photo_url, 
                                $photoWidth * $photoHeight, $photoWidth, $photoHeight, $settings->invoices[0]->need_name, $settings->invoices[0]->need_phone_number,
                                $settings->invoices[0]->need_email, $settings->invoices[0]->need_shipping_address, $settings->invoices[0]->send_phone_number_to_provider,
                                $settings->invoices[0]->send_email_to_provider, $settings->invoices[0]->is_flexible);
                
                
                
            # Logging
            $newInvoiceRequestText = "New invoice request 📲:
    First name: {$senderChat->first_name}.
    Last name: {$senderChat->last_name}.
    Username: @{$senderChat->username} ({$senderChat->id}).
    Language (null if not available): {$senderChat->language_code}.
    Which command was used: {$message->text}.";
                    
            $newInvoiceLogMsg = $Bot->SendMessage(LogsChatID, $newInvoiceRequestText);
                
            if ($mainInvoice->ok === false)
            {
                $errorStr =
    "<code>SendInvoice</code> error.
    Error code: <code>{$mainInvoice->error_code}</code>.
    Error description: {$mainInvoice->description}.";
                # Log error
                $Bot->SendMessage(LogsChatID, $errorStr, $newInvoiceLogMsg->message_id, $contact_the_dev);
            }
        }
        else
        {
            for ($i = 0; $i < count($settings->invoices); $i++)
            {
                if ($message->text === "/start {$settings->invoices[$i]->start_param}")
                {
                    $providerData =
                    [
                        'user_id' => $message->from->id,
                        'prices' => $settings->invoices[$i]->prices,
                        'payload' => $settings->invoices[$i]->payload
                    ];
                            
                    $photoWidth = $settings->invoices[$i]->photo_width;
                    $photoHeight = $settings->invoices[$i]->photo_height;
                    $inlineInvoice = $Bot->SendInvoice($message->chat->id, $settings->invoices[$i]->title, $settings->invoices[$i]->description, $settings->invoices[$i]->payload,
                                    ProviderToken, $settings->invoices[$i]->currency, json_encode($settings->invoices[$i]->prices),
                                    $settings->invoices[$i]->max_tip_amount, json_encode($settings->invoices[$i]->suggested_tip_amounts), $settings->invoices[$i]->start_param,
                                    json_encode($providerData), $settings->invoices[$i]->photo_url, $photoHeight * $photoWidth, $photoWidth, $photoHeight,
                                    $settings->invoices[$i]->need_name, $settings->invoices[$i]->need_phone_number, $settings->invoices[$i]->need_email,
                                    $settings->invoices[$i]->need_shipping_address, $settings->invoices[$i]->send_phone_number_to_provider,
                                    $settings->invoices[$i]->send_email_to_provider, $settings->invoices[$i]->is_flexible);
                            
                    # Logging
                    $newInvoiceRequestText = "New invoice request 📲:
    First name: {$senderChat->first_name}.
    Last name: {$senderChat->last_name}.
    Username: @{$senderChat->username} ({$senderChat->id}).
    Language (null if not available): {$senderChat->language_code}.
    Which command was used: {$message->text}.";
                        
                    $inlineInvoiceLogMsg = $Bot->$Bot->SendMessage(LogsChatID, $newInvoiceRequestText);
                        
                    if ($inlineInvoice->ok === false)
                    {
                        $errorStr =
    "<code>SendInvoice</code> error.
    Error code: <code>{$inlineInvoice->error_code}</code>.
    Error description: {$inlineInvoice->description}.

    <b>Error parameters:</b>
    Retry after: {$inlineInvoice->parameters->retry_after}.";
                        # Log error
                        $Bot->SendMessage(LogsChatID, $errorStr, $inlineInvoiceLogMsg->message_id, $contact_the_dev);
                    }
                    break;
                }
            }
        }
        return true;
    }

    public function ChannelPostHandler($channel_post) : bool
    {
        global $Bot;
        $Bot->DeleteMessage($channel_post->chat->id, $channel_post->message_id);
        return $this->MessageHandler($channel_post);
    }

    public function InlineQueryHandler($inline_query) : bool
    {
        global $Bot;
        global $settings;
        global $contact_the_dev;
        
        $results = [];
        for ($i = 0; $i < count($settings->invoices); $i++)
        {   
            array_push($results, 
            [
                'type' => 'article',
                'id' => $settings->invoices[$i]->payload,
                'title' => $settings->invoices[$i]->title,
                'description' => $settings->invoices[$i]->description,
                'thumb_url' => $settings->invoices[$i]->photo_url,
                'thumb_width' => $settings->invoices[$i]->photo_width,
                'thumb_height' => $settings->invoices[$i]->photo_height,
                'input_message_content' =>
                [
                    'title' => $settings->invoices[$i]->title,
                    'description' => $settings->invoices[$i]->description,
                    'payload' => $settings->invoices[$i]->payload,
                    'provider_token' => ProviderToken,
                    'currency' => $settings->invoices[$i]->currency,
                    'prices' => $settings->invoices[$i]->prices,
                    'photo_url' => $settings->invoices[$i]->photo_url,
                    'photo_width' => $settings->invoices[$i]->photo_width,
                    'photo_height' => $settings->invoices[$i]->photo_height,
                    'photo_size' => $settings->invoices[$i]->photo_width * $settings->invoices[$i]->photo_height,
                    'max_tip_amount' => $settings->invoices[$i]->max_tip_amount,
                    'suggested_tip_amounts' => $settings->invoices[$i]->suggested_tip_amounts,
                    'provider_data' => json_encode(
                    [
                        'sender_user_id' => $inline_query->from->id,
                        'chat_type' => $inline_query->chat_type,
                        'query' => $inline_query->query,
                        'payload' => $settings->invoices[$i]->payload
                    ]),
                    'need_name' => $settings->invoices[$i]->need_name,
                    'need_phone_number' => $settings->invoices[$i]->need_phone_number,
                    'need_email' => $settings->invoices[$i]->need_email,
                    'need_shipping_address' => $settings->invoices[$i]->need_shipping_address,
                    'is_flexible' => $settings->invoices[$i]->is_flexible
                ]
            ]);
        }
        
        $results = json_encode($results);
        
        $answerInlineSuccess = $Bot->AnswerInlineQuery($inline_query->id, $results, 300, false, '', 'Main invoice', 'invoice');
        
        if ($answerInlineSuccess->ok === false)
        {
            $answerInlineSuccess = json_encode($answerInlineSuccess);
            
            # Logging error
            $Bot->SendMessage(LogsChatID, "<b>New inline invoice request:</b>
    <b>User:</b>
        ID: {$inline_query->from->id}.
        First name: {$inline_query->from->first_name}.
        Last name: {$inline_query->from->last_name}.
        Username: {$inline_query->from->username}.
    <b>Query:</b> {$inline_query->query}.
    <b>Chat type:</b> {$inline_query->chat_type}.

    <b>JSON Error response:</b>
    <pre><code class=\"language-json\">
    {$answerInlineSuccess}
    </code></pre>

    You should send to the developer and forward this message to him", 0, $contact_the_dev);
            return false;
        }
        return true;
    }


    public function ShippingQueryHandler($shipping_query) : bool
    {  
        global $Bot;
        global $settings;
        global $contact_the_dev;

        $currentInvoice = $this->GetInvoiceByPayload($shipping_query->invoice_payload, $settings);
        
        # If the invoice supports all countries || If the selected country is one of 
        $countryResult = array_search($shipping_query->shipping_address->country_code, $currentInvoice->supported_countries);
        if (count($currentInvoice->supported_countries) === 0 || $countryResult !== false)
        {
            # Do not care about character case, And support all countries if arrays is empty
            if (count($currentInvoice->supported_cities) === 0 || in_array(strtolower($shipping_query->shipping_address->city), array_map('strtolower', $currentInvoice->supported_cities[$countryResult])))
            {
                $shipping = $Bot->AnswerShippingQuery($shipping_query->id, true, json_encode($currentInvoice->shipping_options));
                if ($shipping === false || $shipping->ok === false)
                {
                    # Logging
                    $Bot->SendMessage(LogsChatID, str_replace('{json-error}', $settings->answer_shipping_query_failed_log, json_encode($shipping)), 0, $contact_the_dev);
                }
            }
            else
            {
                $errorMsg = $settings->error_city_unavailable;
                $errorMsg = str_replace('{city}', $shipping_query->shipping_address->city, $errorMsg);
                $errorMsg = str_replace('{country}', $shipping_query->shipping_address->country_code, $errorMsg);
                $errorMsg = str_replace('{available_cities}', implode(', ', $currentInvoice->supported_cities[$countryResult]), $errorMsg);
                $Bot->AnswerShippingQuery($shipping_query->id, false, '', $errorMsg);
            }
        }
        else # Means country not supported, returns error
        {
            $errorMsg = $settings->error_country_unavailable;
            $errorMsg = str_replace('{country}', $shipping_query->shipping_address->country_code, $errorMsg);
            $errorMsg = str_replace('{available_countries}', implode(', ', $currentInvoice->supported_countries), $errorMsg);
            $Bot->AnswerShippingQuery($shipping_query->id, false, '', $errorMsg);
        }
        return true;
    }


    public function PreCheckoutQueryHandler(object $pre_checkout_query) : bool
    {
        global $Bot;
        global $settings;

        $currentInvoice = $this->GetInvoiceByPayload($pre_checkout_query->invoice_payload, $settings);
        
        # Check the limit of the product
        if ($currentInvoice->limit === 0)
        {
            $Bot->AnswerPreCheckoutQuery($pre_checkout_query->id, false, $settings->error_product_sold_out);
        }
        else
        {
            $Bot->AnswerPreCheckoutQuery($pre_checkout_query->id, true);
        }
        return true;
    }

    public function MyChatMemberHandler(object $my_chat_member) : bool
    {
        global $Bot;
        //global $settings;

        if ($my_chat_member->new_chat_member === 'member')
        {
            if ($my_chat_member->from->id === $my_chat_member->chat->id)
            {
                $Bot->SendMessage(LogsChatID, "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Started conversion with the bot.");
            }
            else
            {
                $Bot->SendMessage(LogsChatID, "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Added the bot to chat:
    {$my_chat_member->chat->title} [{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]");
            }
        }
        else if ($my_chat_member->new_chat_member === 'kicked')
        {
            $Bot->SendMessage(LogsChatID, "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Kicked the bot from chat:
    {$my_chat_member->chat->title} [{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]");
        }
        return true;
    }
        
    public function CallbackQueryHandler(object $callback_query) : bool
    {
        global $Bot;
        global $settings;
        
        # Only buttons callbacks are in this style: "Invoice_{$invoice->payload}"
        if ($this->StartsWith($callback_query->data, 'Invoice_'))
        {
            $providerData =
            [
                'requester_user_id' => $callback_query->from->id,
                'callback_query' => $callback_query->data
            ];

            $requestedInvoice = $this->GetInvoiceByPayload(substr($callback_query->data, 9, strlen($callback_query->data)), $settings);

            $photoHeight = $requestedInvoice->photo_height;
            $photoWidth = $requestedInvoice->photo_width;

            $Bot->SendInvoice($callback_query->message->chat->id, $requestedInvoice->title, $requestedInvoice->description, $requestedInvoice->payload,
                                    ProviderToken, $requestedInvoice->currency, json_encode($requestedInvoice->prices),
                                    $requestedInvoice->max_tip_amount, json_encode($requestedInvoice->suggested_tip_amounts), $requestedInvoice->start_param,
                                    json_encode($providerData), $requestedInvoice->photo_url, $photoHeight * $photoWidth, $photoWidth, $photoHeight,
                                    $requestedInvoice->need_name, $requestedInvoice->need_phone_number, $requestedInvoice->need_email,
                                    $requestedInvoice->need_shipping_address, $requestedInvoice->send_phone_number_to_provider,
                                    $requestedInvoice->send_email_to_provider, $requestedInvoice->is_flexible);

            $Bot->AnswerCallbackQuery($callback_query->id, $settings->succesed);
        }
        else
        {
            $Bot->AnswerCallbackQuery($callback_query->id, $settings->error_unknown_callback_query);
        }
        return true;
    }
        
        

    private function StartsWith(string $text, string $start)
    {
        if (substr($text, 0, strlen($start)) === $start)
        {
            return true;
        }
            
        return false;
    }

    public function EditedChannelPostHandler(object $edited_channel_post): bool
    {
        return false;   
    }

    public function EditedMessageHandler(object $edited_message) : bool
    {
        return false;
    }

    public function ChosenInlineQueryHandler(object $chosen_inline_query): bool
    {
        return false;
    }

    public function PollHandler(object $poll_answer): bool
    {
        return false;
    }

    public function PollAnswerHandler(object $poll_answer): bool
    {
        return false;
    }

    public function ChatMemberHandler(object $chat_member): bool
    {
        return false;
    }
}