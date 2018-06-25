<?php

if (!defined('_PS_VERSION_')) {
    exit();
}

class CoinbaseProcessModuleFrontController extends ModuleFrontController {

    public function postProcess() {
        // Check that payment module is active, to prevent users from 
        // calling this controller when payment method is inactive. 
        if(!$this->isModuleActive()) {
            die($this->module->l('This payment method is not available.', 'payment'));
        }

        $response = $this->apiCreateCharge($this->context->cart);
        $response = json_decode($response, true);

        // TODO: Improve error handling here...
        if (array_key_exists('error', $response)) {
            throw new \Exception("Error occured: {$response['type']}");
        }
        elseif (!array_key_exists('data', $response)) {
            throw new \Exception("data key was not in the response.");
        }
        elseif (!array_key_exists('hosted_url', $response['data'])) {
            throw new \Exception("hosted_url key was not in the response.");
        }   

        header('Location: ' . $response['data']['hosted_url']);
    }
    
    /**
     * Check if the current module is an active payment module.
     */
    public function isModuleActive() {
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'coinbase') {
                $authorized = true;
                break;
            }
        }

        return $authorized;
    }

    /**
     * HTTP POST Call to API that create a charge.
     * @return string Body of HTTP Response
     */
    public function apiCreateCharge($cart) {
        $shopName = Configuration::get('PS_SHOP_NAME');

        $data = json_encode([
            'name' => $shopName, 
            'description' => "Payment for order from {$shopName}", 
            'pricing_type' => "fixed_price", 
            'local_price' => [
                'amount' => OrderManager::getCartTotal($cart), 
                'currency' => OrderManager::getCurrencyIsoById($cart->id_currency)
            ], 
            'metadata' => [
                'cart_id' => (int)$cart->id,
            ], 
            'redirect_url' => OrderManager::getOrderConfirmationUrl($this->context, $cart->id, $this->module->id)
        ]);

        $results = ApiManager::create()->post('/charges/', $data);
        return $results;
    }

}