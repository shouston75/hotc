<?php

class ibEconomyPointButton
{
	/**
	 * CONSTRUCTOR
	 **/
	function __construct()
	{
		/* Make registry objects */
		$this->registry		=  ipsRegistry::instance();
		$this->settings     =& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->member       =  $this->registry->member();
        $this->memberData   =& $this->registry->member()->fetchMemberData();		
	}
	
	public function getOutput()
	{
		return <<<HTML
			
HTML;
	}
	
	public function replaceOutput($output, $key)
	{
		if (!$this->settings['eco_general_on'] || !$this->settings['eco_pts_button_normal'] || !$this->settings['eco_pts_button_on'] || !$this->memberData['g_eco'])
		{
			return $output;
		}

		$postData 	= $this->registry->output->getTemplate('topic')->functionData['post'];
		
        if( is_array($postData) AND count($postData) )
        {
			$tag    = '<!--hook.' . $key . '-->';
			$last   = 0;
			$zeroOrTwo = ( $this->settings['eco_pts_button_decimal'] ) ? 2 : 0;
			
			foreach( $postData as $pid => $post )
			{
				$pos    = strpos( $output, $tag, $last );
				
				if( $pos )
				{
					$post['post']['author']['eco_points'] = $this->registry->getClass('class_localization')->formatNumber($post['post']['author'][ $this->settings['eco_general_pts_field'] ], $zeroOrTwo);
					$string = $this->registry->output->getTemplate('ibEconomy')->pointButton($post['post']['author']);									

					$output = substr_replace( $output, $string . $tag, $pos, strlen( $tag ) );
						
					$last   = $pos + strlen( $tag . $string );
					
					$string = "";
				}
			}
        }

		return $output;
	}
}
?>