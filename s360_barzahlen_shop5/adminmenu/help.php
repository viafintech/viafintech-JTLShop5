<?php

use Plugin\s360_barzahlen_shop5\lib\Config;
use JTL\Shop;
use Plugin\s360_barzahlen_shop5\lib\Helper;

$config = Config::getInstance();

$stmt = "SELECT * FROM tversandartzahlungsart as tvz, tversandart as tva WHERE tvz.kVersandart=tva.kVersandart AND tvz.kZahlungsart=" . $config->paymethod->getMethodID();
$tVersandarten = Shop::Container()->getDB()->executeQuery($stmt, 2);
if ($tVersandarten) {
    Shop::Smarty()->assign("tVersandarten", $tVersandarten);
}

if (Helper::isShopAtLeast52()) {
    Shop::Smarty()->assign("paymentMethodsUrl", "/admin/paymentmethods");
    Shop::Smarty()->assign("shippingMethodsUrl", "/admin/shippingmethods");
} else {
    Shop::Smarty()->assign("paymentMethodsUrl", "zahlungsarten.php");
    Shop::Smarty()->assign("shippingMethodsUrl", "versandarten.php");
}

Shop::Smarty()->display($config->plugin->getPaths()->getAdminPath() . 'template/help.tpl');