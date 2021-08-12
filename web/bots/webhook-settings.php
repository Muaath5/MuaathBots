<?php

require '/app/vendor/autoload.php';

use SimpleBotAPI\TelegramBot;
use SimpleBotAPI\TelegramException;
use SimpleBotAPI\TelegramFloodWait;
use SimpleBotAPI\TelegramChatMigrated;
use SimpleBotAPI\UpdatesHandler;

$BotDir = $_GET['bot'];

if (isset($_GET['token']))
{
    $Token = $_GET['token'];
}
else
{
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-unauthorized.php?bot={$BotDir}");
}

$Bot = new TelegramBot($Token);

if (isset($_GET['m']))
{

    if ($_GET['m'] === 'setWebhook')
    {
        $max_connections = 40;
        $ip_address = '';
        $allowed_updates = ['channel_post', 'message', 'my_chat_member', 'inline_query', 'callback_query'];
        
        if (isset($_GET['max_connections']))
        {
            $max_connections = $_GET['max_connections'];
        }

        $webhookResult = $Bot->SetWebhook([
            'url' => 'https://muaath-bots.herokuapp.com/bots/' . $BotDir . '/webhook.php?token=' . $Token,
            'max_connections' => $max_connections,
            'allowed_updates' => $allowed_updates
        ]);
        
        echo '<h2><code>setWebhook</code> method was called';
        if ($webhookResult == true)
        {
            echo 'Sucessfully</h2>';
        }
        else
        {
            echo "With error: <code>Error {$webhookResult->error_code}</code>:</h2>";
            echo "<pre>{$webhookResult->description}</pre>";
            echo '<br><br>';
            echo 'JSON is: <pre>' . json_encode($webhookResult) . '</pre>';
            echo '<br><br>';
            echo 'Include bot.php is:';
            var_dump($bot_include_result);
        }
    }
    else if ($_GET['m'] === 'deleteWebhook')
    {
        $drop_pending_update = false;
        if (isset($_GET['drop_pending_update']))
        {
            $drop_pending_update = $_GET['drop_pending_update'];
        }
        $Bot->DeleteWebhook([
            'drop_pending_updates' => $drop_pending_update
        ]);
    }
}

$getWhInfo = $Bot->GetWebhookInfo();
?>
<html lang="en" dir="ltr">

<header>
    <meta charset="utf-8">
    <title>Webhook settings</title>
</header>

<body style="margin: 0px; padding: 0px">
    <header style="background-color: deepskyblue; color: white; padding: 20px;">
        <h1>Admin webhook page</h1>
    </header>

    <main style="padding: 10px">
        <section>
            <form method="GET" action="./webhook-settings.php?m=setWebhook">

                <input type="hidden" name="token" value="<?php echo $Token ?>">
                <input type="hidden" name="bot" value="<?php echo $BotDir ?>">

                <label for="max_connections">Max connections</label>
                <input id="max_connections" type="number" name="max_connections" placeholder="Max connections" value="40" min="1" max="100"><br>
                <!--
                Hide IP address because Heroku url is better.
                <input type="text" name="ip_address" placeholder="IP Address">

                Allowed updates should be in backend only editing..
                Maybe it'll be added soon..
                <input type="text" name="allowed_updates" placeholder="Allowed updates (in JSON)"   >
                -->
                <button type="submit">Set Bot Webhook</button>
            </form>

            <form method="GET" action="./webhook-settings.php?m=deleteWebhook">

                <input type="hidden" name="token" value="<?php echo $Token ?>">
                <input type="hidden" name="bot" value="<?php echo $BotDir ?>">

                <label for="drop_pending_updates">Drop pending updates</label>
                <select id="drop_pending_updates" name="drop_pending_updates">
                    <option value="false" selected>No</option>
                    <option value="true">Yes</option>
                </select>
                <br><br>
                <button type="submit">Delete webhook</button>
            </form>
        </section>
        <hr>
        <h3>Webhook info</h3>
        <?php
        # Webhook info
        echo "<h3>Webhook Url: <code>{$getWhInfo->url}</code></h3>";
        echo "<h3>IP Address: <code>{$getWhInfo->ip_address}</code></h3>";
        echo "<h3>Has custom certificate: <code>{$getWhInfo->has_custom_certificate}</code></h3>";

        echo '<hr>';
        # Last error
        if (property_exists($getWhInfo, 'last_error_message'))
        {
            echo '<h3>Last error info:</h3>';
            echo "<h4>Last error:</h4> <h4 class=\"error\"><code>{$getWhInfo->last_error_message}</code></h4>";
            $lastErrorDateStr = date('YYYY-m-d g:i:s a', $getWhInfo->last_error_date);
            echo "<h4>Last error date:</h4> <h4 class=\"date-time\">$lastErrorDateStr</h4>";
        }
        echo "<h3>Pending update count: <code>{$getWhInfo->pending_update_count}</code></h3>";
        echo '<hr>';
        echo "<h3>Max connections: {$getWhInfo->max_connections}</h3>";
        echo "<h3>Allowed updates: {$getWhInfo->allowed_updates}</h3>";
        ?>
    </main>
</body>

</html>