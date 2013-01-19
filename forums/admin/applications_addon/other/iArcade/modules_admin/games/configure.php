<?php

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_iArcade_games_configure extends ipsCommand
{	
	public $html;
	public $registry;
	
	private $form_code;
	private $form_code_js;
	
	public function doExecute( ipsRegistry $registry )
	{

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_iArcade' );

			

		switch( $this->request['do'] )
		{
			case 'saveform':
				$this->saveForm();
			break;

			default:
				$this->listAll();
			break;		 
		}
		
		/* Output to page */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}

	public function listAll()
	{

		$this->html->form_code    = $this->form_code    = 'module=games&section=configure&do=saveform&';

		//Get the games
$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_games' => 't' ),
        'order' => 'name ASC',
        'limit' => array( 0, 99999 )

        )       );
$this->DB->execute();

while( $r = $this->DB->fetch() )
{
        $info = $r;



		
$this->registry->output->html .= $this->html->managewrap( $info ); 
	}

}


	public function saveForm()
	{
			
/* =====
DEPRECIATED CODE

$newwidth = $this->request['width'];
$newheight = $this->request['height'];
$newswf = $this->request['swfname'];
$newname = $this->request['name'];
$game = $this->request['gameid'];


$newgamedata  = array('swf'		=> $newswf,
							 'height' 		=> $newheight,
							 'width' 		=> $newhwidth,
							 'name' 		=> $newname
						    );
							
				  
$this->DB->update( 'iarcade_games', $newgamedata, 'id='.$this->request['gameid'] );

$this->registry->adminFunctions->saveAdminLog( "Edited Games" );
		$this->registry->output->global_message = 'Your changes have been saved!';
		
		$this->listAll();

=============
*/


	}
}





