<?php
# Parameters (GET || POST):
# host   => Host name
# db     => Database name
# user   => Database username
# pass   => Database password
# table  => Databse Table name contains chat IDs
# col_id => Database column that contains chat IDs to post to them.
# token  => Bot token that will send 
# out    => Output type, Should be "json" OR "ui", This will output a result for posting
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
    $code = $ex->getCode();
    $description = $ex->getMessage();
    echo "<h1 class=\"error\">Error $code:</h1>";
    echo "<h2>$description</h2>";
    exit;
}

try
{
    $db->query("SELECT $column_name FROM $table_name");
}
catch (PDOException)
{

}