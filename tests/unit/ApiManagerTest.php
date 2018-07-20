<?php
use \Mockery;

class ApiManagerTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    
    /**
     * Test that the create static method return an instance of the ApiManager
     */
    public function testCreate() {
        Mockery::mock('alias:\Configuration')
            ->shouldReceive('get')->with('COINBASE_API_KEY')->andReturn("key")->once();

        $instance = ApiManager::create();
        $this->assertTrue($instance instanceof ApiManager);
    }

    /**
     * Test that the post method is doing a curl call 
     * to the API to create a charge.
     */
    public function testPost() {
        $data = json_encode(['foo' => 'bar']);
        $key = "my-mocked-key";
        $mock = Mockery::mock('alias:\CurlManager');
        $mock->shouldReceive('init')->with("https://api.commerce.coinbase.com/charges/")->andReturn($mock)->once()
            ->shouldReceive('setopt')->with(CURLOPT_POSTFIELDS, $data)->andReturn($mock)->once()
            ->shouldReceive('setopt')->with(CURLOPT_HTTPHEADER, [
                "Content-Type: application/json", 
                "X-CC-Api-Key: {$key}", 
                "X-CC-Version: 2018-03-22"
            ])->andReturn($mock)->once()
            ->shouldReceive('setopt')->with(CURLOPT_RETURNTRANSFER, true)->andReturn($mock)->once()
            ->shouldReceive('exec')->andReturn("mocked_result")->once()
            ->shouldReceive('close')->once();

        Mockery::mock('alias:\Configuration')
            ->shouldReceive('get')->with('COINBASE_API_KEY')->andReturn($key)->once();

        $res = ApiManager::create()->post(
            "/charges/", 
            $data
        );

        $this->assertEquals($res, "mocked_result");
    }
}