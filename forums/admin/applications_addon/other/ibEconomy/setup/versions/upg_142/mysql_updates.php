<?php
/**
 * (e32) ibEconomy
 * 1.4.1->1.4.2 DB Updates
 */

$SQL[] = "ALTER TABLE groups ADD g_eco_lottery tinyint(1) NOT NULL default '0';";
$SQL[] = "ALTER TABLE groups ADD g_eco_lottery_tix mediumint(5) NOT NULL default '0';";
$SQL[] = "ALTER TABLE groups ADD g_eco_lottery_odds tinyint(1) NOT NULL default '0';";

$SQL[] = "CREATE TABLE eco_lotteries (
	l_id mediumint(9) NOT NULL auto_increment,
	l_start_date int(11) NOT NULL default '0',
	l_draw_date int(11) NOT NULL default '0',
	l_initial_pot decimal(15,2) NOT NULL default '0',
	l_tix_purchased mediumint(9) NOT NULL default '0',
	l_tix_price decimal(10,2) NOT NULL default '0',
	l_winner_id int(11) NOT NULL default '0',
	l_final_pot_size decimal(15,2) NOT NULL default '0',
	l_num_balls tinyint(1) NOT NULL default '0',
	l_top_num smallint(2) NOT NULL default '0',
	PRIMARY KEY  (l_id)
);";

$SQL[] = "CREATE TABLE eco_lottery_tix (
	ltix_id int(15) NOT NULL auto_increment,
	ltix_purch_date int(11) NOT NULL default '0',
	ltix_lotto_id mediumint(9) NOT NULL default '0',
	ltix_paid decimal(10,2) NOT NULL default '0',
	ltix_member_id int(11) NOT NULL default '0',
	ltix_numbers varchar(64) NOT NULL default '',
	PRIMARY KEY  (ltix_id)
);";
	   
?>