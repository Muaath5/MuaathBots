<?php

class BotAPIException extends Exception
{
    public ?ErrorParameters $parameters = null;
}