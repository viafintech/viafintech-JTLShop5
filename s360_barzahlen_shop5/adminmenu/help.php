<?php

use Plugin\s360_barzahlen_shop5\lib\Config;
use JTL\Shop;

$config = Config::getInstance();

$stmt = "SELECT * FROM tversandartzahlungsart as tvz, tversandart as tva WHERE tvz.kVersandart=tva.kVersandart AND tvz.kZahlungsart=" . $config->paymethod->getMethodID();
$tVersandarten = Shop::Container()->getDB()->executeQuery($stmt, 2);
if ($tVersandarten) {
    Shop::Smarty()->assign("tVersandarten", $tVersandarten);
}

Shop::Smarty()->display($config->plugin->getPaths()->getAdminPath() . 'template/help.tpl');