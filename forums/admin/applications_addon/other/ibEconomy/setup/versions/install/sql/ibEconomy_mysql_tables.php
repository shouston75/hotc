<?php

//ibEconomy Banks
$TABLE[] = "CREATE TABLE eco_banks (
	b_id smallint(3) NOT NULL auto_increment,
	b_title varchar(128) NOT NULL default '',
	b_savings_on tinyint(1) NOT NULL default '0',
	b_checking_on tinyint(1) NOT NULL default '0',
	b_c_acnt_cost decimal(10,2) NOT NULL default '0',
	b_s_acnt_cost decimal(10,2) NOT NULL default '0',
	b_sav_interest int(15) NOT NULL default '0',
	b_c_dep_fee decimal(10,2) NOT NULL default '0',
	b_s_dep_fee decimal(10,2) NOT NULL default '0',
	b_c_wthd_fee decimal(10,2) NOT NULL default '0',
	b_s_wthd_fee decimal(10,2) NOT NULL default '0',
	b_loans_on tinyint(1) NOT NULL default '0',
	b_loans_max mediumint(9) NOT NULL default '0',
	b_loans_fee decimal(10,2) NOT NULL default '0',
	b_loans_days smallint(3) NOT NULL default '0',
	b_loans_pen decimal(10,2) NOT NULL default '0',
	b_loans_app_fee mediumint(6) NOT NULL default '0',
	b_loans_count mediumint(9) NOT NULL default '0',
	b_added_on int(11) NOT NULL default '0',		
	b_use_perms tinyint(1) NOT NULL default '0',
	b_position smallint(3) NOT NULL default '0',
	b_on tinyint(1) NOT NULL default '0',
	b_image varchar(32) NOT NULL default '',
	PRIMARY KEY  (b_id)
);";
	
// ibEconomy cart	
$TABLE[] = "CREATE TABLE eco_cart (
  c_id int(3) NOT NULL auto_increment,
  c_member_id int(8) NOT NULL default '0',
  c_member_name varchar(128) NOT NULL default '',
  c_type varchar(16) NOT NULL default '',
  c_type_id mediumint(5) NOT NULL default '0',
  c_type_class varchar(16) NOT NULL default '',
  c_quantity decimal(16,2) NOT NULL default '0',  
  PRIMARY KEY  (c_id)
);";

// ibEconomy Credit-Cards
$TABLE[] = "CREATE TABLE eco_credit_cards (
  cc_id int(3) NOT NULL auto_increment,
  cc_title varchar(128) NOT NULL default '',
  cc_cost decimal(10,2) NOT NULL default '0',
  cc_max int(16) NOT NULL default '0',
  cc_apr decimal(10,2) NOT NULL default '0',
  cc_csh_adv tinyint(1) NOT NULL default '0',
  cc_csh_adv_fee decimal(10,2) NOT NULL default '0',
  cc_bal_trnsfr tinyint(1) NOT NULL default '0',
  cc_bal_trnsfr_apr decimal(10,2) NOT NULL default '0',
  cc_bal_trnsfr_end int(9) NOT NULL default '0',
  cc_bal_trnsfr_fee decimal(10,2) NOT NULL default '0',
  cc_apr_max decimal(10,2) NOT NULL default '0',
  cc_apr_min decimal(10,2) NOT NULL default '0',
  cc_allow_od tinyint(1) NOT NULL default '0',
  cc_od_pnlty mediumint(9) NOT NULL default '0',
  cc_max_od int(16) NOT NULL default '0',
  cc_no_pay_chrg decimal(10,2) NOT NULL default '0',
  cc_added_on int(11) NOT NULL default '0',
  cc_use_perms tinyint(1) NOT NULL default '0',
  cc_position smallint(3) NOT NULL default '0',
  cc_on tinyint(1) NOT NULL default '0',  
  cc_image varchar(32) NOT NULL default '',
  PRIMARY KEY  (cc_id)
);";

// ibEconomy Logs
$TABLE[] = "CREATE TABLE eco_logs (
  l_id int(10) NOT NULL auto_increment,
  l_member_id mediumint(8) NOT NULL default '0',
  l_action varchar(16) NOT NULL default '',
  l_amount decimal(16,2) NOT NULL default '0',
  l_subject_id mediumint(8) NOT NULL default '0',
  l_subject_name varchar(64) NOT NULL default '',
  l_log text NOT NULL,
  l_date int(11) NOT NULL default '0',
  l_ip_address varchar(16) NOT NULL default '',  
  PRIMARY KEY  (l_id)
);";

