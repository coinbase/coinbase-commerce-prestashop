<?php
namespace CoinbaseSDK\Resources;

use CoinbaseSDK\Operations\ReadMethodTrait;

class Event extends ApiResource implements ResourcePathInterface
{
    use ReadMethodTrait;

    /**
     * @return string
     */
    public static function getResourcePath()
    {
        return 'events';
    }

    public function hasMetadataParam($key)
    {
        return isset($this->data['metadata'][$key]);
    }

    public function getMetadataParam($key)
    {
        return isset($this->data['metadata'][$key]) ? $this->data['metadata'][$key] : null;
    }
}
