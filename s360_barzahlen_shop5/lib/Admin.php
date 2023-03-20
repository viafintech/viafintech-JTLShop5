<?php

declare(strict_types=1);

namespace Plugin\s360_barzahlen_shop5\lib;

use Plugin\s360_barzahlen_shop5\lib\Helper;
use Plugin\s360_barzahlen_shop5\lib\Config;
use Plugin\s360_barzahlen_shop5\lib\Logger;
use Plugin\s360_barzahlen_shop5\lib\Database;
use JTL\Shop;

class Admin
{

    const PAGINATION_PER_PAGE = 25;

    private $database;
    private $plugin;
    private $request;
    private $apiClient;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->plugin = Config::getInstance()->plugin;
        $this->request = $_REQUEST;
    }

    public function handle()
    {
        if ($this->isAjaxRequest()) {
            $this->handleAjax();
        } else {
            $this->handleDisplay();
        }
    }

    private function isAjaxRequest()
    {
        return (isset($this->request['isAjax']) && (int) $this->request['isAjax'] === 1);
    }

    private function handleAjax()
    {
        header('Content-Type: application/json');
        try {
            switch ($this->request['action']) {
                case 'loadOrders':
                    echo json_encode($this->getSlips((int) $this->request['page']));
                    break;
                case 'getSlipInfo':
                    echo json_encode($this->getSlipInfo($this->request['slipId']));
                    break;
                case 'getRefundForm':
                    echo json_encode($this->getRefundForm($this->request['slipId']));
                    break;
                case 'performRefund':
                    echo json_encode($this->performRefund($this->request['slipId'], $this->request['refundValue']));
                    break;
                case 'invalidateSlip':
                    echo json_encode($this->invalidateSlip($this->request['slipId']));
                    break;
                case 'resendSlip':
                    echo json_encode($this->resendSlip($this->request['slipId']));
                    break;
                case 'searchOrders':
                    echo json_encode($this->searchSlips($this->request['search']));
                    break;
                default:
                    throw new \Exception('handleAjax(): Unknown action requested.');
            }
        } catch (\Exception $ex) {
            Logger::debug($ex->getMessage());
            echo json_encode([
                'result' => 'error',
                'message' => $ex->getMessage()
            ]);
        }
        exit();
    }

    private function handleDisplay()
    {
        if (isset($this->request['page'])) {
            Shop::Smarty()->assign("page", $this->request['page']);
        } else {
            Shop::Smarty()->assign("page", 1);
        }
        Shop::Smarty()->assign("webhook", $this->database->getWebhookUrl());
        Shop::Smarty()->assign("apiConfig", Config::getInstance()->getApiConfig());
        Shop::Smarty()->assign('AdminmenuPfad', $this->plugin->getPaths()->getAdminURL());
        Shop::Smarty()->assign('AdminPluginURL', Shop::getURL(true) . '/admin/plugin.php?kPlugin=' . $this->plugin->getID());
        Shop::Smarty()->display($this->plugin->getPaths()->getAdminPath() . "template/payments.tpl");
    }

    private function searchSlips($search)
    {
        try {
            $slips = [];
            $payment_slips = $this->database->searchAllSlips($search);
            foreach ($payment_slips as $slip) {
                $slips[] = $this->prepareSlip($slip->id);
            }

            Shop::Smarty()->assign('slips', $slips);
            $html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/payments_inc.tpl');
            return [
                'result' => 'success',
                'html' => $html
            ];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function resendSlip($slip_id)
    {
        $slip = $this->database->selectBySlipID($slip_id);
        $this->apiClient = new APIClient($slip->cRechnungsLand);

        $request = $this->apiClient->ResendRequest($slip->id);

        try {
            if ($slip->transaction_state != Config::SLIP_STATE_PENDING) {
                throw new \Exception("Slip state is not " . Config::SLIP_STATE_PENDING);
            }
            $this->apiClient->handle($request);

            return ['result' => 'success'];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function invalidateSlip($slip_id)
    {
        $slip = $this->database->selectBySlipID($slip_id);
        $this->apiClient = new APIClient($slip->cRechnungsLand);

        $request = $this->apiClient->InvalidateRequest($slip->id);

        try {
            if ($slip->transaction_state != Config::SLIP_STATE_PENDING) {
                throw new \Exception("Slip state is not " . Config::SLIP_STATE_PENDING);
            }
            $response = $this->apiClient->handle($request);
            $invalid_slip = json_decode($response);
            $obj = $this->database->prepareUpdate($invalid_slip);
            $this->database->updateSlip($obj);

            return ['result' => 'success'];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function performRefund($slip_id, $refund_value)
    {
        $payment_slip = $this->database->selectBySlipID($slip_id);
        $this->apiClient = new APIClient($payment_slip->cRechnungsLand);

        $request = $this->apiClient->CreateRequest();
        $request->setSlipType(Config::SLIP_TYPE_REFUND);
        $request->setForSlipId($payment_slip->id);
        $request->setHookUrl($this->database->getWebhookUrl());
        $request->setTransaction("-" . number_format((float) str_replace(",", ".", $refund_value), 2), $payment_slip->transaction_currency);

        try {
            if ($payment_slip->transaction_state != Config::SLIP_STATE_PAID) {
                throw new \Exception("Slip state is not " . Config::SLIP_STATE_PAID);
            }
            $response = $this->apiClient->handle($request);
            $refund_slip = json_decode($response);
            $obj = $this->database->prepareInsert($payment_slip, $refund_slip);
            $this->database->insertSlip($obj);

            return ['result' => 'success'];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function getRefundForm($slip_id)
    {
        try {
            $slip = $this->database->selectBySlipID($slip_id);
            Shop::Smarty()->assign('slip', $slip);
            $html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/refund.tpl');

            return [
                'result' => 'success',
                'html' => $html
            ];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function getSlipInfo($slip_id)
    {
        try {
            $slip = $this->prepareSlip($slip_id);
            Shop::Smarty()->assign('slip', $slip);
            $html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/slip.tpl');

            return [
                'result' => 'success',
                'html' => $html
            ];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function getSlips($page)
    {
        try {
            $slips = [];
            $total = $this->database->totalSlips();
            $limit = self::PAGINATION_PER_PAGE;
            $offset = max(0, ($page - 1) * $limit);

            Shop::Smarty()->assign("page", (int) $this->request['page']);
            Shop::Smarty()->assign("maxpage", ceil($total / $limit));

            $payment_slips = $this->database->selectPagedSlips($offset, $limit);
            foreach ($payment_slips as $slip) {
                $slips[] = $this->prepareSlip($slip->id);
            }

            Shop::Smarty()->assign('slips', $slips);
            $html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/payments_inc.tpl');
            return [
                'result' => 'success',
                '$total' => $total,
                'html' => $html
            ];
        } catch (\Exception $ex) {
            return $this->logReturnError($ex);
        }
    }

    private function logReturnError($ex)
    {
        Logger::debug($ex->getMessage());
        return [
            'result' => 'error',
            'message' => $ex->getMessage()
        ];
    }

    private function prepareSlip($slip_id)
    {
        $slip = $this->database->selectBySlipID($slip_id);
        $slip->created_at = $this->convertDate($slip->created_at);
        $slip->updated_at = $this->convertDate($slip->updated_at);
        $slip->expires_at = $this->convertDate($slip->expires_at);
        $refunds = $this->database->selectAllSlips(["for_slip_id", "slip_type"], [$slip->id, Config::SLIP_TYPE_REFUND]);
        foreach ($refunds as $refund) {
            $refund->created_at = $this->convertDate($refund->created_at, false);
            $refund->expires_at = $this->convertDate($refund->expires_at, false);
            $slip->refunds[] = $this->setSlipActions($refund);
        }
        $slip = $this->setSlipActions($slip);
        return $slip;
    }

    private function convertDate($date, $time = true)
    {
        if (Helper::date_null($date)) {
            return "";
        }
        $date = new \DateTime($date);
        if ($time) {
            return $date->format("d.m.Y H:i:s");
        }
        return $date->format("d.m.Y");
    }

    private function setSlipActions($slip)
    {
        if (!isset($slip->actions)) {
            $slip->actions = (object) [];
        }
        if ($slip->transaction_state === Config::SLIP_STATE_PENDING) {
            $slip->actions->resend = true;
            $slip->actions->invalidate = true;
        }

        if ($slip->slip_type === Config::SLIP_TYPE_PAYMENT && $slip->transaction_state === Config::SLIP_STATE_PAID) {
            $total_refund = 0;
            foreach ($slip->refunds as $refund) {
                if ($refund->transaction_state === Config::SLIP_STATE_PAID || $refund->transaction_state === Config::SLIP_STATE_PENDING) {
                    $total_refund += (float) $refund->transaction_amount;
                }
            }
            if ($total_refund * (-1) < $slip->transaction_amount) {
                $slip->actions->refund = true;
            }
            $slip->total_refund = number_format((float) $total_refund, 2);
        }
        return $slip;
    }
}
