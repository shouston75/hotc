<?php

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_iArcade_games_info extends ipsCommand
{	
	public $html;
	public $registry;
	
	private $form_code;
	private $form_code_js;
	
	public function doExecute( ipsRegistry $registry )
	{

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_iArcade' );

		$this->html->form_code    = $this->form_code    = 'module=games&amp;section=info&amp;';
		$this->html->form_code_js = $this->form_code_js = 'module=games&amp;section=info&amp;';			

		switch( $this->request['do'] )
		{
			case 'saveform':
				$this->saveForm();
			break;

			case 'add':
				$this->addForm();
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
		$listAdmins = $this->DB->build( array( 
				'select'   => '*',
				'from'     => 'iarcade_games',
				'order'    => 'id ASC',
					) );
		$listExec = $this->DB->execute( $listAdmins );
		
		$listHTML = '';

		while( $r = $this->DB->fetch( $listExec ) )
		{
			/* Sort out the editor */
			if( $r['description'] !== '')
			{
				if ( IPSText::getTextClass('editor')->method == 'rte' )
				{
					$sendToEditor	= IPSText::getTextClass('bbcode')->convertForRTE( $r['description'] );
				}
				else
				{
					IPSText::getTextClass( 'bbcode' )->parse_html		= 0;
					IPSText::getTextClass( 'bbcode' )->parse_nl2br		= 1;
					IPSText::getTextClass( 'bbcode' )->parse_smilies	= 1;
					IPSText::getTextClass( 'bbcode' )->parse_bbcode		= 1;
					$sendToEditor	= IPSText::getTextClass('bbcode')->preEditParse( $r['description'] );
				}
			}
			else
			{
				$sendToEditor = '';
			}
			

			$r['editorHTML']	= IPSText::getTextClass('editor')->showEditor( $sendToEditor, 'gameeditor_' . $r['id'] );
			$listHTML			.= $this->html->adminRow( $r ); 



		}
		
$this->registry->output->html .= $this->html->adminWrapper( $listHTML ); 
	}

	public function saveForm()
	{
		$loopGamesQ = $this->DB->build( array( 
				'select'   => '*',
				'from'     => 'iarcade_games',
				'order'    => 'id ASC',
		) );
		$loopGamesExec = $this->DB->execute( $loopGamesQ );
		
		while( $r = $this->DB->fetch( $loopGamesExec ) )
		{
			/* BBCode */
			IPSText::getTextClass('bbcode')->parse_html		= 0;
			IPSText::getTextClass('bbcode')->parse_nl2br	= 1;
			IPSText::getTextClass('bbcode')->parse_smilies	= 1;
			IPSText::getTextClass('bbcode')->parse_bbcode	= 1;
			$dataFromEditor	= IPSText::getTextClass('editor')->processRawPost( $this->request['gameeditor_' . $r['id']] );

			$saveToDB		= IPSText::getTextClass('bbcode')->preDbParse( $dataFromEditor );
						
			/* Check if admin has a row in the table */
			$rowCheckQuery = $this->DB->buildAndFetch( array( 
				'select' => 'description',
				'from'   => 'iarcade_games',
				'where'  => 'id = ' . $r['id'],
			) );
			$rowCheckExec = $this->DB->execute( $rowCheckQuery );
						
			if( $this->DB->getTotalRows( $rowCheckExec ) )
			{			
				$this->DB->update( 'iarcade_games', array( 'description' => $saveToDB ), 'id = ' . $r['id'] );
			}
			else
			{


/*
=============FOR USE IN A FUTURE FUNCTION. WOO.====
				$this->DB->insert( 'iarcade_games',
					array(
						'id' 		=> $r['member_id'],
						'text'	=> $saveToDB
					)
				);
=================END FUTURE===============
*/


			}
		}

		$this->registry->adminFunctions->saveAdminLog( "Edited Game Descriptions" );

//Did it work? I think it worked.
//Tell them it worked, for petes sake!
		$this->registry->output->global_message = 'Your changes have been saved!';
		$this->listAll();
	}
}
