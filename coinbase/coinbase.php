<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

if (defined('_PS_MODULE_DIR_')) {
    require_once _PS_MODULE_DIR_ . 'coinbase/classes/ConfigManager.php';
}

class Coinbase extends PaymentModule
{

    private $configManager;

    public function __construct()
    {
        $this->name = 'coinbase';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->author = 'Coinbase';
        $this->controllers = array('process', 'cancel', 'webhook');
        $this->is_eu_compatible = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Coinbase Commerce');
        $this->description = $this->l('Payment module to handle transactions using Coinbase Commerce.');

        // Since Prestashop do not use Dependency Injection, make sure that we can change 
        // which class that handle certain behavior, so we can easily mock it in tests.
        $this->setConfigManager(new ConfigManager());
    }

    /**
     * Executes when installing module. Validates that required hooks exists
     * and initiate default values in the database.
     */
    public function install()
    {
        // If anything fails during installation, return false which will 
        // raise an error to the user.
        if (
            !parent::install() ||
            !$this->registerHook('payment') ||
            !$this->registerHook('paymentOptions') ||
            !$this->registerHook('paymentReturn') ||
            !$this->configManager->addFields()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Executes when uninstalling the module. Cleanup DB fields
     * and raise error if something goes wrong.
     */
    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->configManager->deleteFields()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Hook in to the list of payment options on checkout page.
     * @return PaymentOption[]
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $paymentOption = new PaymentOption();
        $paymentOption->setCallToActionText($this->l('Coinbase Commerce'))
            ->setAction($this->context->link->getModuleLink($this->name, 'process', array(), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:coinbase/views/templates/front/payment_infos.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/payment.png'));
        $paymentOptions = [$paymentOption];

        return $paymentOptions;
    }

    public function hookPayment($params)
    {
        if (!$this->active)
            return;

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_coinbase' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayPaymentEU($params)
    {

        $payment_options = array(
            'cta_text' => $this->l('Coinbase Commerce'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/payment.png'),
            'action' => $this->context->link->getModuleLink($this->name, 'process', array(), true)
        );

        return $payment_options;
    }

    public function hookPaymentReturn()
    {
    }

    /**
     * Module Configuration page controller. Handle the form POST request
     * and outputs the form.
     */
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('update_settings_' . $this->name)) {
            Configuration::updateValue('COINBASE_API_KEY', Tools::getValue('COINBASE_API_KEY'));
            Configuration::updateValue('COINBASE_SANDBOX', Tools::getValue('COINBASE_SANDBOX'));
            Configuration::updateValue('COINBASE_SHARED_SECRET', Tools::getValue('COINBASE_SHARED_SECRET'));
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Generates a HTML Form that is used on the module configuration page.
     */
    public function displayForm()
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'description' => $this->l('To use this plugin, you must first sign up for an account and create an API Key at ')
                . '<a href="https://commerce.coinbase.com/" target="_blank" title="Coinbase Commerce">Coinbase Commerce</a>.',
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('API Key'),
                    'desc' => $this->l('You can manage your API keys within the Coinbase Commerce Settings page, available here: ') . '<a href="https://commerce.coinbase.com/dashboard/settings" target="_blank">https://commerce.coinbase.com/dashboard/settings</a>',
                    'name' => 'COINBASE_API_KEY',
                    'size' => 50,
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Shared Secret'),
                    'desc' =>
                        $this->l('Using webhooks allows Coinbase Commerce to send payment confirmation messages to the website. To fill this out:') . '</br>'
                        . $this->l('1. In your Coinbase Commerce settings page, scroll to the \'Webhook subscriptions\' section') . '</br>'
                        . $this->l('2. Click \'Add an endpoint\' and paste the following URL: ')
                        . '<a>' . $this->context->link->getModuleLink($this->name, 'webhook', array(), true) . '</a></br>'
                        . $this->l('3. Make sure to select "Send me all events", to receive all payment updates') . '</br>'
                        . $this->l('4. Click "Show shared secret" and paste into the box above.')
                    ,
                    'name' => 'COINBASE_SHARED_SECRET',
                    'size' => 50,
                    'required' => false
                ],
                [
                    'type' => 'radio',
                    'label' => $this->l('Activate Unsafe Mode'),
                    'desc' => $this->l('Do not validate POST requests to webhooks, this is useful for development and testing (DO NOT USE IN PRODUCTION!)'),
                    'name' => 'COINBASE_SANDBOX',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'enabled',
                            'value' => 1,
                            'label' => $this->l('Enable')
                        ],
                        [
                            'id' => 'disabled',
                            'value' => 0,
                            'label' => $this->l('Disable')
                        ]
                    ]
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        $helper->submit_action = 'update_settings_' . $this->name;

        // Sets current value from DB to the form.
        $helper->fields_value['COINBASE_API_KEY'] = Configuration::get('COINBASE_API_KEY');
        $helper->fields_value['COINBASE_SANDBOX'] = Configuration::get('COINBASE_SANDBOX');
        $helper->fields_value['COINBASE_SHARED_SECRET'] = Configuration::get('COINBASE_SHARED_SECRET');

        return $helper->generateForm($fields_form);
    }

    public function setConfigManager($manager)
    {
        $this->configManager = $manager;
    }
}
