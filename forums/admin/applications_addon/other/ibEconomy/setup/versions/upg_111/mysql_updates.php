<?php
/**
 * (e32) ibEconomy
 * 1.1.0->1.1.1 DB Updates
 */

$SQL[] = "ALTER TABLE eco_banks ADD b_image varchar(32) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_credit_cards ADD cc_image varchar(32) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_long_terms ADD lt_image varchar(32) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_cats ADD sc_image varchar(32) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_image varchar(32) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_stocks ADD s_image varchar(32) NOT NULL default '';";

?>