<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib;

use JTL\Shop;
use Plugin\s360_barzahlen_shop5\lib\Helper;


class Database {
    
    const TABLE_NAME_SLIP = "xplugin_s360_barzahlen_shop5_slip";
    
    private static $instance;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private $database;
    
    public function __construct(){
        $this->database = Shop::Container()->getDB();
    }
 
    public function insertSlip($obj) {
        return $this->database->insert(self::TABLE_NAME_SLIP, $obj);
    }

    public function selectPagedSlips($offset, $limit) {
        $result = $this->database->selectAll(self::TABLE_NAME_SLIP, ['slip_type'], [Config::SLIP_TYPE_PAYMENT], 'id', 'created_at DESC', $limit . ' OFFSET ' . $offset);
        if (empty($result)) {
            return [];
        }
        return $result;
    }
    
    public function searchAllSlips($search) {
        $search = str_replace(",", ".", $search);
        $stmt = "SELECT * FROM ".self::TABLE_NAME_SLIP." WHERE slip_type='payment' AND CONCAT(cBestellNr, '', id, '', transaction_amount) LIKE '%".$search."%' LIMIT 100";
        $result = $this->database->executeQueryPrepared($stmt, [], 2, false);
        if (empty($result)) {
            return [];
        }
        return $result;
    }
    
    public function selectAllSlips($keys=[], $values=[], $select='*', $order='', $limit='') {
        return $this->database->selectAll(self::TABLE_NAME_SLIP, $keys, $values, $select, $order, $limit);
    }
    
    public function getDailySlipsAmount($customer_key) {
        $now = new \DateTime(Helper::now());
        $before24 = $now->modify('-1 day');
        $params['customer_key'] = $customer_key;
        $params['before24'] = $before24->format('Y-m-d H:i:s');
        $stmt = "SELECT SUM(transaction_amount) as sum FROM ".self::TABLE_NAME_SLIP." 
            WHERE customer_key=:customer_key 
            AND created_at>=:before24 
            AND slip_type='".Config::SLIP_TYPE_PAYMENT."' 
            AND (transaction_state='".Config::SLIP_STATE_PAID."' 
            OR transaction_state='".Config::SLIP_STATE_PENDING."')";
        return $this->database->executeQueryPrepared($stmt, $params, 1, false);
    }

    public function selectSlipByTransactionID($transaction_id) {
        return $this->database->select(self::TABLE_NAME_SLIP, 'transaction_id', $transaction_id);
    }
    
    public function selectBySlipID($id) {
        return $this->database->select(self::TABLE_NAME_SLIP, 'id', $id);
    }
    
    public function selectSlipBykBestellung($kBestellung, $type="payment") {
        return $this->database->select(self::TABLE_NAME_SLIP, 'kBestellung', $kBestellung, 'slip_type', $type);
    }
    
    public function updateSlip($obj) {
        $obj->updated_at = Helper::now();
        return $this->database->update(self::TABLE_NAME_SLIP, 'id', $obj->id, $obj);
    }
    
    public function getWebhookUrl() {
        $kSprache = Shop::getLanguage(false);
        if ($kSprache <= 0) {
            $kSprache = Shop::Lang()->getDefaultLanguage()->kSprache;
        }
        if (!empty($kSprache)) {
            $kPlugin = Config::getInstance()->plugin->getID();                
            $queryPrepared = 'SELECT * FROM tpluginlinkdatei tpl, tseo ts WHERE ts.cKey = "kLink" AND ts.kKey = tpl.kLink AND tpl.kPlugin = :kPlugin AND ts.kSprache = :kSprache';
            $result = $this->database->executeQueryPrepared($queryPrepared, ['kPlugin' => $kPlugin, 'kSprache' => $kSprache], 1);
            if (!empty($result)) {
                return Shop::getURL(true) . '/' . $result->cSeo;
            }
        }
        return null;
    }
    
    public function totalSlips() {
        $result = $this->database->executeQuery('SELECT 1 FROM ' .self::TABLE_NAME_SLIP. " WHERE slip_type='" .Config::SLIP_TYPE_PAYMENT. "'", 3);
        if (empty($result)) {
            return 0;
        }
        return (int)$result;
    }
    
    /*
     * builds a database object by finalized order an response slip
     * or by payment and response slip 
     */
    public function prepareInsert($order, $slip) {
        $obj = new \stdClass();
        $obj->updated_at = Helper::now();
        //jtl-shop order data
        $obj->kBestellung = (int)$order->kBestellung;
        $obj->cBestellNr = $order->cBestellNr;
        if (Helper::isset_noempty($order->cRechnungsLand)) { //$order is payment slip
            $obj->cRechnungsLand = $order->cRechnungsLand;
        } else if (Helper::isset_noempty($order->oRechnungsadresse->cLand)) { //$order is finalized
            $obj->cRechnungsLand = $order->oRechnungsadresse->cLand;
        }
        if (Helper::isset_noempty($order->cLieferLand)) { //$order is payment slip
            $obj->cLieferLand = $order->cLieferLand;
        } else if (Helper::isset_noempty($order->Lieferadresse->cLand)) { //$order is finalized
            $obj->cLieferLand = $order->Lieferadresse->cLand;
        } 
        //viacash slip data
        if (Helper::isset_noempty($slip->refund->for_slip_id)) {
            $obj->for_slip_id = $slip->refund->for_slip_id; //set payment slip id
        } else {
             $obj->for_slip_id = $slip->id; //set self slip id
        }
        $obj->id = $slip->id; // set own id
        $obj->slip_type = $slip->slip_type; //payment or refund
        $obj->division_id = $slip->division_id;
        $obj->expires_at = $slip->expires_at;
        $obj->customer_key = $slip->customer->key; //equal to customer_email
        $obj->transaction_id = (int)$slip->transactions[0]->id;
        $obj->transaction_state = $slip->transactions[0]->state; 
        $obj->transaction_amount = (float)$slip->transactions[0]->amount;
        $obj->transaction_currency = $slip->transactions[0]->currency;
        return $obj;
    }
    
    public function prepareUpdate($slip) {
        $obj = new \stdClass();
        $obj->id = $slip->id;
        $obj->transaction_state = $slip->transactions[0]->state;
        return $obj;
    }
    
}

