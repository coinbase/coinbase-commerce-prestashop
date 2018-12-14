# Coinbase Commerce Prestashop Payment Module

# Installation
1. Signup for an account at [Coinbase Commerce](https://commerce.coinbase.com/).
2. Create an API Key by going to the Settings tab in the Coinbase Commerce dashboard.
3. Copy the `coinbase/` folder to your Prestashop `modules/` folder.
4. Login to your Prestashop Back Office, navigate to the Modules tab, go to the "Installed Modules" tab and search for "Coinbase Commerce". Click Install to activate the plugin.
5. Click Configure to go to the settings page of the plugin. Set the API Key, Shared Secret Key from Coinbase Commerce Dashboard.
6. Copy webhook url from settings page of the plugin to Coinbase Commerce DashBoard Settings. 

**NOTE:** There is a setting for "Unsafe" mode on the plugins settings page. This should never be set to "Enabled" on a production website. 
It is only used for making testing easier during development, since it will deactivate any validation of the requests that is send to the webhook, which 
will allow the developer to emulate POST requests to the webhook without generating the `X-CC-Webhook-Signature` header.

# Localization
All text strings, labels and descriptions found in the plugin is translatable. You can translate the plugin from the 
International/Translations tab in the Prestashop Back Office.

# Integrate with other e-commerce platforms

[Coinbase Commerce Integrations](https://commerce.coinbase.com/integrate)