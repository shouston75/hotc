<?php
class boardblanksidebar
{
	public $registry;
	public $member;
	public $parser;
	
	public function __construct()
	{
		$this->registry = ipsRegistry::instance();
		$this->member	= $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->settings   =& $this->registry->fetchSettings();
	}
	
	public function getOutput()
	{
		$this->parser = IPSText::getTextClass( 'bbcode' );
		
		$this->parser->parse_smilies = 1;
		$this->parser->parse_html    = 1;
		$this->parser->parse_bbcode  = 1;
		
		/* check for secondary perms */
		$secondary = explode( ',', trim($this->memberData['mgroup_others'],",") );
			
		/* run through each hook */
		if( $this->settings['blank_on1'] == 1 )
		{
			if( $this->settings['blank_group1'] != 'unset' )
			{
				/* hooks enabled do user check */
				$match1 =0;
				$groups1 = explode( ',', $this->settings['blank_group1'] );
				foreach( $groups1 as $k )
				{
					if( $k == $this->memberData['member_group_id'] )
					{
						$match1 = 1;
						break;
					}
					elseif( $secondary[0] != '' && in_array($k, $secondary ) )
					{
						$match1 = 1;
						break;
					}
				}
			}
				
			if( $match1 == 1 || $this->settings['blank_group1'] == 'unset' )
			{
				$string = $this->parser->preDbParse( $this->settings['blank_content'] );
				$string = $this->parser->preDisplayParse( $string );
				$return   = $this->registry->output->getTemplate( 'boards' )->hookblanksidebar( $string,$this->settings['blank_title'] );
			}
		}
		if( $this->settings['blank_on2'] == 1 )
		{
			/* run through each hook */
			$match2 =0;
			$groups2 = explode( ',', $this->settings['blank_group2'] );
			foreach( $groups2 as $k )
			{
				if( $k == $this->memberData['member_group_id'] )
				{
					$match2 = 1;
					break;
				}
				elseif( $secondary[0] != '' && in_array($k, $secondary ) )
				{
					$match2 = 1;
					break;
				}
			}
					
			/* hooks enabled do user check */
			if( $match2 == 1 )
			{
				$string = $this->parser->preDbParse( $this->settings['blank_content2'] );
				$string = $this->parser->preDisplayParse( $string );
				$return .= $this->registry->output->getTemplate( 'boards' )->hookblanksidebar( $string,$this->settings['blank_title2'] );
			}
		}
		if( $this->settings['blank_on3'] == 1 )
		{
			/* hooks enabled do user check */
			$match3 =0;
			$groups3 = explode( ',', $this->settings['blank_group3'] );
			foreach( $groups3 as $k )
			{
				if( $k == $this->memberData['member_group_id'] )
				{
					$match3 = 1;
					break;
				}
				elseif( $secondary[0] != '' && in_array($k, $secondary ) )
				{
					$match3 = 1;
					break;
				}
			}
			
			/* hooks enabled do user check */
			if( $match3 == 1 )
			{
				$string = $this->parser->preDbParse( $this->settings['blank_content3'] );
				$string = $this->parser->preDisplayParse( $string );
				$return .= $this->registry->output->getTemplate( 'boards' )->hookblanksidebar( $string,$this->settings['blank_title3'] );
			}
		}
		
		return $return;
			
	}
}