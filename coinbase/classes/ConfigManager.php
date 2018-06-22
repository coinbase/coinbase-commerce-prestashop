<?php

if (!defined('_PS_VERSION_')) {
    exit();
}

class ConfigManager {

    public function addFields() {
        if(
            !Configuration::updateValue('COINBASE_API_KEY', null) ||
            !Configuration::updateValue('COINBASE_SANDBOX', null)
        ) {
            return false;
        }

        return true;
    }

    public function deleteFields() {
        if(
            !Configuration::deleteByName('COINBASE_API_KEY') ||
            !Configuration::deleteByName('COINBASE_SANDBOX')
        ) {
            return false;
        }

        return true;
    }

}
