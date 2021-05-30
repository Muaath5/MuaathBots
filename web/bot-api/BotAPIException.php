<?php
include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/ErrorParameters.php';
class BotAPIException extends Exception
{
    public ?ErrorParameters $parameters = null;
}