//ibEconomy Long-Term Investments
$TABLE[] = "CREATE TABLE eco_long_terms (
	lt_id smallint(3) NOT NULL auto_increment,
	lt_title varchar(32) NOT NULL default '',
	lt_type varchar(16) NOT NULL default '',
	lt_min_days mediumint(9) NOT NULL default '0',
	lt_min decimal(10,2) NOT NULL default '0',
	lt_risk_level smallint(3) NOT NULL default '0',
	lt_early_cash tinyint(1) NOT NULL default '0',
	lt_early_cash_fee decimal(10,2) NOT NULL default '0',
	lt_added_on mediumint(11) NOT NULL default '0',
	lt_use_perms tinyint(1) NOT NULL default '0',
	lt_position smallint(3) NOT NULL default '0',
	lt_on tinyint(1) NOT NULL default '0',
	lt_image varchar(32) NOT NULL default '',
	PRIMARY KEY  (lt_id)
);";

//ibEconomy Portfolio/Assets
$TABLE[] = "CREATE TABLE eco_portfolio (
	p_id mediumint(9) NOT NULL auto_increment,
	p_member_id mediumint(8) NOT NULL default '0',
	p_member_name varchar(128) NOT NULL default '',
	p_type varchar(16) NOT NULL default '0',
	p_type_id mediumint(5) NOT NULL default '0',
	p_type_class varchar(16) NOT NULL default '',
	p_amount decimal(16,2) NOT NULL default '0',
	p_max decimal(16,2) NOT NULL default '0',
	p_rate decimal(10,2) NOT NULL default '0',
	p_last_hit int(11) NOT NULL default '0',
	p_rate_ends int(11) NOT NULL default '0',
	p_rate_next decimal(10,2) NOT NULL default '0',
	p_purch_date int(11) NOT NULL default '0',
	p_update_date int(11) NOT NULL default '0',
	PRIMARY KEY  (p_id)
);";

//ibEconomy Shop Categories
$TABLE[] = "CREATE TABLE eco_shop_cats (
	sc_id smallint(4) NOT NULL auto_increment,
	sc_title varchar(30) NOT NULL default '',
	sc_desc text NOT NULL,
	sc_inventory smallint(4) NOT NULL default '0',
	sc_use_perms tinyint(1) NOT NULL default '0',
	sc_position smallint(4) NOT NULL default '0',
	sc_on tinyint(1) NOT NULL default '0',
	sc_image varchar(32) NOT NULL default '',
	PRIMARY KEY  (sc_id)
);";

//ibEconomy Shop Items
$TABLE[] = "CREATE TABLE eco_shop_items (
	si_id int(10) NOT NULL auto_increment,
	si_title varchar(128) NOT NULL default '',
	si_desc text NOT NULL,
	si_cat smallint(4) NOT NULL default '0',
	si_cost decimal(10,2) NOT NULL default '0',
	si_inventory int(10) NOT NULL default '0',
	si_restock tinyint(1) NOT NULL default '0',
	si_restock_amt int(10) NOT NULL default '0',
	si_restock_time smallint(1) NOT NULL default '0',
	si_limit int(10) NOT NULL default '0',
	si_other_users tinyint(1) NOT NULL default '0',
	si_min_num int(10) NOT NULL default '0',
	si_max_num int(10) NOT NULL default '0',
	si_protected varchar(255) NOT NULL default '',
	si_protected_g varchar(255) NOT NULL default '',
	si_added_on int(11) NOT NULL default '0',
	si_use_perms tinyint(1) NOT NULL default '0',
	si_file varchar(64) NOT NULL default '',
	si_sold int(11) NOT NULL default '0',
	si_last_restock int(11) NOT NULL default '0',
	si_position smallint(4) NOT NULL default '0',
	si_on tinyint(1) NOT NULL default '0',
	si_image varchar(32) NOT NULL default '',
	si_extra_settings_1 varchar(255) NOT NULL default '',
	si_extra_settings_2 varchar(255) NOT NULL default '',
	si_extra_settings_3 varchar(255) NOT NULL default '',
	si_extra_settings_4 varchar(255) NOT NULL default '',
	si_extra_settings_5 varchar(255) NOT NULL default '',
	si_extra_settings_6 varchar(255) NOT NULL default '',
	si_allow_user_pm tinyint(1) NOT NULL default '0',
	si_default_pm text NOT NULL,
	si_max_daily_buys smallint(3) NOT NULL default '0',
	PRIMARY KEY  (si_id)
);";

