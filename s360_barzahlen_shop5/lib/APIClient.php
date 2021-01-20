<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib;

use JTL\Shop;
use Plugin\s360_barzahlen_shop5\lib\Logger;
use Plugin\s360_barzahlen_shop5\lib\Config;
use Plugin\s360_barzahlen_shop5\lib\Barzahlen\Client;
use Plugin\s360_barzahlen_shop5\lib\Barzahlen\Request\CreateRequest;
use Plugin\s360_barzahlen_shop5\lib\Barzahlen\Request\ResendRequest;
use Plugin\s360_barzahlen_shop5\lib\Barzahlen\Request\InvalidateRequest;


class APIClient {

    public $Client;
    
    public function __construct($cLand) {
        
        if (empty($cLand)) {
            throw new \Exception("No country given for APIClient()");
        }
            
        $conf = Config::getInstance();
        $userAgent = "s360 " . $conf->plugin->getMeta()->getName() . " Plugin v" . $conf->plugin->getMeta()->getVersion() . " jtl" . substr(Shop::getApplicationVersion(), 0, 1);    

        $config = $conf->getApiConfig()[$cLand];
        
        if ($config->sandbox) {
            $_SESSION["Barzahlen"]->api->sandbox = $config->sandbox;
        }
        
        $client = new Client($config->divisionId, $config->APIKey, $config->sandbox);
        $client->setUserAgent($userAgent);        
        $this->Client = $client;
        
    }
    
    public function handle($request) {
        $response = $this->Client->handle($request);
        Logger::api_message(
                (new \ReflectionClass($request))->getShortName(),
                $request->getBody(),
                $response
                );
        return $response;
        
    }

    public function CreateRequest() {
        return new CreateRequest();
    }
    
    public function ResendRequest($id) {
        return new ResendRequest($id, "email");
    }
    
    public function InvalidateRequest($id) {
        return new InvalidateRequest($id);
    }
    
}
