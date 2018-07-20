<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (defined('_PS_MODULE_DIR_')) {
    require_once _PS_MODULE_DIR_ . 'coinbase/classes/ConfigManager.php';
}

class Coinbase extends PaymentModule {

    private $configManager;

    public function __construct() {
        $this->name = 'coinbase';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Coinbase';
        $this->controllers = array('process');
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
    public function install() {
        // If anything fails during installation, return false which will 
        // raise an error to the user.
        if (
            !parent::install() || 
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
    public function uninstall() {
        if (
            !parent::uninstall() || 
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
    public function hookPaymentOptions($params) {
        if (!$this->active) {
            return;
        }

        $paymentOption = new PaymentOption();
        $paymentOption->setCallToActionText($this->l('Coinbase Commerce'))
            ->setAction($this->context->link->getModuleLink($this->name, 'process', array(), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:coinbase/views/templates/front/payment_infos.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.png'));
        $paymentOptions = [$paymentOption];

        return $paymentOptions;
    }

    public function hookPaymentReturn() {}

    /**
     * Module Configuration page controller. Handle the form POST request 
     * and outputs the form. 
     */
    public function getContent() {
        $output = null;
 
        if (Tools::isSubmit('update_settings_' . $this->name))
        {
            Configuration::updateValue('COINBASE_API_KEY', Tools::getValue('COINBASE_API_KEY'));
            Configuration::updateValue('COINBASE_SANDBOX', Tools::getValue('COINBASE_SANDBOX'));
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output.$this->displayForm();
    }

    /** 
     * Generates a HTML Form that is used on the module configuration page.
     */
    public function displayForm() {
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
                    'name' => 'COINBASE_API_KEY',
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
    
        return $helper->generateForm($fields_form);
    }

    public function setConfigManager($manager) {
        $this->configManager = $manager;
    }
}