//ibEconomy Sidebar Blocks
$TABLE[] = "CREATE TABLE eco_sidebar_blocks (
	sb_id int(5) NOT NULL auto_increment,
	sb_title varchar(128) NOT NULL default '',
	sb_item_type varchar(24) NOT NULL default '',
	sb_display_type varchar(24) NOT NULL default '',
	sb_display_num smallint(3) NOT NULL default '0',
	sb_display_order varchar(24) NOT NULL default '',
	sb_show_text tinyint(1) NOT NULL default '0',
	sb_pic varchar(48) NOT NULL default '',
	sb_font_color varchar(24) NOT NULL default '',
	sb_bg_color varchar(24) NOT NULL default '',
	sb_boxed tinyint(1) NOT NULL default '0',
	sb_custom tinyint(1) NOT NULL default '0',
	sb_raw tinyint(1) NOT NULL default '0',
	sb_custom_content text NOT NULL,
	sb_on_index tinyint(1) NOT NULL default '0',
	sb_use_perms tinyint(1) NOT NULL default '0',
	sb_position smallint(3) NOT NULL default '0',
	sb_on tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (sb_id)
);";

//ibEconomy Stocks
$TABLE[] = "CREATE TABLE eco_stocks (
	s_id smallint(5) NOT NULL auto_increment,
	s_title varchar(16) NOT NULL default '',
	s_title_long varchar(64) NOT NULL default '',
	s_type varchar(16) NOT NULL default '',
	s_type_var varchar(16) NOT NULL default '',
	s_type_var_value mediumint(9) NOT NULL default '0',
	s_risk_level smallint(3) NOT NULL default '0',
	s_value decimal(10,2) NOT NULL default '0',
	s_limit mediumint(9) NOT NULL default '0',
	s_can_trade tinyint(1) NOT NULL default '0',
	s_last_calc mediumint(9) NOT NULL default '0',
	s_last_calc_dif mediumint(9) NOT NULL default '0',
	s_last_run int(11) NOT NULL default '0',
	s_added_on int(11) NOT NULL default '0',
	s_use_perms tinyint(1) NOT NULL default '0',
	s_position smallint(3) NOT NULL default '0',
	s_on tinyint(1) NOT NULL default '0',
	s_image varchar(32) NOT NULL default '',
	PRIMARY KEY  (s_id)
);";

//ibEconomy Lotteries
$TABLE[] = "CREATE TABLE eco_lotteries (
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
	l_winning_nums varchar(64) NOT NULL default '',
	l_winners varchar(128) NOT NULL default '',
	PRIMARY KEY  (l_id)
);";

//ibEconomy Lottery Tix
$TABLE[] = "CREATE TABLE eco_lottery_tix (
	ltix_id int(15) NOT NULL auto_increment,
	ltix_purch_date int(11) NOT NULL default '0',
	ltix_lotto_id mediumint(9) NOT NULL default '0',
	ltix_paid decimal(10,2) NOT NULL default '0',
	ltix_member_id int(11) NOT NULL default '0',
	ltix_numbers varchar(64) NOT NULL default '',
	PRIMARY KEY  (ltix_id)
);";

//Groups
$TABLE[] = "ALTER TABLE groups ADD g_eco tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_bank tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_welfare tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_loan tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_stock tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_cc tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_lt tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_shopitem tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_asset tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_transaction tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_lt_max mediumint(9) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_bank_max mediumint(9) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_cash_adv_max mediumint(9) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_stock_max mediumint(9) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_welfare_max mediumint(9) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_bal_trnsfr_max mediumint(9) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_max_pts int(21) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_max_cc_debt int(21) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_max_loan_debt int(21) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_frm_ptsx decimal(4,2) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_edit_pts tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_lottery tinyint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_lottery_tix mediumint(1) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE groups ADD g_eco_lottery_odds tinyint(1) NOT NULL default '0'";

//Members
$TABLE[] = "ALTER TABLE pfields_content ADD eco_points decimal(21,2) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE pfields_content ADD eco_worth decimal(21,2) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE pfields_content ADD eco_welfare decimal(21,2) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE pfields_content ADD eco_on_welfare int(11) NOT NULL default '0'";

//Forums
$TABLE[] = "ALTER TABLE forums ADD eco_tpc_pts decimal(10,2) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE forums ADD eco_rply_pts decimal(10,2) NOT NULL default '0'";
$TABLE[] = "ALTER TABLE forums ADD eco_get_rply_pts decimal(10,2) NOT NULL default '0'";

?>