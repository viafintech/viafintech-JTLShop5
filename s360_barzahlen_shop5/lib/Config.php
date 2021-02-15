<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib;

use JTL\Plugin\Helper as PluginHelper;


class Config {
    
    const PluginID = "s360_barzahlen_shop5";
    
    const SLIP_TYPE_PAYMENT = "payment";
    const SLIP_TYPE_REFUND = "refund";
    
    const SLIP_STATE_PENDING = "pending";
    const SLIP_STATE_PAID = "paid";
    const SLIP_STATE_EXPIRED = "expired";
    const SLIP_STATE_INVALIDATED = "invalidated";
    
    const COUNTRY_LIMIT = [
        'DE' => 1000.00,
        'AT' => 1000.00,
        'IT' => 1000.00,
        'GR' => 500.00,
        'CH' => 1500.01,
        'ES' => 1000.00,
        'FR' => 1000.00
    ];
    
    const COUNTRY_CURRENCY = [
        'DE' => 'EUR',
        'AT' => 'EUR',
        'IT' => 'EUR',
        'GR' => 'EUR',
        'CH' => 'CHF',
        'ES' => 'EUR',
        'FR' => 'EUR'
    ];

    const SUPPORTED_LOCALES = [
        'de-DE', 'de-CH', 'el-GR', 'en-CH', 'es-ES', 'fr-FR', 'it-IT'
    ];

    private static $instance;
   
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public $paymethod;
    public $plugin;
    private $api_config;
    private $translation;
    

    public function __construct() {
        $this->plugin = PluginHelper::getPluginById(self::PluginID);
        $this->paymethod = $this->plugin->getPaymentMethods()->getMethods()[0]; //only one method defined
        $this->translation = $this->plugin->getLocalization()->getTranslations();

        $raw_config = $this->plugin->getConfig()->getOptions()->all();
        if (!empty($raw_config)) {
            $api_config = [];
            foreach ($raw_config as $config) {
                if (strpos($config->valueID, "_set_")) {
                    $_ = explode("_", $config->valueID);
                    if (sizeof($_) === 6) {
                        if (!isset($api_config[strtoupper($_[4])])) {
                            $api_config[strtoupper($_[4])] = (object)[];
                        }
                        $api_config[strtoupper($_[4])]->{$_[5]} = $config->value;
                    }
                    (sizeof($_) === 5) ? $this->{$_[4]} = $config->value : null;
                }
            }
            foreach ($api_config as $key => $conf) { // filter configured api settings
                if ( (int)$conf->sandbox>=0 && !empty($conf->divisionId) && !empty($conf->APIKey) ) {
                    $this->api_config[$key] = $conf;
                }
            }
        }
    }    

    public function isConfiguredFor($country) {
        return Helper::isset_noempty($this->api_config[$country]);
    }
    
    public function getApiConfig() {
        return $this->api_config;
    }

    public function translate($key) {
        return $this->translation[$key];
    }
    

}