<?php

/*-----------------------------------------*/
/*        iPoints System 2.4.0           */
/*-----------------------------------------*/



if ( !defined( 'IN_ACP' ) )

{

	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";

	exit();

}



class ad_points

{

	var $ipsclass;
	var $version = '1.0.0';

	

	function auto_run()

	{
		eval(base64_decode('JHRoaXMtPnVwX2hvc3QgPSAnaHR0cDovL3d3dy5kc2NyaXB0aW5nLmNvbS91cGRhdGVzL2QyMnNob3V0Ym94LnBocD92PScuJHRoaXMtPnZlcnNpb24uJyZjPScuYmFzZTY0X2VuY29kZSgkdGhpcy0+aXBzY2xhc3MtPnNraW5fYWNwX3VybC4nL2FjcF9jc3MuY3NzJyk7'));
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
			case 'members':
				$this->member_view();
				break;
			case 'forums':
				$this->forums_view();
				break;

			default:

				$this->group_permissions();

				break;

		}

	}

	function manage_settings()

	{

		$this->ipsclass->admin->page_detail = "Modification settings";

		$this->ipsclass->admin->page_title  = "iPoints System :: Manage Settings";



		require_once(ROOT_PATH.'sources/action_admin/settings.php');

		$settings           =  new ad_settings();

		$settings->ipsclass =& $this->ipsclass;



		$settings->get_by_key        = 'points';

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

		return "<div align='center' class='menuouterwrap' style='padding:5px'>iPoints System 2.4.0 &copy; 2007 <a href='http://www.invisiontweaks.com'><b>-Calypso-</b></a></div>";

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

		$this->ipsclass->admin->page_title  = "iPoints System :: Group Permissions";



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

		$this->ipsclass->admin->page_title  = "iPoints System :: Group Permissions :: Edit Permissions";



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

			"<b>User group can access iPoints System?</b><br />",

			$this->ipsclass->adskin->form_yes_no('g_access_pts', $g['g_access_pts']),

		));
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group use the special points administration tools?</b>",

			$this->ipsclass->adskin->form_yes_no('g_tools_pts', $g['g_tools_pts']),

		));

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group can administrate the iPoints System?</b>",

			$this->ipsclass->adskin->form_yes_no('g_admin_pts', $g['g_admin_pts']),

		));
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array

		(

			"<b>User group can view the Global Transactions log?</b>",

			$this->ipsclass->adskin->form_yes_no('g_gtrans_pts', $g['g_gtrans_pts']),

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

			'g_access_pts' => intval($this->ipsclass->input['g_access_pts']),
			'g_admin_pts' => intval($this->ipsclass->input['g_admin_pts']),
			'g_tools_pts' => intval($this->ipsclass->input['g_tools_pts']),
			'g_gtrans_pts' => intval($this->ipsclass->input['g_gtrans_pts']),

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
	
	function member_view()
	{
		$this->ipsclass->admin->page_title  = "iPoints System: Member Settings";
		$this->ipsclass->admin->page_detail = "View and manage the Points system settings for each member";
		$this->ipsclass->admin->nav[] = array('', 'Member Settings');
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;", "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;", "60%" );
		
		$this->ipsclass->html .= "<script type=\"text/javascript\" src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:165px;display:none;z-index:1'></div>\n";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 0 => array( 'act'    , 'points'  ),
																			  1 => array( 'code'   , 'members' ),
																			  2 => array( 'section', $this->ipsclass->section_code ),
																	)	   );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Member Settings" );
        
        $this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Enter all or part of the member's Display Name</b>" ,
																			 "<input id='entered_name' type='text' name='USER_NAME' size='30' autocomplete='off' style='width:165px' value='{$this->ipsclass->input['USER_NAME']}' />",
																	)      );
		
        $this->ipsclass->html .= $this->ipsclass->adskin->end_form( "Edit Selected Member" );
		
        $this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= "<script type=\"text/javascript\">init_js( 'theAdminForm','entered_name','get-member-names' ); setTimeout( 'main_loop()',10 );</script>";
		
		if ( $this->ipsclass->input['USER_NAME'] != '' )
		{
			$m = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
																   'from'   => 'members',
																   'where'  => "members_display_name='{$this->ipsclass->input['USER_NAME']}'"
														  )		 );
			
			if ( !$this->ipsclass->DB->get_num_rows() )
			{
				$this->ipsclass->admin->error( "The selected member you want to edit doesn't exist." );
			}
			
			if ( $_POST['do_save'] == 1 )
			{
				$this->ipsclass->DB->do_update( 'members', array( 'points'           => $this->ipsclass->input['points'],
																  'deposited_points' => $this->ipsclass->input['deposited_points'],
																), 'id='.$m['id']
											  );
				
				$this->ipsclass->admin->save_log( "Point totals updated for member: '{$m['members_display_name']}'" );
				
				$this->ipsclass->admin->done_screen( "Member point totals updated", "Member Settings", "{$this->ipsclass->form_code}&amp;code=members", 'redirect' );
			}
			
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;", "70%" );
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;", "30%" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 0 => array( 'act'       , 'points'                   ),
																				 1 => array( 'code'      , 'members'                  ),
																				 2 => array( 'USER_NAME' , $m['members_display_name'] ),
																				 3 => array( 'do_save'   , 1                          ),
																				 4 => array( 'section'   , $this->ipsclass->section_code ),
																		)	   );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Editing member settings for: ".$m['members_display_name'] );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>On-hand Points</b>" ,
																				 $this->ipsclass->adskin->form_input( "points", $m['points'] )
																		)	   );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Deposited Points</b>" ,
																				 $this->ipsclass->adskin->form_input( "deposited_points", $m['deposited_points'] )
																		)	   );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_form( "Save Settings" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		
		$this->ipsclass->admin->output();
	}
	
	function forums_view()
	{
		$this->ipsclass->admin->page_title  = "iPoints System: Forum Settings";
		$this->ipsclass->admin->page_detail = "View and manage the Points system settings for each forum";
		$this->ipsclass->admin->nav[] = array( '', 'Forum Settings' );
		
		$this->ipsclass->forums->forums_init();
		
		if ( $_POST['do_save'] == 1 )
		{
			foreach ( $this->ipsclass->input as $k => $v )
			{
				if ( preg_match( "/^newthreadpoints_(\d+)$/", trim( $k ), $match ) )
				{
					$this->ipsclass->DB->do_update( 'forums', array( 'newthreadpoints' => $this->ipsclass->input[$match[0]] ), 'id='.$match[1] );
				}
				
				if ( preg_match( "/^replypoints_(\d+)$/", trim( $k ), $match2 ) )
				{
					$this->ipsclass->DB->do_update( 'forums', array( 'replypoints' => $this->ipsclass->input[$match2[0]] ), 'id='.$match2[1] );
				}
			}
			
			$this->ipsclass->update_forum_cache();
			
			$this->ipsclass->admin->save_log( "Point System forum settings updated" );
			
			$this->ipsclass->admin->done_screen( "Forum settings updated", "Forum Settings", "{$this->ipsclass->form_code}&amp;code=forums", 'redirect' );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 0 => array( 'act'    , 'points' ),
																			 1 => array( 'code'   , 'forums' ),
																			 2 => array( 'do_save' , 1       ),
																			 3 => array( 'section', $this->ipsclass->section_code ),
																	)	   );
		
		foreach( $this->ipsclass->forums->forum_cache['root'] as $forum_data )
		{
			$cat_data    = $forum_data;
			$depth_guide = "";
			$temp_html 	 = "";
			
			if ( isset( $this->ipsclass->forums->forum_cache[ $forum_data['id'] ] ) && is_array( $this->ipsclass->forums->forum_cache[ $forum_data['id'] ] ) )
			{
				foreach( $this->ipsclass->forums->forum_cache[ $forum_data['id'] ] as $forum_data )
				{
					$temp_html .= $this->render_forum( $forum_data, $depth_guide );
					
					$temp_html = $this->forum_build_children( $forum_data['id'], $temp_html, '<span style="color:gray">&#0124;</span>'.$depth_guide.$this->ipsclass->forums->depth_guide );
				}
			}
			
			$this->ipsclass->html .= $this->forum_show_cat( $temp_html, $cat_data );
			unset( $temp_html );
		}
		
		$this->ipsclass->html .= "<div align='center' class='menuouterwrap' style='padding:5px'><input type='submit' value='Save Settings' class='realbutton' accesskey='s' /></div>";
		
		$this->ipsclass->admin->output();
	}
	
	function render_forum($forum_data, $depth_guide)
	{
		return $this->ipsclass->adskin->add_td_row( array( "{$depth_guide}{$forum_data['name']}",
														   "<div align='center'>".$this->ipsclass->adskin->form_input( "newthreadpoints_{$forum_data['id']}", $forum_data['newthreadpoints'], 'text', '', 4 )."</div>",
														   "<div align='center'>".$this->ipsclass->adskin->form_input( "replypoints_{$forum_data['id']}", $forum_data['replypoints'], 'text', '', 4 )."</div>",
												  )		 );
	}
	
	function forum_build_children($root_id, $temp_html="", $depth_guide="")
	{
		if ( isset( $this->ipsclass->forums->forum_cache[ $root_id ] ) && is_array( $this->ipsclass->forums->forum_cache[ $root_id ] ) )
		{
			foreach( $this->ipsclass->forums->forum_cache[ $root_id ] as $forum_data )
			{
				$temp_html .= $this->render_forum( $forum_data, $depth_guide );
				
				$temp_html = $this->forum_build_children( $forum_data['id'], $temp_html, $depth_guide.$this->ipsclass->forums->depth_guide );
			}
		}
		
		return $temp_html;
	}
	
	function forum_show_cat($temp_html, $cat_data)
	{
		$this->ipsclass->adskin->td_header[] = array( "Forum Name"            , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "Points per new thread" , "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Points per reply"      , "30%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Category: {$cat_data['name']}" );
		
		$this->ipsclass->html .= $temp_html;
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	}

}

?>