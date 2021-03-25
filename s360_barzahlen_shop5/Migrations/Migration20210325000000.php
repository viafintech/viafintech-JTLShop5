<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;


class Migration20210325000000 extends Migration implements IMigration {
    
    const TABLE_NAME_SLIP = "xplugin_s360_barzahlen_shop5_slip";
    
    public function up() {
        $this->execute("ALTER TABLE `".self::TABLE_NAME_SLIP."` CHANGE `cBestellNr` `cBestellNr` VARCHAR(20) NOT NULL");
    }
    
    public function down() {
        //nothing to do
    }
    
}