<?php

if (!defined('_PS_VERSION_')) {
    exit();
}

/**
 * With PHPUnit and Mockery we can't mock the curl methods.
 * Therefor we create a wrapper that makes it possible to test 
 * if the curl methods are being called, without calling them.
 */
class CurlManager {

    public function __construct($handler) {
        $this->handler = $handler;
    }

    public static function init($url) {
        $handler = curl_init($url);
        return new self($handler);
    }

    public function setopt($option, $data) {
        curl_setopt($this->handler, $option, $data);
        return $this;
    }

    public function exec() {
        $result = curl_exec($this->handler);
        return $result;
    }

    public function close() {
        curl_close($this->handler);
    }

}