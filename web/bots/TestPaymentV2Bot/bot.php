<?php
declare(strict_types=1);

namespace MuaathBots;

require 'vendor/autoload.php';

use SimpleBotAPI\TelegramBot;
use SimpleBotAPI\UpdatesHandler;

/**
 * Test bot for Telegram Payments 2.0, Can be used for real payments
 * @version Bot API 5.3
 */
class TestPaymentV2Bot extends UpdatesHandler
{
    private TelegramBot $Bot;
    private string $ProviderToken;
    private int|string $LogsChatID;
    public $Settings;

    public function __construct(TelegramBot $bot, string $provider_token, float|string $logs_chat_id)
    {
        $this->Bot = $bot;
        $this->ProviderToken = $provider_token;
        $this->LogsChatID = $logs_chat_id;
        $this->Settings = json_decode(file_get_contents(__DIR__ . '/$this->Settings.json'));
    }

    # Functions
    private function GetInvoiceByPayload(string $payload, bool $returnIndexInArray = false) : array|object
    {
        for ($i = 0; $i < count($this->Settings->invoices); $i++)
        {
            if ($payload === $this->Settings->invoices[$i]->payload)
            {
                if ($returnIndexInArray)
                {
                    return array($this->Settings->invoices[$i], $i);
                }
                else
                {
                    return $this->Settings->invoices[$i];
                }
            }
        }
        return false;
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
            
            $senderChat = $message->from;
            if (property_exists($message, 'text'))
            {    
                if ($message->text[0] === '/')
                {
                    return $this->CommandsHandler($message, $this->Settings);
                }
            }
            else if (property_exists($message, 'successful_payment'))
            {
                $resultInvoice = $this->GetInvoiceByPayload($message->successful_payment->invoice_payload, $this->Settings, true);

                # Handle limits
                if ($resultInvoice[0]->limit > 0)
                {
                    # Write the new invoice limit to the file:
                    $this->Settings->invoices[$resultInvoice[1]]->limit--;
                    // file_put_contents(SettingsFilePath, json_encode($this->Settings));
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
                
                $this->Bot->SendMessage([
                    'chat_id' => $this->LogsChatID,
                    'text' => $info,
                    'parse_mode' => 'HTML'
                ]);
                
                $this->Bot->SendMessage([
                    'chat_id' => $senderChat->id,
                    'text' => $this->Settings->successful_payment_message,
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
        return true;
    }
        
    private function CommandsHandler(object $message) : bool
    {

        $senderChat = $message->chat;

        try
        {
            switch ($message->text)
            {   
                case '/start':
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $this->Settings->start_message,
                        'parse_mode' => 'HTML',
                        'reply_markup' => json_encode(['inline_keyboard' => [[
                            []
                        ]]])
                    ]);
                    break;

                case '/project':
                case '/start project':
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $this->Settings->project_message,
                        'parse_mode' => 'HTML'
                    ]);
                    break;

                case '/help':
                case '/start help':
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $this->Settings->help_message,
                        'parse_mode' => 'HTML'
                    ]);
                    break;
                
                case '/inline':
                case '/start inline':
                    $inlineQueryKeyboard = json_encode(
                    [
                        'inline_keyboard' =>
                        [
                            [[
                                'text' => $this->Settings->inline_chat_button,
                                'switch_inline_query' => 'payment'
                            ]],
                            [[
                                'text' => $this->Settings->inline_current_chat_button,
                                'switch_inline_query_current_chat' => 'payment'
                            ]]
                        ]
                    ]);
                    $this->Bot->SendMessage([
                        'chat_id' => $message->chat->id,
                        'text' => $this->Settings->inline_message,
                        'reply_markup' => $inlineQueryKeyboard
                    ]);
                    break;

                case '/invoice':
                case '/start invoice':
                    $providerData =
                    [
                        'payload' => $this->Settings->invoices[0]->payload,
                        'user_id' => $senderChat->id,
                        'prices' => $this->Settings->invoices[0]->prices
                    ];
                    
                    # $invoice Should be Message object, If an error occurd 
                    $photoWidth = $this->Settings->invoices[0]->photo_width;
                    $photoHeight = $this->Settings->invoices[0]->photo_height;
                    $this->Bot->SendMessage([
                        'chat_id' => $senderChat->id,
                        'text' => $this->Settings->warning_message,
                        'parse_mode' => 'HTML'
                    ]);
                    $mainInv = $this->Settings->invoices[0];
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
                        'is_flexible' => $mainInv->is_flexible
                    ]);
                    
                    # Logging
                    $newInvoiceRequestText = "New invoice request 📲:
                    First name: {$senderChat->first_name}.
                    Last name: {$senderChat->last_name}.
                    Username: @{$senderChat->username} ({$senderChat->id}).
                    Language (null if not available): {$senderChat->language_code}.
                    Which command was used: {$message->text}.";
                    
                    $this->Bot->SendMessage([
                        'chat_id' => $this->LogsChatID,
                        'text' => $newInvoiceRequestText
                    ]);
                        

                    break;
                    
                default:
                    for ($i = 0; $i < count($this->Settings->invoices); $i++)
                    {
                        if ($message->text === "/start {$this->Settings->invoices[$i]->start_param}")
                        {
                            $providerData =
                            [
                                'user_id' => $message->from->id,
                                'prices' => $this->Settings->invoices[$i]->prices,
                                'payload' => $this->Settings->invoices[$i]->payload
                            ];
                            
                            $photoWidth = $this->Settings->invoices[$i]->photo_width;
                            $photoHeight = $this->Settings->invoices[$i]->photo_height;
                            $this->Bot->SendInvoice([
                                'chat_id' => $senderChat->id,
                                'title' => $this->Settings->invoices[$i]->title,
                                'description' => $this->Settings->invoices[$i]->description,
                                'payload' => $this->Settings->invoices[$i]->payload,
                                'provider_token' => $this->ProviderToken,
                                'currency' => $this->Settings->invoices[$i]->currency,
                                'prices' => json_encode($this->Settings->invoices[$i]->prices),
                                'max_tip_amount' => $this->Settings->invoices[$i]->max_tip_amount,
                                'suggested_tip_amounts' => json_encode($this->Settings->invoices[$i]->suggested_tip_amounts),
                                'start_param' => $this->Settings->invoices[$i]->start_param,
                                'provider_data' => json_encode($providerData),
                                'photo_url' => $this->Settings->invoices[$i]->photo_url, 
                                'photo_size' => $photoWidth * $photoHeight,
                                'photo_width' => $photoWidth,
                                'photo_height' => $photoHeight,
                                'need_name' => $this->Settings->invoices[$i]->need_name,
                                'need_phone_number' => $this->Settings->invoices[$i]->need_phone_number,
                                'need_email' => $this->Settings->invoices[$i]->need_email,
                                'need_shipping_address' => $this->Settings->invoices[$i]->need_shipping_address,
                                'send_phone_number_to_provider' => $this->Settings->invoices[$i]->send_phone_number_to_provider,
                                'send_email_to_provider' => $this->Settings->invoices[$i]->send_email_to_provider,
                                'is_flexible' => $this->Settings->invoices[$i]->is_flexible]);
                            
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
        # Delete sent command
        $this->Bot->DeleteMessage([
            'chat_id' => $channel_post->chat->id,
            'message_id' => $channel_post->message_id
        ]);
        return $this->MessageHandler($channel_post);
    }

    public function InlineQueryHandler($inline_query) : bool
    {   
        $results = [];
        for ($i = 0; $i < count($this->Settings->invoices); $i++)
        {   
            array_push($results, 
            [
                'type' => 'article',
                'id' => $this->Settings->invoices[$i]->payload,
                'title' => $this->Settings->invoices[$i]->title,
                'description' => $this->Settings->invoices[$i]->description,
                'thumb_url' => $this->Settings->invoices[$i]->photo_url,
                'thumb_width' => $this->Settings->invoices[$i]->photo_width,
                'thumb_height' => $this->Settings->invoices[$i]->photo_height,
                'input_message_content' =>
                [
                    'title' => $this->Settings->invoices[$i]->title,
                    'description' => $this->Settings->invoices[$i]->description,
                    'payload' => $this->Settings->invoices[$i]->payload,
                    'provider_token' => $this->ProviderToken,
                    'currency' => $this->Settings->invoices[$i]->currency,
                    'prices' => $this->Settings->invoices[$i]->prices,
                    'photo_url' => $this->Settings->invoices[$i]->photo_url,
                    'photo_width' => $this->Settings->invoices[$i]->photo_width,
                    'photo_height' => $this->Settings->invoices[$i]->photo_height,
                    'photo_size' => $this->Settings->invoices[$i]->photo_width * $this->Settings->invoices[$i]->photo_height,
                    'max_tip_amount' => $this->Settings->invoices[$i]->max_tip_amount,
                    'suggested_tip_amounts' => $this->Settings->invoices[$i]->suggested_tip_amounts,
                    'provider_data' => json_encode(
                    [
                        'sender_user_id' => $inline_query->from->id,
                        'chat_type' => $inline_query->chat_type,
                        'query' => $inline_query->query,
                        'payload' => $this->Settings->invoices[$i]->payload
                    ]),
                    'need_name' => $this->Settings->invoices[$i]->need_name,
                    'need_phone_number' => $this->Settings->invoices[$i]->need_phone_number,
                    'need_email' => $this->Settings->invoices[$i]->need_email,
                    'need_shipping_address' => $this->Settings->invoices[$i]->need_shipping_address,
                    'is_flexible' => $this->Settings->invoices[$i]->is_flexible
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
        catch (\Exception $ex)
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
                'parse_mode' => 'HTML'
            ]);
            return false;
        }
        return true;
    }


    public function ShippingQueryHandler($shipping_query) : bool
    {  

        $currentInvoice = $this->GetInvoiceByPayload($shipping_query->invoice_payload, $this->Settings);
        
        # If the invoice supports all countries || If the selected country is one of 
        $countryResult = array_search($shipping_query->shipping_address->country_code, $currentInvoice->supported_countries);
        try
        {

            if (count($currentInvoice->supported_countries) === 0 || $countryResult !== false)
            {
                # Do not care about character case, And support all countries if arrays is empty
                if (count($currentInvoice->supported_cities) === 0 || in_array(strtolower($shipping_query->shipping_address->city), array_map('strtolower', $currentInvoice->supported_cities[$countryResult])))
                {
                    $this->Bot->AnswerShippingQuery([
                        'shipping_query_id' => $shipping_query->id,
                        'ok' => true,
                        'shipping_options' => json_encode($currentInvoice->shipping_options)
                    ]);
                    
                    
                }
                else
                {
                    $errorMsg = $this->Settings->error_city_unavailable;
                    $errorMsg = str_replace('{city}', $shipping_query->shipping_address->city, $errorMsg);
                    $errorMsg = str_replace('{country}', $shipping_query->shipping_address->country_code, $errorMsg);
                    $errorMsg = str_replace('{available_cities}', implode(', ', $currentInvoice->supported_cities[$countryResult]), $errorMsg);
                    $this->Bot->AnswerShippingQuery([
                        'shipping_query_id' => $shipping_query->id,
                        'ok' => false,
                        'error_message' => $errorMsg
                    ]);
                }
            }
            else # Means country not supported, returns error
            {
                $errorMsg = $this->Settings->error_country_unavailable;
                $errorMsg = str_replace('{country}', $shipping_query->shipping_address->country_code, $errorMsg);
                $errorMsg = str_replace('{available_countries}', implode(', ', $currentInvoice->supported_countries), $errorMsg);
                $this->Bot->AnswerShippingQuery([
                    'shipping_query_id' => $shipping_query->id,
                    'ok' => false, 
                    'error_message' => $errorMsg
                ]);
            }
        }
        catch (\Exception $ex)
        {
            # Logging
            $this->Bot->SendMessage([
                'chat_id' => $this->LogsChatID,
                'text' => str_replace('{json-error}', $this->Settings->answer_shipping_query_failed_log, $ex->__toString()),
            ]);
        }
        return true;
    }


    public function PreCheckoutQueryHandler(object $pre_checkout_query) : bool
    {
        $currentInvoice = $this->GetInvoiceByPayload($pre_checkout_query->invoice_payload, $this->Settings);
        
        # Check the limit of the product
        if ($currentInvoice->limit === 0)
        {
            $this->Bot->AnswerPreCheckoutQuery([
                'pre_checkout_query_id' => $pre_checkout_query->id,
                'ok' => false,
                'error_message' => $this->Settings->error_product_sold_out
            ]);
        }
        else
        {
            $this->Bot->AnswerPreCheckoutQuery([
                'pre_checkout_query_id' => $pre_checkout_query->id,
                'ok' => true
            ]);
            $currentInvoice->limit--;
            
        }
        return true;
    }

    public function MyChatMemberHandler(object $my_chat_member) : bool
    {
        //global $this->Settings;

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
        
    public function CallbackQueryHandler(object $callback_query) : bool
    {        
        # Only buttons callbacks are in this style: "Invoice_{$invoice->payload}"
        if ($this->StartsWith($callback_query->data, 'Invoice_'))
        {
            $providerData =
            [
                'requester_user_id' => $callback_query->from->id,
                'callback_query' => $callback_query->data
            ];

            $requestedInvoice = $this->GetInvoiceByPayload(substr($callback_query->data, 9, strlen($callback_query->data)), $this->Settings);

            $photoHeight = $requestedInvoice->photo_height;
            $photoWidth = $requestedInvoice->photo_width;

            $this->Bot->SendInvoice($callback_query->message->chat->id, $requestedInvoice->title, $requestedInvoice->description, $requestedInvoice->payload,
                                    $this->ProviderToken, $requestedInvoice->currency, json_encode($requestedInvoice->prices),
                                    $requestedInvoice->max_tip_amount, json_encode($requestedInvoice->suggested_tip_amounts), $requestedInvoice->start_param,
                                    json_encode($providerData), $requestedInvoice->photo_url, $photoHeight * $photoWidth, $photoWidth, $photoHeight,
                                    $requestedInvoice->need_name, $requestedInvoice->need_phone_number, $requestedInvoice->need_email,
                                    $requestedInvoice->need_shipping_address, $requestedInvoice->send_phone_number_to_provider,
                                    $requestedInvoice->send_email_to_provider, $requestedInvoice->is_flexible);

            $this->Bot->AnswerCallbackQuery($callback_query->id, $this->Settings->succesed);
        }
        else
        {
            $this->Bot->AnswerCallbackQuery([
                'callback_query_id' => $callback_query->id,
                'text' => $this->Settings->errors->unknown_callback_query
            ]);
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