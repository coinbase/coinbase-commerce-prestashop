<?php

if (!defined('_PS_VERSION_')) {
    exit();
}

/**
 * Wrapper to interact with the order/cart data.
 */
class OrderManager {

    public static function getCurrencyIsoById($currencyId) {
        $currency = new Currency($currencyId);
        return $currency->iso_code;
    }

    public static function getOrderConfirmationUrl($context, $cartId, $moduleId, $secureKey=null) {
        $secureKey = $secureKey ?? $context->customer->secure_key;

        return $context->shop->getBaseURL(true) . 'index.php?' . http_build_query([
            'controller' => 'order-confirmation', 
            'id_cart' => $cartId, 
            'id_module' => $moduleId, 
            'key' => $secureKey
        ]);
    }

    public static function getCartById($cartId) {
        return new Cart($cartId);
    }

    public static function getCustomerById($customerId) {
        return new Customer($customerId);
    }

    public static function getCartTotal($cart) {
        return $cart->getOrderTotal(true, Cart::BOTH);
    }
    
}