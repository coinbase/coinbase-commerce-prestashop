<?php
class CoinbaseProcessModuleFrontController extends ModuleFrontController {
    
    const API_URL = "https://api.commerce.coinbase.com";

    public function postProcess() {
        $order = $this->createOrder();
        $response = $this->createChargePOST($order);
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

    protected function createOrder() {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        
        $this->module->validateOrder(
            $cart->id, 
            Configuration::get('PS_OS_BANKWIRE'), // id_order_state, the status of the order.
            $total, // Amount to be paid(?)
            $this->module->displayName, // Payment Method
            null, // Message
            [], // Extra vars
            (int)$currency->id, // Currency ID
            false, // Dont touch amount 
            $customer->secure_key // If we should use a secure key or not
        );

        return new Order($this->module->currentOrder);
    }

    /**
     * HTTP POST Call to API that create a charge.
     * @return string Body of HTTP Response
     */
    protected function createChargePOST($order) {
        $apiKey = Configuration::get('COINBASE_API_KEY');
        $currency = new Currency($order->id_currency);

        $ch = curl_init(self::API_URL . "/charges/");
        $data = json_encode([
            'name' => Configuration::get('PS_SHOP_NAME'), 
            'description' => "Payment for order #{$order->id}", 
            'pricing_type' => "fixed_price", 
            'local_price' => [
                'amount' => $order->total_paid_tax_incl, 
                'currency' => $currency->iso_code
            ], 
            'metadata' => [
                'customer_id' => (int)$order->id_customer,
                'order_id' => (int)$order->id,
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