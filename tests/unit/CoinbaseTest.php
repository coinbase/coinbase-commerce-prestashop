<?php
use \Mockery;

class CoinbaseTest extends Mockery\Adapter\Phpunit\MockeryTestCase {

    public function setup() {
        $this->unitTestHelper = new UnitTestHelper();
        $this->paymentMock = $this->unitTestHelper->getMockedPaymentModule();
        $this->configManager = $this->unitTestHelper->getMockedConfigManager();

        $this->module = new Coinbase();
        $this->module->setConfigManager($this->configManager);
    }

    /**
     * Test that the module is being displayed in the right tab 
     * in the Prestashop backoffice.
     */
    public function testModuleIsAvailableUnderPaymentModulesTab() {
        $this->assertEquals($this->module->tab, 'payments_gateways', 
                            "The module should be displayed under the payments_gateways tab.");
    }

    /**
     * Test if the install method return True if all calls within it succeed.
     */
    public function testInstallSuccess() {
        $this->module->method('install')->willReturn(true);
        $this->module->method('registerHook')->will($this->onConsecutiveCalls(true, true));
        $this->configManager->method('addFields')->willReturn(true);

        $this->assertTrue($this->module->install(), "Install should return True if install is successful.");
    }

    /**
     * Test if the install method return False if any of the calls within it fails.
     */
    public function testInstallFailed() {
        $this->module->method('install')->willReturn(true);

        // Second call return False, this should make the install fail.
        $this->module->method('registerHook')->will($this->onConsecutiveCalls(true, false));
        $this->configManager->method('addFields')->willReturn(true);

        $this->assertFalse($this->module->install(), "Install should return False if install fails.");
    }

    /**
     * Test that the Install method return True if all calls within it 
     * is successful.
     */
    public function testUninstallSuccess() {
        $this->module->method('uninstall')->willReturn(true);
        $this->configManager->method('deleteFields')->willReturn(true);


        $this->assertTrue($this->module->uninstall(), "Uninstall should return True if uninstall is successful.");
    }

    /**
     * Test that the Uninstall method return False if any of the calls within it
     * does not succeed.
     */
    public function testUninstallFailed() {
        $this->module->method('uninstall')->willReturn(true);

        // Return false to indiciate something went wrong.
        $this->configManager->method('deleteFields')->willReturn(false);

        $this->assertFalse($this->module->uninstall(), "Uninstall should return False if uninstall fails.");
    }
}