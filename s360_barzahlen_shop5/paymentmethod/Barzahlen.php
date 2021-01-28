<?php

use JTL\Helpers\Text;
use JTL\Shop;
use Plugin\s360_barzahlen_shop5\lib\Config;
use Plugin\s360_barzahlen_shop5\lib\Helper;
use Plugin\s360_barzahlen_shop5\lib\Logger;
use Plugin\s360_barzahlen_shop5\lib\APIClient;
use Plugin\s360_barzahlen_shop5\lib\Database;
use JTL\Plugin\Payment\Method;


class Barzahlen extends Method 
{
    private $config;
    private $cLand;
    private $cWaehrungName;
    private $APIClient;
    

    public function init($nAgainCheckout = 0)
    {   
        parent::init($nAgainCheckout);
        $this->name = 'Barzahlen';
        $this->caption = 'Barzahlen';
        $this->config = Config::getInstance();
        
        (Helper::isset_noempty($_SESSION["Kunde"]->cLand))
            ? $this->cLand = $_SESSION["Kunde"]->cLand
            : $this->cLand = null;
        
        (is_object($_SESSION["Waehrung"]))
            ? $this->cWaehrungName = $_SESSION["Waehrung"]->getCode()
            : $this->cWaehrungName = null;

        $this->cLand
            ? $this->APIClient = new APIClient($this->cLand)
            : $this->APIClient = null;

        return $this;
    }
    
    public function preparePaymentProcess($order):void
    {      
        if (!$this->hasAllowedCurrency()) {
            $_SESSION["Barzahlen"]->message->text = $this->config->translate("lang_local_currency_only");
            $_SESSION["Barzahlen"]->message->type = "error";
            $this->redirectOnError();
        }

        $request = $this->APIClient->CreateRequest();
        $request->setSlipType(Config::SLIP_TYPE_PAYMENT);
        if (Helper::isset_noempty($this->config->expireIn)) {
            $expiresAt = new DateTime(date('Y-m-d', time()));
            $expiresAt->modify("+".$this->config->expireIn." day");
            $request->setExpiresAt($expiresAt);
        }
        $request->setCustomerKey($order->oRechnungsadresse->cMail);
        $request->setCustomerEmail($order->oRechnungsadresse->cMail);

        $targetLocale = strtolower(Text::convertISO2ISO639(Shop::getLanguage(true))) . '-' . strtoupper(isset($order->oRechnungsadresse->cLand) ? $order->oRechnungsadresse->cLand : '');
        // we set the customer language to null if the locale is not supported.
        if(!in_array($targetLocale, Config::SUPPORTED_LOCALES)) {
            $request->setCustomerLanguage(null);
        } else {
            $request->setCustomerLanguage($targetLocale);
        }

        if (Helper::isset_noempty($this->config->sendCustomerAddress)) {
            if ($order->oRechnungsadresse->cStrasse && $order->oRechnungsadresse->cHausnummer) {
                $address["street_and_no"] = html_entity_decode($order->oRechnungsadresse->cStrasse . " " . $order->oRechnungsadresse->cHausnummer, ENT_COMPAT, 'UTF-8');
            }
            $address["zipcode"] = $order->oRechnungsadresse->cPLZ;
            $address["city"] = html_entity_decode($order->oRechnungsadresse->cOrt, ENT_COMPAT, 'UTF-8');
            $address["country"] = $order->oRechnungsadresse->cLand;
            $request->setAddress($address);
        }        
        $request->setHookUrl(Database::getInstance()->getWebhookUrl());
        $request->setTransaction(number_format($order->fGesamtsumme, 2), $this->cWaehrungName);

        try {
            $response = $this->APIClient->handle($request);
            $slip = json_decode($response);

            $orderFinalized = finalisiereBestellung(); 
            $orderFinalized->Lieferadresse->cLand  = $order->Lieferadresse->cLand; //finalized order don't contains shipping

            $obj = Database::getInstance()->prepareInsert($orderFinalized, $slip);
            Database::getInstance()->insertSlip($obj);
            
            $_SESSION["Barzahlen"]->checkout_token = $slip->checkout_token;
            
            // pretend we were redirected to notify (this leads to the confirmation page being shown)
            $tBestellId = Shop::Container()->getDB()->select('tbestellid', 'kBestellung', (int)$orderFinalized->kBestellung);
            if (Helper::isset_noempty($tBestellId)) {
                header('Location: ' . Shop::getURL() . '/bestellabschluss.php?i=' . $tBestellId->cId);
                exit();
            }

        } catch (\Exception $ex) {
            Logger::debug($ex->getMessage());
            $_SESSION["Barzahlen"]->has_error = true;
            $_SESSION["Barzahlen"]->message->text = $this->config->translate("lang_limit_exeeded");
            $_SESSION["Barzahlen"]->message->type = "error";
            $this->redirectOnError();
        }
        
    }

