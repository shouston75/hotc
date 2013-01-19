<?php

/*e$ Bookie Mod 1.0.1* ACP/
/*emoney isn't a coder*/
/*Created for Fantasy Football Haven (http://fantasyfootballhaven.com)*/



if ( !defined( 'IN_ACP' ) )

	{

	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";

	exit();
	
	}

class ad_bookie

{

	var $ipsclass;

	function auto_run()

	{
		$this->ipsclass->form_code = str_replace('&amp;', '&', $this->ipsclass->form_code);
		
		switch ($this->ipsclass->input['code'])

		{

			case 'settings':
				$this->manage_settings();
				break;
			case 'save':
				$this->save_settings();
				break;
			case 'gperms':
				$this->group_permissions();
				break;
			case 'edit-gperms':
				$this->edit_group_permissions();
				break;
			case 'do-edit-gperms':
				$this->do_edit_group_permissions();
				break;

			default:

				$this->group_permissions();

				break;

		}

	}
	
	function manage_settings()

	{

		$this->ipsclass->admin->page_detail = "Modification settings";

		$this->ipsclass->admin->page_title  = "ibBookie :: Manage Settings";



		require_once(ROOT_PATH.'sources/action_admin/settings.php');

		$settings           =  new ad_settings();

		$settings->ipsclass =& $this->ipsclass;


		$settings->get_by_key        = 'bookie';

		$settings->return_after_save = $this->ipsclass->form_code.'&code=settings';

		
		$settings->setting_view().$this->c();

	}	
	
	function save_settings()

	{
	
		require_once( ROOT_PATH.'sources/action_admin/settings.php' );

		$adsettings           =  new ad_settings();

		$adsettings->ipsclass =& $this->ipsclass;

		
		
		$adsettings->setting_rebuildcache();

		$this->manage_settings();

	}
	
	function c()

	{

		return "<div align='center' class='menuouterwrap' style='padding:5px'>ibBookie 1.0.1 &copy; 2008 <a href='http://www.fantasyfootballhaven.com'><b>emoney</b></a></div>";

	}
	
	function group_permissions()

	{

		//-----------------------------------------
		// Make sure we're a root admin, or else!
		//-----------------------------------------
		
		if ($this->ipsclass->member['mgroup'] != $this->ipsclass->vars['admin_group'])
		{
			$this->ipsclass->admin->error("Sorry, these functions are for the root admin group only");
		}

		$this->ipsclass->admin->page_detail = "Select which user group to edit permissions for.";

		$this->ipsclass->admin->page_title  = "ibBookie :: Group Permissions";



		$this->ipsclass->adskin->td_header[] = array("&nbsp;", "95%");

		$this->ipsclass->adskin->td_header[] = array("&nbsp;", "5%");



		$this->ipsclass->html .= $this->ipsclass->adskin->start_table("User Groups");



		$gc = $this->ipsclass->cache['group_cache'];


		foreach ($gc as $id => $gd)

		{

			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

			(

				$gd['prefix'].$gd['g_title'].$gd['suffix'],

				"<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' id='group-menu-{$id}' border='0' alt='Options' class='ipd' /></div>

				<script type='text/javascript'>

				menu_build_menu

				(

					'group-menu-{$id}',

					new Array

					(

						img_edit+\" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit-gperms&id={$id}'>Edit Permissions</a>\"

					)

				);

				</script>",

			));

		}



		$this->ipsclass->html .= $this->ipsclass->adskin->end_table().$this->c();



