<?php
ini_set('error_reporting', E_ERROR | E_PARSE); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', 'TEST_VERSION');
}

use PHPUnit\Framework\TestCase;

class UnitTestHelper extends TestCase {

    /**
     * Mock the PaymentModule from Prestashop core.
     */
    public function getMockedPaymentModule() {
        $paymentModule = $this->getMockBuilder(get_class(new stdClass()))
            ->setMockClassName('PaymentModule')
            ->setMethods(
                array(
                    '__construct',
                    'display',
                    'displayConfirmation',
                    'install',
                    'l',
                    'registerHook',
                    'uninstall',
                    'unregisterHook',
                )
            )
            ->getMock();
        return $paymentModule;
    }

    public function getMockedModuleFrontController() {
        $frontController = $this->getMockBuilder(get_class(new stdClass()))
            ->setMockClassName('ModuleFrontController')
            ->getMock();
        return $frontController;
    }

    public function getMockedConfigManager() {
        $configManager = $this->getMockBuilder(get_class(new ConfigManager()))
            ->setMethods(array(
                'addFields', 
                'deleteFields'
            ))
            ->getMock();
        return $configManager;
    }
}