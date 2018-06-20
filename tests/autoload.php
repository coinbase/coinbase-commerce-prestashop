<?php
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
        $payment_module = $this->getMockBuilder(get_class(new stdClass()))
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
        return $payment_module;
    }
}