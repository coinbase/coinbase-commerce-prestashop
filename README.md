# Coinbase Commerce Prestashop Payment Module

# Installation

## Setup your Coinbase Account
1. Signup for an account at [Coinbase Commerce](https://commerce.coinbase.com/).
2. Create an API Key by going to the Settings tab in the Coinbase Commerce dashboard.
3. Add an Webhook Endpoint which points to https://<YOUR SITE>/module/coinbase/webhook

## Setup the Plugin
4. Copy the `coinbase/` folder to your Prestashop `modules/` folder.
5. Login to your Prestashop Back Office, navigate to the Modules tab, go to the "Installed Modules" tab and search for "Coinbase Commerce". Click Install to activate the plugin.
6. Click Configure to go to the settings page of the plugin. Set the API Key to the key that you created in step 2.

**NOTE:** There is a setting for "Unsafe" mode on the plugins settings page. This should never be set to "Enabled" on a production website. 
It is only used for making testing easier during development, since it will deactivate any validation of the requests that is send to the webhook, which 
will allow the developer to emulate POST requests to the webhook without generating the `X-CC-Webhook-Signature` header.

# Run Tests

This Prestashop plugin is using the composer dependencies Mockery and PHPUnit to do unit testing. Because of the limitations of Mockery all unit tests need to be run as isolated processes. 

You run the tests with `vendor/phpunit/phpunit/phpunit --process-isolation`.