<?php
/**
 * (e32) ibEconomy
 * 1.1.2->1.1.3 DB Updates
 */

$SQL[] = "ALTER TABLE eco_shop_items ADD si_extra_settings_1 varchar(255) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_extra_settings_2 varchar(255) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_extra_settings_3 varchar(255) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_extra_settings_4 varchar(255) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_extra_settings_5 varchar(255) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_extra_settings_6 varchar(255) NOT NULL default '';";

?>