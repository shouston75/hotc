<?php

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}
class admin_iArcade_settings_homepage extends ipsCommand
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

			default:
				$this->homepage();
			break;		 
		}
		
		/* Output to page */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}

	public function homepage()
	{
$regkey = $this->settings['iArcade-regkey'];
if ($regkey == "none") {
$regkey = "<font color='red'><b>You have not registered your copy of iArcade! Registration is fast and free, so please register your copy!</font>";
} else {

$checkkey = $this->settings['iArcade-regkey'];
$path = "http://www.iarcademod.com/lcm/reg.php?function=check&key=$checkkey";
$regkey = "<iframe src='http://www.iarcademod.com/lcm/reg.php?function=check&key=$checkkey' width='150' height='50' scrolling='no' frameborder='0'/></iframe>";
}

$i=0;
$c=0;
$this->DB->build( array( 'select' => '*',
        'from'  => array( 'iarcade_games' => 't' )
        )       );
$this->DB->execute();

while( $r = $this->DB->fetch() )
{
$i++;
$c=$c+$r['playcount'];
}

			
$this->registry->output->html .= $this->html->homepage($i,$c,$regkey); 
	}



}






