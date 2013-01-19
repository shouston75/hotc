
 ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 Date: Fri, 04 Jan 2013 15:41:40 +0000
 Error: 1064 - You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ')  SET a.like_id=MD5(CONCAT('forums',';','topics',';',a.like_member_id)),a.like_' at line 1
 IP Address: 69.71.191.105 - /forums/index.php?
 ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 mySQL query error: UPDATE ibf_core_like a  LEFT JOIN ibf_core_like b ON ( b.like_app='forums' AND b.like_area='topics' AND b.like_rel_id= )  SET a.like_id=MD5(CONCAT('forums',';','topics',';',a.like_member_id)),a.like_lookup_id=MD5(CONCAT('forums',';','topics',';','')),a.like_lookup_area=MD5(CONCAT('forums',';','topics',';',a.like_member_id)),a.like_rel_id=0 WHERE a.like_app='forums' AND a.like_area='topics' AND a.like_rel_id= AND b.like_id  IS NULL 
 .--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------.
 | File                                                                       | Function                                                                      | Line No.          |
 |----------------------------------------------------------------------------+-------------------------------------------------------------------------------+-------------------|
 | admin/sources/classes/like/composite.php                                   | [db_main_mysql].update                                                        | 1751              |
 '----------------------------------------------------------------------------+-------------------------------------------------------------------------------+-------------------'
 | admin/applications/forums/modules_public/moderate/moderate.php             | [classes_like_composite].merge                                                | 3417              |
 '----------------------------------------------------------------------------+-------------------------------------------------------------------------------+-------------------'
 | admin/applications/forums/modules_public/moderate/moderate.php             | [public_forums_moderate_moderate]._multiTopicMerge                            | 3170              |
 '----------------------------------------------------------------------------+-------------------------------------------------------------------------------+-------------------'
 | admin/applications/forums/modules_public/moderate/moderate.php             | [public_forums_moderate_moderate]._multiTopicModify                           | 194               |
 '----------------------------------------------------------------------------+-------------------------------------------------------------------------------+-------------------'
 | admin/sources/base/ipsController.php                                       | [public_forums_moderate_moderate].doExecute                                   | 306               |
 '----------------------------------------------------------------------------+-------------------------------------------------------------------------------+-------------------'