    public function redirectOnError() {
        header('Location: ' . Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1');
        exit();
    }
   
    
    public function isValidIntern($args_arr = []):bool {
        //post order is not supported, disable payment method
        if (!$this->config->paymethod->duringOrder) {
            Logger::debug("Postorder payment is not supported, please enable preorder in payment method.");
            return false;
        }
        if ($this->inConfiguredCountries()) {
            return true;
        }
        return false;
    }
   
    
    public function isValid($customer, $cart):bool {
        if (Helper::isset_noempty($_SESSION["Barzahlen"]->has_error) && $_SESSION["Barzahlen"]->has_error === true) {
            return false;
        }
        return parent::isValid($customer, $cart);
    }
    
    
    public function isSelectable():bool {
        if ($this->inSupportedCountries() &&
            $this->inConfiguredCountries() && 
            $this->hasAllowedCurrency() && 
            $this->hasAllowedLimit() 
            ) {
            return true;
        }
        return false;
    }
    
    
    public function inSupportedCountries() {
        return isset(Config::COUNTRY_LIMIT[$this->cLand]);
    }

    
    public function inConfiguredCountries() {
        return $this->config->isConfiguredFor($this->cLand);
    }
    
    
    public function hasAllowedCurrency() {
        return (Config::COUNTRY_CURRENCY[$this->cLand]===$this->cWaehrungName);
    }
    
    
    public function hasAllowedLimit() {
        if (Helper::isset_noempty($_SESSION['Warenkorb'])) {
            $OffeneSumme = $this->getDailyAmount();
            $WarenSumme = $_SESSION['Warenkorb']->gibGesamtsummeWaren(1); //inkl. Versandkosten und Gutscheine
            $fAufpreis = $this->getAdditionalPaymentCost();
            $GesamtSumme = $OffeneSumme + $WarenSumme + $fAufpreis;
            if ($GesamtSumme < Config::COUNTRY_LIMIT[$this->cLand]) {
                return true;
            }
        }
        return false;
    }
    
    
    private function getAdditionalPaymentCost() {
        $fAufpreis = 0.00;
        if (Helper::isset_noempty($_SESSION['AktiveVersandart'])) {
            $tVersandartZahlungsart = Shop::Container()->getDB()->select('tversandartzahlungsart', 'kVersandart', (int)$_SESSION['AktiveVersandart'], 'kZahlungsart', $this->config->tZahlungsart->kZahlungsart);
            if (Helper::isset_noempty($tVersandartZahlungsart)) {
                $fAufpreis = $tVersandartZahlungsart->fAufpreis;
            }
        }
        return $fAufpreis;
    }
    
    
    private function getDailyAmount() {
        $amount = 0.00;
        if (Helper::isset_noempty($_SESSION["Kunde"]->cMail)) {
            $amount = Database::getInstance()->getDailySlipsAmount($_SESSION["Kunde"]->cMail);
        }
        return $amount->sum;
    }


   
    public function cancelOrder($kBestellung, $bDelete = false) {
        
        $payment_slip = Database::getInstance()->selectSlipBykBestellung($kBestellung);

        if(!Helper::isset_noempty($payment_slip)){
            Logger::debug("Slip not found for kBestellung ".$kBestellung."!");
            return parent::cancelOrder($kBestellung, $bDelete);
        }

        $this->APIClient = new APIClient($payment_slip->cRechnungsLand);      
        
        if ($payment_slip->transaction_state===Config::SLIP_STATE_PENDING) {

            $request = $this->APIClient->InvalidateRequest($payment_slip->id);
            
            try {
                $response = $this->APIClient->handle($request);
                $invalid_slip = json_decode($response);
                $obj = Database::getInstance()->prepareUpdate($invalid_slip);
                Database::getInstance()->updateSlip($obj);

            } catch (\Exception $ex) {
                Logger::debug($ex->getMessage());
            }

        } else if ($payment_slip->transaction_state===Config::SLIP_STATE_PAID) {
            
            $request = $this->APIClient->CreateRequest();
            $request->setSlipType(Config::SLIP_TYPE_REFUND);
            $request->setForSlipId($payment_slip->id);
            $request->setHookUrl(Database::getInstance()->getWebhookUrl());
            $request->setTransaction("-".$payment_slip->transaction_amount, $payment_slip->transaction_currency);
            
            try {
                $response = $this->APIClient->handle($request);
                $refund_slip = json_decode($response);
                $obj = Database::getInstance()->prepareInsert($payment_slip, $refund_slip);
                Database::getInstance()->insertSlip($obj);
                
            } catch (\Exception $ex) {
                Logger::debug($ex->getMessage());
            }
            
        } else if ($payment_slip->transaction_state===Config::SLIP_STATE_EXPIRED || $payment_slip->transaction_state===Config::SLIP_STATE_INVALIDATED) {
            Logger::debug("Slip for order " . $payment_slip->cBestellNr . " is already '"  .$payment_slip->transaction_state . "'. (" . $payment_slip->id . ")");
        } else {
            Logger::debug("Slip for order " . $payment_slip->cBestellNr . " has unknown state: '"  .$payment_slip->transaction_state . "'. (" . $payment_slip->id . ")");
        }
        
        return parent::cancelOrder($kBestellung, $bDelete);
    }

}