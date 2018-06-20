<?php
use PHPUnit\Framework\TestCase;

class CoinbaseTest extends TestCase {

    public function setup() {
        $unit_test_helper = new UnitTestHelper();
        $unit_test_helper->getMockedPaymentModule();
    }

    /**
     * Test that the module is being displayed in the right tab 
     * in the Prestashop backoffice.
     */
    public function testModuleIsAvailableUnderPaymentModulesTab() {
        $this->module = new Coinbase();
        $this->assertEquals($this->module->tab, 'payments_gateways', 
                            "The module should be displayed under the payments_gateways tab.");
    }

}