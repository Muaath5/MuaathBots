# TestPaymentV2Bot
Test payments 2.0 - [@TestPaymentsV2Bot](https://t.me/TestPaymentV2Bot), By [Muaath Alqarni](https://t.me/Muaath_5).
This bot uses **Bot API 5.2**, Supports _Telegram Payments 2.0_.

## Bot settings
| Setting                | Value    |
|------------------------|----------|
| Supports Inline query  | Yes      |
| Inline query feedback  | No       |
| Inline query location  | No       |
| Linked website         | No       |
| Payments               | Yes      |
| Group privacy          | No       |
| Allow groups           | Yes      |

## Features
* Supports inline queries
* Supports (User/Admin) modes
* Supports multiple invoices
* Supports deep linking
* Supports Reply keyboards
* Supports Payments 2.0
* Has logs channel for errors & payments
<!--
* Can delete and add invocies via bot
* Can has limits on selling the products
-->

## Bot commands
| Command     | Description                                                       | Allowed users | Style                              |
|-------------|-------------------------------------------------------------------|---------------|------------------------------------| 
| `/start`    | Sends start message with Main bot keyboard                        | Any           | `/start`                           |
| `/invoice`  | Sends main Invoice in `settings.json`                             | Any           | `/invoice` & `/invoice {$payload}` |
| `/inline`   | Sends a message with an `InlineKeyboardMarkup` for inline queries | Any           | `/inline` & `/start inline`        |
| `/project`  | Returns the developer info and the Github source code repo link   | Any           | `/project` & `/start project`      |
| `/help`     | Returns info about commands and what can bot do                   | Any           | `/help` & `/start help`            |
| `/settings` | Returns info about current invoices                               | Admin         | `/settings`                        |
| `/admin`    | Shows admin keyboard (Selective)                                  | Admin         | `/admin`                           |
<!--
> Coming soon..
| `/addinv`   | Adds an invoice                                                   | Creator       | `/addinv {$JSON}`                  |
| `/delinv`   | Deletes an invoice by payload                                     | Creator       | `delinv {$payload}`                |
-->


## Test payments 2.0 FAQ
| Question                            | Answer                                                                |
|-------------------------------------|-----------------------------------------------------------------------|
| What's used Bot API version?        | Bot API 5.2                                                           |
| Which payment provider is choosed?  | Stripe.                                                               |
| Does it real payment?               | No, It's a test payment. See: Test payments in Stripe.                |
| Can the Bot get the payment info?   | No, It's not accessed by Bot API. You can check the source code file. |
| What's the data that bot collects?  | The bot collects User info, Shipping info, Invoice info.              |
| Which place bot stores the data in? | The bots send logs & payments to selected Telegram chat               |

## Issues
If you find an issue about this bot: [Open issue for Test payments 2.0 bot]()