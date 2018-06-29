<?php
use \Mockery;

class WebhookTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    
    public function setup() {
        $this->unitTestHelper = new UnitTestHelper();
        $this->unitTestHelper->getMockedModuleFrontController();

        $this->webhook = new CoinbaseWebhookModuleFrontController();
    }

    /**
     * Test that the method calls validateOrder
     * with the correct arguments.
     */
    public function testCreateOrder() {
        Mockery::mock('alias:\Configuration')
            ->shouldReceive('get')->with('PS_OS_PAYMENT')->andReturn(1);

        $currency = Mockery::mock('Currency');
        $currency->id = 1;

        $cartMock = Mockery::mock();
        $cartMock->id = 1;
        $cartMock->id_customer = 1;

        $customerMock = Mockery::mock();
        $customerMock->secure_key = "secure-key";

        Mockery::mock('alias:\OrderManager')
            ->shouldReceive('getCartById')->with(1)->andReturn($cartMock)
            ->shouldReceive('getCustomerById')->with(1)->andReturn($customerMock);
        
        $paymentCollectionMock = Mockery::mock('PaymentCollection');
        $paymentCollectionMock->shouldReceive('count')->andReturn(0)->once();

        Mockery::mock('overload:\Order')
            ->shouldReceive('getOrderPaymentCollection')->andReturn($paymentCollectionMock);
    
        $mockModule = Mockery::mock();
        $mockModule->displayName = 'Coinbase';
        $mockModule->currentOrder = 1;
        $mockModule->shouldReceive('validateOrder')->with(
            1, 
            1, 
            50, 
            'Coinbase', 
            null, 
            [], 
            1, 
            false, 
            "secure-key"
        )->once();
        $this->webhook->module = $mockModule;

        UnitTestHelper::callProtected($this->webhook, 'createOrder', [1, 50, $currency, "trans-id"]);
    }

    /**
     * Test that valid payload does not return any error messages.
     */
    public function testValidateRequestSuccess() {
        $payload = json_decode("{\"event\": []}", true);
        Mockery::mock('alias:\Configuration')
            ->shouldReceive('get')->with('COINBASE_SANDBOX')->andReturn(true)->once()
            ->shouldReceive('get')->with('COINBASE_API_KEY')->andReturn(true)->once();
        
        $res = UnitTestHelper::callProtected($this->webhook, 'validateRequest', [$payload]);
        $this->assertNull($res);
    }

    /**
     * Test that payload without event key return error.
     */
    public function testValidateRequestEventMissing() {
        $payload = json_decode("{\"foo\": []}", true);
        Mockery::mock('alias:\Configuration')
            ->shouldReceive('get')->with('COINBASE_SANDBOX')->andReturn(true)->once()
            ->shouldReceive('get')->with('COINBASE_API_KEY')->andReturn(true)->once();
        
        $res = UnitTestHelper::callProtected($this->webhook, 'validateRequest', [$payload]);
        $this->assertEquals($res, "Request needs to contain 'event' value.");
    }
}