		$this->ipsclass->admin->output();

	}
	
	function edit_group_permissions()

	{

		$id = intval($this->ipsclass->input['id']);

		if (!$id)

		{

			$this->ipsclass->admin->error('Invalid ID provided, please provide a valid ID to edit.');

		}



		$g = $this->ipsclass->DB->build_and_exec_query(array('select' => '*', 'from' => 'groups', 'where' => 'g_id='.$id));

		if (!$this->ipsclass->DB->get_num_rows())

		{

			$this->ipsclass->admin->error('That group does not seem to exist.');

		}



		$this->ipsclass->admin->page_detail = "Edit the permissions for this user group.";

		$this->ipsclass->admin->page_title  = "ibBookie :: Group Permissions :: Edit Permissions";



		$this->ipsclass->adskin->td_header[] = array("&nbsp;", "60%");

		$this->ipsclass->adskin->td_header[] = array("&nbsp;", "40%");



		$this->ipsclass->html .= $this->ipsclass->adskin->start_form(array

		(

			0 => array('section', $this->ipsclass->section_code),

			1 => array('act'    , $this->ipsclass->input['act']),

			2 => array('code'   ,'do-edit-gperms'),

			3 => array('id'     , $g['g_id']),

		));



		$this->ipsclass->html .= $this->ipsclass->adskin->start_table("User Groups Permissions :: ".$g['g_title']);

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group can access ibBookie?</b><br />",

			$this->ipsclass->adskin->form_yes_no('ibbookie_groups', $g['ibbookie_groups']),

		));
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group can add games in ibBookie?</b><br />",

			$this->ipsclass->adskin->form_yes_no('ibbookie_add_groups', $g['ibbookie_add_groups']),

		));		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group can moderate ibBookie? (edit games, add and edit categories)</b>",

			$this->ipsclass->adskin->form_yes_no('ibbookie_mod_groups', $g['ibbookie_mod_groups']),

		));		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group can administrate ibBookie? (pay winners, ban users from ibBookie)</b>",

			$this->ipsclass->adskin->form_yes_no('ibbookie_admin_groups', $g['ibbookie_admin_groups']),

		));

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>How many points may a member of this group wager on any one event? Leave set to 0 to disable.</b><br />",

			$this->ipsclass->adskin->form_input('ibbookie_max_pts', $g['ibbookie_max_pts']),

		));		

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('Save Permissions');

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table().$this->c();



		$this->ipsclass->admin->output();

	}
	
	function do_edit_group_permissions()

	{

		$id = intval($this->ipsclass->input['id']);

		if (!$id)

		{

			$this->ipsclass->admin->error('Invalid ID provided, please provide a valid ID to edit.');

		}



		$g = $this->ipsclass->DB->build_and_exec_query(array('select' => '*', 'from' => 'groups', 'where' => 'g_id='.$id));

		if (!$this->ipsclass->DB->get_num_rows())

		{

			$this->ipsclass->admin->error('That group does not seem to exist.');

		}



		$up = array

		(

			'ibbookie_groups' => intval($this->ipsclass->input['ibbookie_groups']),
			'ibbookie_add_groups' => intval($this->ipsclass->input['ibbookie_add_groups']),
			'ibbookie_mod_groups' => intval($this->ipsclass->input['ibbookie_mod_groups']),
			'ibbookie_admin_groups' => intval($this->ipsclass->input['ibbookie_admin_groups']),
			'ibbookie_max_pts' => intval($this->ipsclass->input['ibbookie_max_pts']),

		);



		$this->ipsclass->DB->do_update('groups', $up, 'g_id='.$id);

		$this->_update_group_cache();



		$this->ipsclass->admin->redirect($this->ipsclass->form_code.'&code=gperms', 'Group permissions successfully updated.');

	}
	
	function _update_group_cache()

	{

		$this->ipsclass->cache['group_cache'] = array();

		$this->ipsclass->DB->build_query(array('select' => '*', 'from' => 'groups'));

		$this->ipsclass->DB->exec_query();



		while ($r = $this->ipsclass->DB->fetch_row())

		{

			$this->ipsclass->cache['group_cache'][$r['g_id']] = $r;

		}



		$this->ipsclass->update_cache(array('name' => 'group_cache', 'array' => 1, 'deletefirst' => 1));

	}

	
	
}

?>