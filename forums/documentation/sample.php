<?php

if ( ! defined( 'IN_IPB' ) )
{
    print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
    exit();
}

class custom_script
{
	var $ipsclass;
	var $xml_array;
	
	function init_install()
	{
		//-----------------------------------------
		// Increment the step value
		//-----------------------------------------
		
		$this->ipsclass->input['step']++;
		
		//-----------------------------------------
		// Do a redirect
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />Custom Script Redirect Text...." );
		
		//-----------------------------------------
		// Or, if you don't have anything to do in
		// this step, boink them past it
		//-----------------------------------------
		
		$this->ipsclass->boink_it( "{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;st={$this->ipsclass->input['st']}" );
	}
	
	function init_uninstall()
	{
		//-----------------------------------------
		// Increment the step value
		//-----------------------------------------
		
		$this->ipsclass->input['step']++;
		
		//-----------------------------------------
		// Do a redirect
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;un=1&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />Custom Script Redirect Text...." );
		
		//-----------------------------------------
		// Or, if you don't have anything to do in
		// this step, boink them past it
		//-----------------------------------------
		
		$this->ipsclass->boink_it( "{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;un=1&amp;st={$this->ipsclass->input['st']}" );
	}
	
	function install()
	{
		//-----------------------------------------
		// Increment the step value
		//-----------------------------------------
		
		$this->ipsclass->input['step']++;
		
		//-----------------------------------------
		// Do a redirect
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />Custom Script Redirect Text...." );
		
		//-----------------------------------------
		// Or, if you don't have anything to do in
		// this step, boink them past it
		//-----------------------------------------
		
		$this->ipsclass->boink_it( "{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;st={$this->ipsclass->input['st']}" );
	}
	
	function uninstall()
	{
		//-----------------------------------------
		// Increment the step value
		//-----------------------------------------
		
		$this->ipsclass->input['step']++;
		
		//-----------------------------------------
		// Do a redirect
		//-----------------------------------------
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;un=1&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />Custom Script Redirect Text...." );
		
		//-----------------------------------------
		// Or, if you don't have anything to do in
		// this step, boink them past it
		//-----------------------------------------
		
		$this->ipsclass->boink_it( "{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;un=1&amp;st={$this->ipsclass->input['st']}" );
	}
}

?>