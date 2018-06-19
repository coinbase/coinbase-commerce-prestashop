<?php
class CoinbaseProcessModuleFrontController extends ModuleFrontController {
    
    const API_URL = "https://api.commerce.coinbase.com";

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
    protected function isModuleActive() {
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
    protected function apiCreateCharge($cart) {
        $apiKey = Configuration::get('COINBASE_API_KEY');
        $currency = new Currency($cart->id_currency);
        $shopName = Configuration::get('PS_SHOP_NAME');

        $ch = curl_init(self::API_URL . "/charges/");
        $data = json_encode([
            'name' => $shopName, 
            'description' => "Payment for order from {$shopName}", 
            'pricing_type' => "fixed_price", 
            'local_price' => [
                'amount' => $cart->getOrderTotal(true, Cart::BOTH), 
                'currency' => $currency->iso_code
            ], 
            'metadata' => [
                'cart_id' => (int)$cart->id,
            ]
        ]);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json", 
            "X-CC-Api-Key: {$apiKey}", 
            "X-CC-Version: 2018-03-22"
        ]);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $results = curl_exec($ch);
        curl_close($ch);

        return $results;
    }

}