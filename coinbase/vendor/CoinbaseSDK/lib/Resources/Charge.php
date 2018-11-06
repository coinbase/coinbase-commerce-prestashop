<?php
namespace CoinbaseSDK\Resources;

use CoinbaseSDK\Operations\CreateMethodTrait;
use CoinbaseSDK\Operations\ReadMethodTrait;
use CoinbaseSDK\Operations\SaveMethodTrait;

class Charge extends ApiResource
{
    use CreateMethodTrait, ReadMethodTrait, SaveMethodTrait;

    /**
     * @return string
     */
    public static function getResourcePath()
    {
        return 'charges';
    }

    public function hasMetadataParam($key)
    {
        return isset($this->attributes['metadata'][$key]);
    }

    public function getMetadataParam($key)
    {
        return isset($this->attributes['metadata'][$key]) ? $this->attributes['metadata'][$key] : null;
    }
}
