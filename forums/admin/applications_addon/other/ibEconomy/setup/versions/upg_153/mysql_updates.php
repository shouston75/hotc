<?php

// Remove unused settings
$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key='eco_pppc_grp_adj';";
$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key='eco_pppc_commenter';";
$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key='eco_pppc_receiver';";
$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key='eco_pppc_own_profile';";
$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key='eco_pppc_time';";
