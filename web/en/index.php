<html lang="en">
    <head>
        <?php include './views/head.html' ?>
        <title></title>
    </head>
  
    <body>
        <?php
            include "header.php";
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
                    <pic src="https://telegram.org/img/t_logo.png" alt="شعار تيليجرام" width="32px" height="32px"></pic>
                    Out Telegram channel
                </a>
                    
                <a href="https://t.me/ArabicContactBot" class="telegram-button">
                    <pic src="https://telegram.org/img/t_logo.png" alt="شعار تيليجرام" width="32px" height="32px"></pic>
                    Contact us on Telegram
                </a>
                
            </article>
            <hr>
            <article>
                <h2>ما الأدوات المستعلمة في الآليين؟</h2>
                نستعمل عددًا من الأدوات، منها:
                <ol>
                    <li>استضافة موقع <a href="https://heroku.com">Heroku</a></li>
                    <li>لغة برمجة PHP</li>
                </ol>
                ونحن لا نستعمل أي مكتبة خارجية، ونسعى لتطوير مكتبتنا الخاصة، ثم نشرها.
            </article>
        </main>
        
        <?php
            include "footer.php";
        ?>
    </body>
</html>
