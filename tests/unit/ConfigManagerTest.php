<?php
use \Mockery;

class ConfigManagerTest extends Mockery\Adapter\Phpunit\MockeryTestCase {

    public function setup() {
        $this->module = new ConfigManager();
    }

    /**
     * Test that the addFields method actually adds all configurations.
     */
    public function testAddFields() {
        Mockery::mock('alias:\Configuration')
            ->shouldReceive('updateValue')->with('COINBASE_API_KEY', null)->andReturn(true)->once()
            ->shouldReceive('updateValue')->with('COINBASE_SANDBOX', null)->andReturn(true)->once();
            
        $this->module->addFields();
    }

    /**
     * Test that the deleteFields method actually delete all configurations.
     */
    public function testDeleteFields() {
        Mockery::mock('alias:\Configuration')
            ->shouldReceive('deleteByName')->with('COINBASE_API_KEY')->andReturn(true)->once()
            ->shouldReceive('deleteByName')->with('COINBASE_SANDBOX')->andReturn(true)->once();
            
        $this->module->deleteFields();
    }

}