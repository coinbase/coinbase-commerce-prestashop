<?php
use \Mockery;

class MockCart {
    public $id_currency;
    public $id;

    public function __construct() {
        $this->id = 1;
        $this->id_currency = 1;
    }
}

class ProcessTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    
    public function setup() {
        $this->unitTestHelper = new UnitTestHelper();
        $this->unitTestHelper->getMockedModuleFrontController();
        
        $this->process = new CoinbaseProcessModuleFrontController();
        $this->process->context = $this->getMockedContext();
        $this->process->module = new stdClass();
        $this->process->module->id = 1;

    }

    /**
     * Test that isModuleActive return True when coinbase
     * is part of our installed/active payment modules.
     */
    public function testIsModuleActiveWithCoinbase() {
        Mockery::mock('alias:\Module')
            ->shouldReceive('getPaymentModules')->andReturn([
                ['name' => 'coinbase'], 
                ['name' => 'otherpayment'], 
            ]);

        $active = $this->process->isModuleActive();

        $this->assertEquals($active, true, "Should be true when coinbase is returned by getPaymentModules.");
    }

    /**
     * Test that isModuleActive return false when coinbase is 
     * NOT part of the installed/active payment modules.
     */
    public function testIsModuleActiveWithoutCoinbase() {
        Mockery::mock('alias:\Module')
            ->shouldReceive('getPaymentModules')->andReturn([
                ['name' => 'somepayment'], 
                ['name' => 'otherpayment'], 
            ]);

        $active = $this->process->isModuleActive();

        $this->assertEquals($active, false, "Should be false when coinbase is not returned by getPaymentModules.");
    }

    /**
     * Test that the apiCreateCharge method makes an API call 
     * to the correct path and including all mandatory fields.
     */
    public function testApiCreateCharge() {
        Mockery::mock('alias:\Configuration')->shouldReceive('get')->andReturn('shopName');
        
        Mockery::mock('alias:\OrderManager')
            ->shouldReceive('getCurrencyIsoById')->andReturn('USD')->once()
            ->shouldReceive('getCartTotal')->andReturn(10)->once()
            ->shouldReceive('getOrderConfirmationUrl')->andReturn('/order-confirm')->once();

        Mockery::mock('overload:\ApiManager')
            ->shouldReceive('create')->andReturnSelf()
            ->shouldReceive('post')->with(
                '/charges/', 
                json_encode([
                    'name' => 'shopName', 
                    'description' => "Payment for order from shopName", 
                    'pricing_type' => "fixed_price", 
                    'local_price' => [
                        'amount' => 10, 
                        'currency' => 'USD'
                    ], 
                    'metadata' => [
                        'cart_id' => (int)1,
                    ], 
                    'redirect_url' => '/order-confirm'
                ])
            )->andReturn('success');

        $cart = Mockery::mock();
        $cart->id = 1;
        $cart->id_currency = 1;

        $result = $this->process->apiCreateCharge($cart);
        $this->assertEquals($result, 'success');
    }

    private function getMockedContext() {
        $context = $this->getMockBuilder(get_class(new stdClass()))->getMock();
        
        $cart = $this->getMockBuilder(get_class(new stdClass()))->getMock();
        $cart->id = 'cartId';

        $customer = $this->getMockBuilder(get_class(new stdClass()))->getMock();
        $customer->secure_key = 'secure_key';
        
        $shop = $this->getMockBuilder(get_class(new stdClass()))
            ->setMethods([
                'getBaseURL',
            ])
            ->getMock();

        $context->cart = $cart;
        $context->customer = $customer;
        $context->shop = $shop;
        
        return $context;
    }

}