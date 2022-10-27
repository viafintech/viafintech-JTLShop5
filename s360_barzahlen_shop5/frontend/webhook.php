<?php

namespace Plugin\s360_barzahlen_shop5\frontend;

use JTL\Checkout\Bestellung;
use Barzahlen;
use Plugin\s360_barzahlen_shop5\lib\Config;
use Plugin\s360_barzahlen_shop5\lib\Database;
use Plugin\s360_barzahlen_shop5\lib\Logger;
use Plugin\s360_barzahlen_shop5\lib\Barzahlen\Webhook;

$header['REQUEST_METHOD'] = "POST";
$header['REQUEST_URI'] = filter_input(INPUT_SERVER, 'REQUEST_URI');
$header['QUERY_STRING'] = "";
$header['HTTP_DATE'] = filter_input(INPUT_SERVER, 'HTTP_DATE');
$header['HTTP_BZ_SIGNATURE'] = filter_input(INPUT_SERVER, 'HTTP_BZ_SIGNATURE');
$header['HTTP_X_FORWARDED_HOST'] = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_HOST');
$header['HTTP_X_FORWARDED_PORT'] = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_PORT');
$header['HTTP_HOST'] = filter_input(INPUT_SERVER, 'HTTP_HOST');
$header['SERVER_PORT'] = filter_input(INPUT_SERVER, 'SERVER_PORT');

$body = file_get_contents("php://input");

$config = Config::getInstance();
$apiConfig = $config->getApiConfig();
$verified = false;
$webhook = null;

if (!empty($apiConfig)) {
    // try verify incoming response
    foreach ($apiConfig as $conf) {
        $webhook = new Webhook($conf->APIKey);
        $verified = $webhook->verify($header, $body);
        if ($verified) {
            break;
        }
    }
}

if (!$verified) {
    Logger::debug("Webhook response could not be verified.");
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

// catch response
if ($header['REQUEST_METHOD'] === "POST" && !empty($body)) {
    Logger::api_message(
        (new \ReflectionClass($webhook))->getShortName(),
        "",
        $body
    );
}

// response is verified
$response = json_decode($body);
$database = Database::getInstance();
$payment_slip = $database->selectSlipByTransactionID($response->affected_transaction_id);

if (!empty($payment_slip)) {
    $obj = $database->prepareUpdate($response->slip);
    $database->updateSlip($obj);
} else {
    Logger::debug("Transaction ID '" . $response->affected_transaction_id . "' not found. (" . $response->slip->id . ")");
}

// handle payment event
if ($response->slip->slip_type === Config::SLIP_TYPE_PAYMENT && $response->event === Config::SLIP_STATE_PAID)
{
    $order = new Bestellung((int) $payment_slip->kBestellung, true);
    if (!empty($order)) {
        $payment = [
            'fBetrag' => $response->slip->transactions[0]->amount,
            'cISO' => $response->slip->transactions[0]->currency,
            'dZeit' => $response->event_occurred_at,
            'cHinweis' => $response->slip->id
        ];
        $barzahlen = new Barzahlen($config->paymethod->getModuleID());
        $barzahlen->addIncomingPayment($order, (object) $payment);
    } else {
        Logger::debug("kBestellung '" . $payment_slip->kBestellung . "' not found. Skipping incoming payment for transaction '" . $response->affected_transaction_id . "'.");
    }
}

// handle unknown refund events and insert transaction, if created in cotrol center
if ($response->slip->slip_type === Config::SLIP_TYPE_REFUND && empty($payment_slip))
{
    // try to recover order information
    $payment_slip = $database->selectBySlipID($response->slip->refund->for_slip_id);
    if (!empty($payment_slip)) {
        $obj = $database->prepareInsert($payment_slip, $response->slip);
        $database->insertSlip($obj);
    } else {
        Logger::debug("Payment Slip '" . $response->slip->refund->for_slip_id . "' not found. Skipping incoming refund for transaction '" . $response->affected_transaction_id . "'.");
    }
}

header("HTTP/1.1 200 OK");
exit();
