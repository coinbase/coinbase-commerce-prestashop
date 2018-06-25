<?php

if (!defined('_PS_VERSION_')) {
    exit();
}

if (defined('_PS_MODULE_DIR_')) {
    require_once _PS_MODULE_DIR_ . 'coinbase/classes/CurlManager.php';
}

class ApiManager {

    public function __construct() {
        $this->url = "https://api.commerce.coinbase.com";
        $this->apiKey = Configuration::get('COINBASE_API_KEY');
    }

    public static function create() {
        return new self();
    }

    public function post($path, $data, $headers=null) {
        $curl = CurlManager::init($this->url . $path)
            ->setopt(CURLOPT_POSTFIELDS, $data)
            ->setopt(CURLOPT_HTTPHEADER, $headers ?? [
                "Content-Type: application/json", 
                "X-CC-Api-Key: {$this->apiKey}", 
                "X-CC-Version: 2018-03-22"
            ])
            ->setopt(CURLOPT_RETURNTRANSFER, true);
        
        $results = $curl->exec();
        $curl->close();

        return $results;
    }
}