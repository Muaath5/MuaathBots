<html lang="ar" dir="rtl">
    <head>
        <?php
            include './views/head.html';
        ?>
    </head>
    <body>
        <?php
            include './views/header.php'
        ?>
        
        <main>
            <h1>الآليون المتوفرون:</h1>
            <ol>
                <?php
                function GetSubDirs(string $dir) : array
                {
                    $dir_content = scandir($dir);
                
                    // Remove . And .. subdirs
                    unset($dir_content[array_search('.', $dir_content, true)]);
                    unset($dir_content[array_search('..', $dir_content, true)]);

                    $sub_dirs = [];
                    foreach($dir_content as $file)
                    {
                        if (is_dir($dir.'/'.$file))
                        {
                            array_push($subdirs, $file);
                        }
                    }

                    return $sub_dirs;
                }
                
                $bots = GetSubDirs($_SERVER['DOCUMENT_ROOT'] . '/bots');

                foreach ($bots as $bot_username)
                {
                    echo '<li><a href="https://t.me/' . $bot_username . '">@' . $bot_username . '</a></li>';
                }
                ?>
            </ol>
        </main>
        
        <?php
            include 'footer.php'
        ?>
    </body>
</html>
