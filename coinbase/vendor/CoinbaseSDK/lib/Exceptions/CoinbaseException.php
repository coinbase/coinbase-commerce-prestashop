<?php
namespace CoinbaseSDK\Exceptions;

class CoinbaseException extends \Exception
{
    public static function getClassName()
    {
        return get_called_class();
    }
}
