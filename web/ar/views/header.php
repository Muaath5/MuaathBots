<header>
    <h2><a href="/index.php">الآليون العرب</a></h2>
    <nav>
        <ul>
            <li><a href="https://arabic-telegram-bots.herokuapp.com/avaliable-bots.php">الآليون المتوفرون</a></li>
            <li><a href="https://arabic-telegram-bots.herokuapp.com/telegram-bot-admin.php">مدير آلي على تيليجرام</a></li>
        </ul>
    </nav>
    <section>

    <?php

    if (isset($_ENV['SupportTelegramLogin']) && isset($_ENV['TelegramAccountURL']) && isset($_ENV['TgUserCookieName']) && isset($_ENV['TelegramLoginURL']))
    {
        if (getenv('SupportTelegramLogin') == true)
        {
            if (isset($_COOKIE[getenv('TgUserCookieName')]))
            {
                $user = json_decode($_COOKIE[getenv('TgUserCookieName')]);

                # Echo (View account) button
                echo "<a href=" . getenv('TelegramAccountURL') . ">";

                # Print image
                echo "<img src=\"{$user->photo_url}\" alt=\"صورة الملف الشخصي\"> {$user->first_name} {$user->last_name}</a>";
            }
            else
            {
                echo '<script async src="https://telegram.org/js/telegram-widget.js?14" data-telegram-login="ArabicTelegramContactBot" data-size="large" data-auth-url="' . getenv('TelegramLoginURL') . '" data-request-access="write"></script>';
            }   
        }
    }

    ?>

    </section>
</header>
    
    
