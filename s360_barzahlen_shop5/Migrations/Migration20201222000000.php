<?php

declare(strict_types=1);

namespace Plugin\s360_barzahlen_shop5\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20201222000000 extends Migration implements IMigration
{

    const TABLE_NAME_SLIP = "xplugin_s360_barzahlen_shop5_slip";

    public function up()
    {
        $stmt = "CREATE TABLE IF NOT EXISTS `" . self::TABLE_NAME_SLIP . "` (
            `slip_id` int(10) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`slip_id`),
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL,
            `kBestellung` int(10) NOT NULL,
            `cBestellNr` int(10) NOT NULL,
            `cRechnungsLand` varchar(2) NOT NULL COMMENT 'iso2',
            `cLieferLand` varchar(2) NOT NULL COMMENT 'iso2',
            `for_slip_id` varchar(40) NOT NULL,
            `id` varchar(40) NOT NULL,
            `slip_type` varchar(7) NOT NULL COMMENT 'payment, refund',
            `division_id` int(5) NOT NULL,
            `expires_at` datetime NOT NULL,
            `customer_key` varchar(255) NOT NULL COMMENT 'email',
            `transaction_id` int(10) NOT NULL,
            `transaction_state` varchar(11) NOT NULL COMMENT 'pending,paid,expired,invalidated',
            `transaction_amount` float(5,2) NOT NULL,
            `transaction_currency` varchar(3) NOT NULL COMMENT 'iso3')
        ";
        $this->execute($stmt);
    }

    public function down()
    {
        if ($this->doDeleteData()) {
            $this->execute("DROP TABLE IF EXISTS `" . self::TABLE_NAME_SLIP . "`");
        }
    }
}
