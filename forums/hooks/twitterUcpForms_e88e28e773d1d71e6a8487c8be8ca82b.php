<?php

class twitterUcpForms extends public_core_usercp_manualResolver  
{
	public function doExecute( ipsRegistry $registry )
	{
		if ( $this->request['area'] == 'settings' AND $this->request['tab'] == 'core' AND $this->request['do'] == 'save' )
		{
			if ( isset( $this->request['twitter'] ) )
			{
				if ( $this->request['resetOAuth'] )
				{
					IPSMember::save( $this->memberData['member_id'], array( 'core' => 
																		array( 	'oauth_state'				 => NULL,
																				'oauth_access_token' 		 => NULL, 
																				'oauth_access_token_secret'	 => NULL,
																				'oauth_request_token'		 => NULL,
																				'oauth_request_token_secret' => NULL,
																				) ) );
				}
			}
		}
		
		parent::doExecute( $registry );
	}
}

?>