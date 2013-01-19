<?php
/**
 * (e32) ibEconomy
 * 1.3.2->1.3.3 DB Updates
 */

$SQL[] = "ALTER TABLE eco_shop_items ADD si_allow_user_pm tinyint(1) NOT NULL default '0';";
$SQL[] = "ALTER TABLE eco_shop_items ADD si_default_pm text NOT NULL;";

?>