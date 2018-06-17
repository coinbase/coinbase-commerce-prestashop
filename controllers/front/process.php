<?php
class CoinbaseProcessModuleFrontController extends ModuleFrontController {
    
    const API_URL = "https://api.commerce.coinbase.com";

    public function postProcess() {
        $response = $this->createChargePOST();
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
     * HTTP POST Call to API that create a charge.
     * @return string Body of HTTP Response
     */
    protected function createChargePOST() {
        $apiKey = Configuration::get('COINBASE_API_KEY');

        $ch = curl_init(self::API_URL . "/charges/");
        $data = json_encode([
            'name' => "The Human Fund", 
            'description' => "Money For People", 
            'pricing_type' => "fixed_price", 
            'local_price' => [
                'amount' => 100, 
                'currency' => 'USD'
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