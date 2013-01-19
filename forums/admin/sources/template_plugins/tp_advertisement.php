<?php
/**
 * @file		tp_advertisement.php		Template plugin for advertisements
 *
 * $Copyright: $
 * $License: $
 * $Author: ips_terabyte $
 * $LastChangedDate: 2011-05-05 07:03:47 -0400 (Thu, 05 May 2011) $
 * $Revision: 8644 $
 * @since 		16th December 2010
 */

/**
 *
 * @class	tp_advertisement
 * @brief	Template plugin for advertisements
 *
 */
class tp_advertisement extends output implements interfaceTemplatePlugins
{
	/**
	 * Prevent our main destructor being called by this class
	 *
	 * @return	@e void
	 */
	public function __destruct()
	{
	}
	
	/**
	 * Run the plug-in
	 *
	 * @param	string	$data		The initial data from the tag
	 * @param	array	$options	Array of options
	 * @return	@e string			Processed HTML
	 */
	public function runPlugin( $data, $options )
	{
		if ( is_numeric( $data ) )
		{
			$return = '$this->registry->getClass(\'IPSAdCode\')->getAdById( ' . $data . ' )';
		}
		else
		{
			$return = '$this->registry->getClass(\'IPSAdCode\')->getAdCode( ' . $data . ' )';
		}
		
		return '" . ' . $return . ' . "';
	}
	
	/**
	 * Return information about this modifier.
	 *
	 * @return	@e array			Plugin Information
	 */
	public function getPluginInfo()
	{
		return array( 'name'    => 'advertisement',
					  'author'  => 'IPS, Inc.',
					  'usage'   => '{parse advertisement="ad_code_global_header"}',
					  'options' => array() );
	}
}