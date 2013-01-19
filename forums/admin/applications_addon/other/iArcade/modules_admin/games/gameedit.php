<?php

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_iArcade_games_gameedit extends ipsCommand
{	
	public $html;
	public $gameegistry;
	
	private $form_code;
	private $form_code_js;
	
	public function doExecute( ipsRegistry $gameegistry )
	{

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_iArcade' );

		$this->html->form_code    = $this->form_code    = 'module=games&amp;section=gameedit&amp;';
		$this->html->form_code_js = $this->form_code_js = 'module=games&amp;section=gameedit&amp;';			

		switch( $this->request['do'] )
		{
			case 'saveform':
				$this->saveForm();
			break;

			case 'delete':
				$this->delete();
			break;

			case 'deletegamefromdb':
$id = $this->request['id'];
$this->DB->delete( 'iarcade_games', "id='$id'" );
break;

			case 'deletetarfromdb':
$tarfile_name = $this->request['tarfile_name'];
$this->DB->delete( 'iarcade_tars', "tarfile_name='$tarfile_name'" );
break;

			default:
				$this->showIt();
			break;		 
		}
		
		/* Output to page */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}








	public function delete()
	{
$id = $this->request['id'];
$game = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_games', 
      'where'  => "id='$id'",
	'limit' => array( 0, 1)
	) );
$this->registry->output->html .= $this->html->delete( $outpt ); 


}











	public function showIt()
	{
$id = $this->request['id'];
$game = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_games', 
      'where'  => "id='$id'",
	'limit' => array( 0, 1)
	) );


			/* Sort out the editor */
			if( $game['description'] !== '')
			{
				if ( IPSText::getTextClass('editor')->method == 'rte' )
				{
					$sendToEditor	= IPSText::getTextClass('bbcode')->convertForRTE( $game['description'] );
				}
				else
				{
					IPSText::getTextClass( 'bbcode' )->parse_html		= 0;
					IPSText::getTextClass( 'bbcode' )->parse_nl2br		= 1;
					IPSText::getTextClass( 'bbcode' )->parse_smilies	= 1;
					IPSText::getTextClass( 'bbcode' )->parse_bbcode		= 1;
					$sendToEditor	= IPSText::getTextClass('bbcode')->preEditParse( $game['description'] );
				}
			}
			else
			{
				$sendToEditor = '';
			}
			

			$game['editorHTML']	= IPSText::getTextClass('editor')->showEditor( $sendToEditor, 'gameeditor_' . $game['id'] );
	$listHTML	.= $this->html->gameedit( $game ); 
	
	
$this->registry->output->html .= $this->html->adminWrapper( $listHTML ); 



	}

	public function saveForm()
	{

$id = $this->request['gameid'];


		$loopGamesQ = $this->DB->build( array( 
				'select'   => '*',
				'from'     => 'iarcade_games',
				'where'  => "id='$id'",
				'order'    => 'id ASC',
		) );
		$loopGamesExec = $this->DB->execute( $loopGamesQ );
		
		while( $game = $this->DB->fetch( $loopGamesExec ) )
		{
			/* BBCode */
			IPSText::getTextClass('bbcode')->parse_html		= 0;
			IPSText::getTextClass('bbcode')->parse_nl2br	= 1;
			IPSText::getTextClass('bbcode')->parse_smilies	= 1;
			IPSText::getTextClass('bbcode')->parse_bbcode	= 1;
			$dataFromEditor	= IPSText::getTextClass('editor')->processRawPost( $this->request['gameeditor_' . $game['id']] );

			$saveToDB		= IPSText::getTextClass('bbcode')->preDbParse( $dataFromEditor );
		

$newwidth = $this->request['width'];
$newheight = $this->request['height'];
$newswf = $this->request['swfname'];
$newname = $this->request['gamename'];
$game = $this->request['gameid'];


$newgamedata  = array('swf'		=> $newswf,
			'height' 		=> $newheight,
			 'width' 		=> $newwidth,
			'description' 	=> $saveToDB,
			 'name' 		=> $newname
						    );
							
print_r($newgamedata);
	  
$this->DB->update( 'iarcade_games', $newgamedata, 'id='.$this->request['gameid'] );



		}






$this->registry->adminFunctions->saveAdminLog( "Edited Game" );

//Did it work? I think it worked.
//Tell them it worked, for petes sake!
$this->registry->output->global_message = 'Your changes have been saved!';
//		$this->showIt();
	}
}

