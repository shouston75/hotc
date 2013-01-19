<?php

/*
+------------------------------------------------
|
|	iRC-Component
|	===========================
|	@author	: Martin Aronsen
|	@web	: http://invisionmodding.com
|	@email 	: m@rtin.no
|	@version: 	: 1.1
|	===========================
|	IPB comp.: v2.3.x & v2.2.x
|	===========================
|
+------------------------------------------------
*/


if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class component_public
{
	
	var $ipsclass;
	var $output;
	var $nav = array ();
	var $irc = array ();
	
	
	function run_component()
	{
		
		
		//---------------------------------------
		// Is the shit turn'd on yet?
		//---------------------------------------

		if ( ! $this->ipsclass->vars['ma23-irc_enable'] )
		{
				$this->ipsclass->Error ( array ( MSG => 'ma23-irc_not_enabled' ) );
		}
		
		//---------------------------------------
		// Yes it was, lets give em something
		// beautiful to look at
		//---------------------------------------
		
		$this->ipsclass->load_template( 'skin_irc' );
		
		
		$this->pagetitle = $this->ipsclass->lang['ma23-irc_title'];
		$this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=irc'>{$this->pagetitle}</a>";		
		

		$this->show_applet();
		
		$this->ipsclass->print->add_output ( $this->output );
		$this->ipsclass->print->do_output ( array( 'TITLE' => "{$this->ipsclass->vars['board_name']} - {$this->pagetitle}", 'JS' => 0, 'NAV' => $this->nav ) );
		
		
	}
	
	function show_applet()
	{
		
		//---------------------------------------
		// Give the members some nickzZzZ
		//---------------------------------------
		if ( strstr( ",{$this->ipsclass->vars['ma23-irc_allowed_group']},", ",{$this->ipsclass->member['mgroup']}," ) )
		{
			if ( ! $this->ipsclass->member['id'])
			{
				$this->irc['prinick'] 	= $this->ipsclass->vars['guest_name_pre'] . rand ( 100, 999);
				$this->irc['altnick']	= $this->ipsclass->vars['guest_name_pre'] . rand ( 100, 999);
				$this->irc['userid']	= $this->irc['prinick'];
			}
			else
			{
				$this->irc['prinick'] 	= $this->ipsclass->member['members_display_name'];
				$this->irc['altnick']	= $this->ipsclass->member['members_display_name'] . rand ( 100, 999 );
				$this->irc['userid']	= $this->ipsclass->member['id'];
			}
		}
		else
		{			
			$this->ipsclass->Error ( array ( MSG => 'no_permission') );
		}
		
		// Default applet width
		$this->irc['applet_width'] = "100%";
		

		// Got rules?
		if( !empty( $this->ipsclass->vars['ma23-irc_rules'] ) )
		{
			$this->irc['rules'] 		= $this->ipsclass->vars['ma23-irc_rules'];
			$this->irc['rule_pos']		= $this->ipsclass->vars['ma23-irc_rules_pos'];
			$this->irc['applet_width'] 	= "75%";
		}
		
		// Shouldn't be any reason for you to change this, so I hardcode'ed it.
		$this->irc['cabfiles'] = 'irc.cab,securedirc.cab,pixx.cab';
		
				
		$this->output .= $this->ipsclass->compiled_templates['skin_irc']->show_applet($this->irc);
		
		//---------------------------------------
		// Array of required parameters
		//---------------------------------------
		
		$param = array(
			'highlight' => 'true',
			'asl' => 'true',
			'style:highlightlinks' => 'true',
			'pixx:highlightnick' => 'true',
			'pixx:styleselector' => 'false',
			'pixx:setfontonstyle' => 'true',
			'pixx:displayentertexthere' => 'false',
			'pixx:timestamp' => 'true',
			'pixx:mouseurlopen' => '1 2',
			'pixx:mousechanneljoin' => '1 2',
			'pixx:configurepopup' => 'true',
			'pixx:popupmenustring1' => 'Whois',
			'pixx:popupmenustring2' => 'Query',
			'pixx:popupmenustring3' => 'Ban',
			'pixx:popupmenustring4' => 'Kick + Ban',
			'pixx:popupmenustring5' => '--',
			'pixx:popupmenustring6' => 'Op',
			'pixx:popupmenustring7' => 'DeOp',
			'pixx:popupmenustring8' => 'HalfOp',
			'pixx:popupmenustring9' => 'DeHalfOp',
			'pixx:popupmenustring10' => 'Voice',
			'pixx:popupmenustring11' => 'DeVoice',
			'pixx:popupmenustring12' => '--',
			'pixx:popupmenustring13' => 'Ping',
			'pixx:popupmenustring14' => 'Version',
			'pixx:popupmenustring15' => 'Time',
			'pixx:popupmenustring16' => 'Finger',
			'pixx:popupmenustring17' => '--',
			'pixx:popupmenustring18' => 'DCC Send',
			'pixx:popupmenustring19' => 'DCC Chat',
			'pixx:popupmenucommand1_1' => '/Whois %1',
			'pixx:popupmenucommand2_1' => '/Query %1',
			'pixx:popupmenucommand3_1' => '/mode %2 -o %1',
			'pixx:popupmenucommand3_2' => '/mode %2 +b %1',
			'pixx:popupmenucommand4_1' => '/mode %2 -o %1',
			'pixx:popupmenucommand4_2' => '/mode %2 +b %1',
			'pixx:popupmenucommand4_3' => '/kick %2 %1',
			'pixx:popupmenucommand6_1' => '/mode %2 +o %1',
			'pixx:popupmenucommand7_1' => '/mode %2 -o %1',
			'pixx:popupmenucommand8_1' => '/mode %2 +h %1',
			'pixx:popupmenucommand9_1' => '/mode %2 -h %1',
			'pixx:popupmenucommand10_1' => '/mode %2 +v %1',
			'pixx:popupmenucommand11_1' => '/mode %2 -v %1',
			'pixx:popupmenucommand13_1' => '/CTCP PING %1',
			'pixx:popupmenucommand14_1' => '/CTCP VERSION %1',
			'pixx:popupmenucommand15_1' => '/CTCP TIME %1',
			'pixx:popupmenucommand16_1' => '/CTCP FINGER %1',
			'pixx:popupmenucommand18_1' => '/DCC SEND %1',
			'pixx:popupmenucommand19_1' => '/DCC CHAT %1'
		);
		
		foreach ($param as $k => $v)
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_irc']->other_param ( $k, $v);
			unset ( $k );
			unset ( $v );
		}

		
		
		//---------------------------------------
		// Allowing smileys?
		//---------------------------------------
		
		if ( $this->ipsclass->vars['ma23-irc_smilies'] === 1 )
		{
			$smiley = array(
				array(		"sourire.gif"		,		":)"	,	":-)"		),
				array(		"content.gif"		,		":D"	, 	":-D"		),
				array(		"OH-2.gif"			,		":-O"					),
				array(		"OH-1.gif"			,		":o"					),
				array(		"langue.gif"		,		":P"	,	":-P"		),
				array(		"clin-oeuil.gif"	,		";)"	,	";-)"		),
				array(		"triste.gif"		,		":("	,	":-("		),
				array(		"OH-3.gif"			,		":|"	,	":-|"		),
				array(		"pleure.gif"		,		":'("					),
				array(		"rouge.gif"			,		":$"	, 	":-$"		),
				array(		"cool.gif"			,		"(H)"	,	"(h)"		),
				array(		"enerve1.gif"		,		":-@"					),
				array(		"enerve2.gif"		,		":@"					),
				array(		"roll-eyes.gif"		,		":s"	,	":-S"		),
			);
			
			$x=1;
			$imgpath    = 'img/';
			for ($i=0; isset($smiley[$i][0]); $i++)
			{
				for ($k=1; isset($smiley[$i][$k]); $k++)
				{
					$img 			= $imgpath.$smiley[$i][0];
					$smiley[$i][$k] 	= stripslashes($smiley[$i][$k]);
					$key				= 'style:smiley'.$x;
					$value			= $smiley[$i][$k].' '.$img;
					$this->output      .= $this->ipsclass->compiled_templates['skin_irc']->other_param ( $key , $value);
					$x++;
				}
			}
		}
		
		// My work is done.
		$this->output .= $this->ipsclass->compiled_templates['skin_irc']->end_applet( $this->irc );

	}
}

?>