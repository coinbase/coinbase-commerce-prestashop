<?php

if (!defined('_PS_VERSION_')) {
    exit();
}

if (defined('_PS_MODULE_DIR_')) {
    require_once _PS_MODULE_DIR_ . 'coinbase/classes/OrderManager.php';
}

class CoinbaseWebhookModuleFrontController extends ModuleFrontController {

    public function postProcess() {
        $payload = json_decode(file_get_contents('php://input'), true);

        // Throws error if request is invalid.
        $error = $this->validateRequest($payload);
        if($error) {
            die($error);
        }

        if($payload['event']['type'] == 'charge:confirmed') {
            $this->handleChargeConfirmed($payload);
        }
        elseif($payload['event']['type'] == 'charge:failed') {
            $this->handleChargeFailed($payload);
        }

        die("OK");
    }

    /**
     * Handler for when event is sent with type charge:confirmed.
     * It takes the payload data and creates an order.
     */
    protected function handleChargeConfirmed($payload) {
        $cartId = $payload['event']['data']['metadata']['cart_id'];
        $payments = $payload['event']['data']['payments'];
        if (!empty($payments)) {
            // Get the last payment in list of payments. 
            // That's the only one we care about adding.
            $lastPayment = $payments[count($payments)-1];

            $amount = $lastPayment['value']['local']['amount'];
            $transactionId = $lastPayment['transaction_id'];
            
            // Get the currency object by the ISO code provided in the data.
            // If the currency does not exist with that ISO code, throw error.
            $currencyId = Currency::getIdByIsoCode($lastPayment['value']['local']['currency']);
            if(!isset($currencyId)) {
                throw new \Exception("The currency ISO code provided in the request does not exist in the shop.");
            }
            $currency = new Currency($currencyId);
            $this->createOrder($cartId, $amount, $currency, $transactionId, Configuration::get('PS_OS_PAYMENT'));
        }
    }
    
    /**
     * Handler for when event is sent with type charge:failed
     * If payment was made, and failed, it creates an order with 
     * ERROR status.
     */
    protected function handleChargeFailed($payload) {
        $cartId = $payload['event']['data']['metadata']['cart_id'];
        $payments = $payload['event']['data']['payments'];

        // If no payments is made, it means that the charge expired.
        // in this case we can ignore handling it. 
        // Else it means that a payment was made, but it still failed due 
        // to for example invalid amount.
        if(empty($payments)) {
            return;
        }

        // Get the last payment in list of payments. 
        // That's the only one we care about adding.
        $lastPayment = $payments[count($payments)-1];

        $amount = $lastPayment['value']['local']['amount'];
        $transactionId = $lastPayment['transaction_id'];
        
        // Get the currency object by the ISO code provided in the data.
        // If the currency does not exist with that ISO code, throw error.
        $currencyId = Currency::getIdByIsoCode($lastPayment['value']['local']['currency']);
        if(!isset($currencyId)) {
            throw new \Exception("The currency ISO code provided in the request does not exist in the shop.");
        }
        $currency = new Currency($currencyId);

        $this->createOrder($cartId, $amount, $currency, $transactionId);
    }

    /**
     * Create an order from a cart and a payment transaction.
     * 
     * @param int $cartId The ID of the cart that we create the order from.
     * @param float $amount The amount paid.
     * @param Currency $currency The currency that was used in the payment.
     * @param string $transactionId The ID of the transaction from the payment.
     * @param int $statusId The status that the order should be set to.
     * @return Order The created order.
     */
    protected function createOrder($cartId, $amount, $currency, $transactionId, $statusId=null) {
        $cart = OrderManager::getCartById($cartId);
        $customer = OrderManager::getCustomerById($cart->id_customer);
        $statusId = $statusId ?? Configuration::get('PS_OS_PAYMENT');
        
        $this->module->validateOrder(
            $cart->id,
            $statusId,
            $amount,
            $this->module->displayName,
            null, // Message
            [], // Extra vars
            (int)$currency->id,
            false, // Dont touch amount
            $customer->secure_key
        );

        $order = new Order($this->module->currentOrder);
        
        // Update the Transaction ID of the payment that was created 
        // when we validated the order. 
        $payments = $order->getOrderPaymentCollection();
        if($payments->count() > 0) {
            $payments[0]->transaction_id = $transactionId;
            $payments[0]->update();
        }

        return $order;
    }

    /**
     * Check the header of the request for a signature that should match 
     * a signature generated on the server.
     * @param array $body The request body.
     * @return string|null Error message
     */
    protected function validateRequest($payload) {
        // We calculate the HMAC Signature using our API Key. 
        // The requests header must match this to be a valid request.
        $hash = hash_hmac("sha256", file_get_contents('php://input'), Configuration::get('COINBASE_API_KEY'));

        // Only validate hash if we are not in test mode.
        $testMode = (bool)Configuration::get('COINBASE_SANDBOX');

        if(!array_key_exists('HTTP_X_CC_WEBHOOK_SIGNATURE', $_SERVER) && !$testMode) {
            return "Webhook signature not included in the headers of the request.";
        }
        elseif (array_key_exists('HTTP_X_CC_WEBHOOK_SIGNATURE', $_SERVER) && $hash !== $_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] && !$testMode) {
            return "The webhook signature of the request does not match the one generated by the server.";
        }
        elseif(!array_key_exists('event', $payload)) {
            return "Request needs to contain 'event' value.";
        }

        return null;
    }
                
}