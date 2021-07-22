<?php
declare(strict_types=1);

include_once $_SERVER['DOCUMENT_ROOT'] . '/bot-api/BotAPIExceptions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/bot-api/UpdatesHandler.php';

/**
 * Test bot for Telegram Payments 2.0, Can be used for real payments
 * @version Bot API 5.3
 */
class TestPaymentV2Bot extends UpdatesHandler
{
    private TelegramBot $Bot;
    private string $ProviderToken;
    private int|string $LogsChatID;

    public function __construct(TelegramBot $bot, string $provider_token, float|string $logs_chat_id)
    {
        $this->Bot = $bot;
        $this->ProviderToken = $provider_token;
        $this->LogsChatID = $logs_chat_id;
    }

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

    # Update handlers
    public function MessageHandler($message) : bool
    {
        global $settings;
        global $contact_the_dev;
        global $bot_admins;

        try
        {

            if (in_array($message->from->id, $bot_admins))
            {
                # Nothing here now..
            }
            
            $senderChat = $message->from;
            if (property_exists($message, 'text'))
            {    
                if ($message->text[0] === '/')
                {
                    return $this->CommandsHandler($message, $settings);
                }
            }
            else if (property_exists($message, 'successful_payment'))
            {
                $resultInvoice = $this->GetInvoiceByPayload($message->successful_payment->invoice_payload, $settings, true);
                $this->Bot->SendMessage($this->LogsChatID, "JSON for resultInvoice is: <code>" . json_encode($resultInvoice) . '</code>');
                $successedInvoice = $resultInvoice[0];
                # Handle limits
                if ($resultInvoice[0]->limit > 0)
                {
                    $settings->invoices[$resultInvoice[1]]->limit--;
                    //file_put_contents(SettingsFilePath, json_encode($settings));
                }
                else if ($resultInvoice[0]->limit != -1)
                {
                    $this->Bot->SendMessage($this->LogsChatID, "Error, {$message->successful_payment->invoice_payload} was sold out over the limit", 0, $contact_the_dev);
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
    Total amount: {$floatTotalAmount} {$message->successful_payment->currency}.
    Telegram payment ID: {$message->successful_payment->telegram_payment_charge_id}.
    Provider payment ID: {$message->successful_payment->provider_payment_charge_id}";
                
                $this->Bot->SendMessage($this->LogsChatID, $info);
                
                $this->Bot->SendMessage($senderChat->id, $settings->successful_payment_message, $message->message_id);
            }
        }
        catch (TelegramException $ex)
        {
            // Log error in logs channel

            return false;
        }
    }
        
    private function CommandsHandler(object $message, object $settings) : bool
    {
        global $contact_the_dev;

        $senderChat = $message->chat;

        try
        {
            switch ($message->text)
            {   
                case '/start':
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $settings->start_message,
                        'pase_mode' => 'Markdown'
                    ]);
                    break;

                case '/project' || '/start project':
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $settings->project_message,
                        'pase_mode' => 'Markdown'
                    ]);
                    break;

