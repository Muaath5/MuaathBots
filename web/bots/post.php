<?php

require '/app/vendor/autload.php';

use SimpleBotAPI\TelegramBot;

# Parameters (GET || POST):
# host   => Host name
# db     => Database name
# user   => Database username
# pass   => Database password
# table  => Databse Table name contains chat IDs
# col_id => Database column that contains chat IDs to post to them.
# token  => Bot token that will send 
# out    => Output type, Should be "json" OR "ui", This will output a result for posting
# text   => Text will be broadcasted, You can use {first_name}, {last_name}, {chat_id}, Or {username}

# type   => [Optional], This should be "chats" OR "users", The type of IDs to post

# Configuration
$db_name = $_REQUEST['db'];
$host = $_REQUEST['host'];

# Source
$table_name = $_REQUEST['table'];
$column_name = $_REQUEST['col_id'];

try
{   
    $db = new PDO("pgsql:host=$host;dbname=$db_name", $_REQUEST['user'], $_REQUEST['pass']);
}
catch (PDOException $ex)
{
    echo "<h1 class=\"error\">Error {$ex->getCode()}:</h1>";
    echo "<h2>{$ex->getMessage()}</h2>";
    exit;
}

try
{
    $result = $db->query("SELECT * FROM $table_name");
}
catch (PDOException $ex)
{
    echo "<h1 class=\"error\">Error {$ex->getCode()}:</h1>";
    echo "<h2>{$ex->getMessage()}</h2>";
    exit;
}

$Bot = new TelegramBot($_REQUEST['token']);

# Post to all of these IDs
foreach ($result as $row)
{
    $chat = $Bot->GetChat($row[$column_name]);
    $text = $_REQUEST['text'];

    # Username
    if (!property_exists($chat, 'username'))
    {
        str_replace('{username}', '<a href="">' . $chat->first_name . '</a>', $text);
    }
    else
    {
        str_replace('{username}', $chat->username, $text);
    }

    # First Name
    if (property_exists($chat, 'first_name')) str_replace('{first_name}', $chat->first_name, $text);

    $Bot->SendMessage([
        'chat_id' => $chat->id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ]);
}