<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Coinbase extends PaymentModule {

    public function __construct() {
        $this->name = 'coinbase';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Coinbase';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;
        $this->bootstrap = true;

        parent::__construct();
    
        $this->displayName = $this->l('Coinbase Commerce');
        $this->description = $this->l('Payment module to handle transactions using Coinbase Commerce.');
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
            !Configuration::updateValue('COINBASE_API_KEY', null) ||
            !Configuration::updateValue('COINBASE_SANDBOX', null)
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
            !Configuration::deleteByName('COINBASE_API_KEY') || 
            !Configuration::deleteByName('COINBASE_SANDBOX')
        ) {
            return false;
        }

        return true;
    }

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
                    'label' => $this->l('Activate Sandbox Mode'), 
                    'desc' => $this->l('Sandbox mode allows you to test the module without doing real payments.'),
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
}