                case '/help' || '/start help':
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $settings->help_message,
                        'pase_mode' => 'Markdown'
                    ]);
                    break;
                
                case '/inline' || '/start inline':
                    $inlineQueryKeyboard = json_encode(
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
                    ]);
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $settings->inline_message,
                        'reply_markup' => $inlineQueryKeyboard
                    ]);
                            
                case '/invoice' || '/start invoice':
                    $providerData =
                    [
                        'payload' => $settings->invoices[0]->payload,
                        'user_id' => $senderChat->id,
                        'prices' => $settings->invoices[0]->prices
                    ];
                    
                    
                    # $invoice Should be Message object, If an error occurd 
                    $photoWidth = $settings->invoices[0]->photo_width;
                    $photoHeight = $settings->invoices[0]->photo_height;
                    $this->Bot->SendMessage($senderChat->id, $settings->warning_message);
                    $mainInv = $settings->invoices[0];
                    $this->Bot->SendInvoice([
                        'chat_id' => $senderChat->id,
                        'title' => $mainInv->title,
                        'description' => $mainInv->description,
                        'payload' => $mainInv->payload,
                        'provider_token' => $this->ProviderToken,
                        'currency' => $mainInv->currency,
                        'prices' => json_encode($mainInv->prices),
                        'max_tip_amount' => $mainInv->max_tip_amount,
                        'suggested_tip_amounts' => json_encode($mainInv->suggested_tip_amounts),
                        'start_param' => $mainInv->start_param,
                        'provider_data' => json_encode($providerData),
                        'photo_url' => $mainInv->photo_url, 
                        'photo_size' => $photoWidth * $photoHeight,
                        'photo_width' => $photoWidth,
                        'photo_height' => $photoHeight,
                        'need_name' => $mainInv->need_name,
                        'need_phone_number' => $mainInv->need_phone_number,
                        'need_email' => $mainInv->need_email,
                        'need_shipping_address' => $mainInv->need_shipping_address,
                        'send_phone_number_to_provider' => $mainInv->send_phone_number_to_provider,
                        'send_email_to_provider' => $mainInv->send_email_to_provider,
                        'is_flexible' => $mainInv->is_flexible]);
                    
                    # Logging
                    $newInvoiceRequestText = "New invoice request 📲:
                    First name: {$senderChat->first_name}.
                    Last name: {$senderChat->last_name}.
                    Username: @{$senderChat->username} ({$senderChat->id}).
                    Language (null if not available): {$senderChat->language_code}.
                    Which command was used: {$message->text}.";
                    
                    $newInvoiceLogMsg = $this->Bot->SendMessage($this->LogsChatID, $newInvoiceRequestText);
                    
                default:
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
                            $this->Bot->SendInvoice([
                                'chat_id' => $senderChat->id,
                                'title' => $settings->invoices[$i]->title,
                                'description' => $settings->invoices[$i]->description,
                                'payload' => $settings->invoices[$i]->payload,
                                'provider_token' => $this->ProviderToken,
                                'currency' => $settings->invoices[$i]->currency,
                                'prices' => json_encode($settings->invoices[$i]->prices),
                                'max_tip_amount' => $settings->invoices[$i]->max_tip_amount,
                                'suggested_tip_amounts' => json_encode($settings->invoices[$i]->suggested_tip_amounts),
                                'start_param' => $settings->invoices[$i]->start_param,
                                'provider_data' => json_encode($providerData),
                                'photo_url' => $settings->invoices[$i]->photo_url, 
                                'photo_size' => $photoWidth * $photoHeight,
                                'photo_width' => $photoWidth,
                                'photo_height' => $photoHeight,
                                'need_name' => $settings->invoices[$i]->need_name,
                                'need_phone_number' => $settings->invoices[$i]->need_phone_number,
                                'need_email' => $settings->invoices[$i]->need_email,
                                'need_shipping_address' => $settings->invoices[$i]->need_shipping_address,
                                'send_phone_number_to_provider' => $settings->invoices[$i]->send_phone_number_to_provider,
                                'send_email_to_provider' => $settings->invoices[$i]->send_email_to_provider,
                                'is_flexible' => $settings->invoices[$i]->is_flexible]);
                            
                            # Logging
                            $newInvoiceRequestText = "New invoice request 📲:
                            First name: {$senderChat->first_name}.
                            Last name: {$senderChat->last_name}.
                            Username: @{$senderChat->username} ({$senderChat->id}).
                            Language (null if not available): {$senderChat->language_code}.
                            Which command was used: {$message->text}.";
                            
                            $this->Bot->SendMessage([
                                'chat_id' => $this->LogsChatID,
                                'text' => $newInvoiceRequestText,
                            ]);
                            
                            break;
                        }
                    }
                    break;
            
            }

        }
        catch (Exception $ex)
        {
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => $ex,
                'parse_mode' => 'Markdown',
                'reply_markup' => $contact_the_dev
            ]);
            return false;
        }
        return true;
    }

    public function ChannelPostHandler($channel_post) : bool
    {
        # Delete sent command
        $this->Bot->DeleteMessage([
            'chat_id' => $channel_post->chat->id,
            'message_id' => $channel_post->message_id
        ]);
        return $this->MessageHandler($channel_post);
    }

    public function InlineQueryHandler($inline_query) : bool
    {
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
                    'provider_token' => $this->ProviderToken,
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
        
        try
        {
            $this->Bot->AnswerInlineQuery([
                'inline_query_id' => $inline_query->id,
                'results' => $results,
                'switch_pm_text' => 'Main invoice',
                'switch_pm_parameter' => 'invoice'
            ]);    
        }
        catch (Exception $ex)
        {
            # Logging error
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => "<b>New inline invoice request:</b>
    <b>User:</b>
        ID: {$inline_query->from->id}.
        First name: {$inline_query->from->first_name}.
        Last name: {$inline_query->from->last_name}.
        Username: {$inline_query->from->username}.
    <b>Query:</b> {$inline_query->query}.
    <b>Chat type:</b> {$inline_query->chat_type}.
    
    <b>Error:</b>
    <i>{$ex}</i>

    You should send to the developer and forward this message to him",
                'parse_mode' => 'Markdown',
                'reply_markup' => $contact_the_dev
            ]);
            return false;
        }
        return true;
    }


    public function ShippingQueryHandler($shipping_query) : bool
    {  
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
                $shipping = $this->Bot->AnswerShippingQuery($shipping_query->id, true, json_encode($currentInvoice->shipping_options));
                if ($shipping === false || $shipping->ok === false)
                {
                    # Logging
                    $this->Bot->SendMessage($this->LogsChatID, str_replace('{json-error}', $settings->answer_shipping_query_failed_log, json_encode($shipping)), 0, $contact_the_dev);
                }
            }
            else
            {
                $errorMsg = $settings->error_city_unavailable;
                $errorMsg = str_replace('{city}', $shipping_query->shipping_address->city, $errorMsg);
                $errorMsg = str_replace('{country}', $shipping_query->shipping_address->country_code, $errorMsg);
                $errorMsg = str_replace('{available_cities}', implode(', ', $currentInvoice->supported_cities[$countryResult]), $errorMsg);
                $this->Bot->AnswerShippingQuery($shipping_query->id, false, '', $errorMsg);
            }
        }
        else # Means country not supported, returns error
        {
            $errorMsg = $settings->error_country_unavailable;
            $errorMsg = str_replace('{country}', $shipping_query->shipping_address->country_code, $errorMsg);
            $errorMsg = str_replace('{available_countries}', implode(', ', $currentInvoice->supported_countries), $errorMsg);
            $this->Bot->AnswerShippingQuery($shipping_query->id, false, '', $errorMsg);
        }
        return true;
    }


    public function PreCheckoutQueryHandler(object $pre_checkout_query) : bool
    {
        global $settings;

        $currentInvoice = $this->GetInvoiceByPayload($pre_checkout_query->invoice_payload, $settings);
        
        # Check the limit of the product
        if ($currentInvoice->limit === 0)
        {
            $this->Bot->AnswerPreCheckoutQuery($pre_checkout_query->id, false, $settings->error_product_sold_out);
        }
        else
        {
            $this->Bot->AnswerPreCheckoutQuery($pre_checkout_query->id, true);
        }
        return true;
    }

    public function MyChatMemberHandler(object $my_chat_member) : bool
    {
        //global $settings;

        if ($my_chat_member->new_chat_member === 'member')
        {
            if ($my_chat_member->from->id === $my_chat_member->chat->id)
            {
                $this->Bot->SendMessage($this->LogsChatID, "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Started conversion with the bot.");
            }
            else
            {
                $this->Bot->SendMessage($this->LogsChatID, "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Added the bot to chat:
    {$my_chat_member->chat->title} [{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]");
            }
        }
        else if ($my_chat_member->new_chat_member === 'kicked')
        {
            $this->Bot->SendMessage($this->LogsChatID, "{$my_chat_member->from->first_name} [{$my_chat_member->from->username}, <code>{$my_chat_member->from->id}</code>] Kicked the bot from chat:
    {$my_chat_member->chat->title} [{$my_chat_member->chat->username}, <code>{$my_chat_member->chat->id}</code>]");
        }
        return true;
    }
        
    public function CallbackQueryHandler(object $callback_query) : bool
    {
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

            $this->Bot->SendInvoice($callback_query->message->chat->id, $requestedInvoice->title, $requestedInvoice->description, $requestedInvoice->payload,
                                    $this->ProviderToken, $requestedInvoice->currency, json_encode($requestedInvoice->prices),
                                    $requestedInvoice->max_tip_amount, json_encode($requestedInvoice->suggested_tip_amounts), $requestedInvoice->start_param,
                                    json_encode($providerData), $requestedInvoice->photo_url, $photoHeight * $photoWidth, $photoWidth, $photoHeight,
                                    $requestedInvoice->need_name, $requestedInvoice->need_phone_number, $requestedInvoice->need_email,
                                    $requestedInvoice->need_shipping_address, $requestedInvoice->send_phone_number_to_provider,
                                    $requestedInvoice->send_email_to_provider, $requestedInvoice->is_flexible);

            $this->Bot->AnswerCallbackQuery($callback_query->id, $settings->succesed);
        }
        else
        {
            $this->Bot->AnswerCallbackQuery($callback_query->id, $settings->error_unknown_callback_query);
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