<html dir="ltr" lang="en">
    <head>
        <meta charset="utf-8">
        <title>403 - Unauthorized</title>
        <link rel="stylesheet" type="text/css" href="/css/main.css">
    </head>
    <body>
        <h1 class="http-error center">Error 403 - Unauthorized</h1>
        <h2 class="error-missed-parameter">The GET paramter: <code>token</code> is missed</h2>
        <h3><a href="https://t.me/<?php if (isset($_ENV['BotChannel'])) {echo getenv('BotChannel');} else { echo "MuaathBots"; }?>">Bot Channel</a></h3>
        <form method="GET" action="./<?php echo BotDirectory;?>/webhook.php">
            <input name="token" type="password" placeholder="Bot token obtained from @BotFather">
            <button type="submit">Authenticate</button>
        </form>
    </body>
</html>