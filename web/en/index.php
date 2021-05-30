<html lang="en">
    <head>
        <?php include './views/head.html' ?>
        <title>Main page</title>
    </head>
  
    <body>
        <?php
            include './views/header.php';
        ?>
        
        <main class="has-view">
            <section class="telegram-view">
                <h2>Welcome to Muaath bots!</h2>
            </section>
            <hr>
            <article>
                <h2>Where can I find your bots & projects</h2>
                We have a channel in Telegram, Also we have GitHub organization.
                <br><br>
                
                <a href="https://t.me/ArabicTelegramBots" class="telegram-button">
                    <pic src="https://telegram.org/img/t_logo.png" alt="Telegram logo" width="32px" height="32px"></pic>
                    Out Telegram channel
                </a>
                    
                <a href="https://t.me/ArabicContactBot" class="telegram-button">
                    <pic src="https://telegram.org/img/t_logo.png" alt="Telegram logo" width="32px" height="32px"></pic>
                    Contact us on Telegram
                </a>
                
            </article>
            <hr>
            <article>
                <h2>What tools are used to create bots?</h2>
                We are using these tools:
                <ol>
                    <li><a href="https://heroku.com">Heroku</a> free host</li>
                    <li>PHP Programming language</li>
                    <li><a href="https://github.com">GitHub</a> to save open source code</li>
                </ol>
                We are not using any third-party for Bot API library, And work on creating a small library and publish it.
            </article>
        </main>
        
        <?php
            include './views/footer.php';
        ?>
    </body>
</html>
