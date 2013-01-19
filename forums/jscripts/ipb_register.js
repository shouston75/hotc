//------------------------------------------
// Invision Power Board v2.1
// Register JS File
// (c) 2005 Invision Power Services, Inc.
//
// http://www.invisionboard.com
//------------------------------------------

var user_display_name_max_length = 26;
var reg_got_dname    = 0;
var reg_oktogo       = 0;
var reg_field_ids    = new Array();
var reg_img_ids      = new Array();
var reg_box_ids      = new Array();
var reg_msg_ids      = new Array();

var ucp_dname_illegal_chars = new Array( '[', ']', '|', ',', ';', '$', '"', '<', '>', '\\' );
var ucp_dname_illegal_regex = '';

for ( var i in ucp_dname_illegal_chars )
{
	ucp_dname_illegal_regex += '\\' + ucp_dname_illegal_chars[i];
}

var error_email  = 0;

var in_member_id = 0;

RegExp.escape = function(text)
{
	if (!arguments.callee.sRE)
	{
	   	var specials = [ '/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\', '$' ];
	    	
	   	arguments.callee.sRE = new RegExp( '(\\' + specials.join('|\\') + ')', 'g' );
	}
	  
	return text.replace(arguments.callee.sRE, '\\$1');
};

/*-------------------------------------------------------------------------*/
// INIT Reg form
/*-------------------------------------------------------------------------*/

function init_reg_form( got_dname, register_method )
{
	//------------------------------------------
	// INIT objects
	//------------------------------------------
	
	reg_got_dname = got_dname;
	
	//------------------------------------------
	// Fields...
	//------------------------------------------
	
	if( register_method == 'username' )
	{
		reg_field_ids['name']             = document.getElementById( 'reg-name' );
	}

	reg_field_ids['password']         = document.getElementById( 'reg-password' );
	reg_field_ids['password-check']   = document.getElementById( 'reg-password-check' );
	reg_field_ids['emailaddress']     = document.getElementById( 'reg-emailaddress' );
	reg_field_ids['emailaddress-two'] = document.getElementById( 'reg-emailaddress-two' );
	
	//------------------------------------------
	// Images
	//------------------------------------------
	
	if( register_method == 'username' )
	{
		reg_img_ids['name']         = document.getElementById( 'img-name' );
	}

	reg_img_ids['password']     = document.getElementById( 'img-password' );
	reg_img_ids['emailaddress'] = document.getElementById( 'img-emailaddress' );
	
	//------------------------------------------
	// Boxes
	//------------------------------------------
	
	if( register_method == 'username' )
	{
		reg_box_ids['name']         = document.getElementById( 'box-name' );
	}

	reg_box_ids['password']     = document.getElementById( 'box-password' );
	reg_box_ids['emailaddress'] = document.getElementById( 'box-emailaddress' );
	
	//------------------------------------------
	// Messages
	//------------------------------------------
	
	if ( register_method == 'username' )
	{
		reg_msg_ids['name']         = document.getElementById( 'msg-name' );
	}

	reg_msg_ids['password']     = document.getElementById( 'msg-password' );
	reg_msg_ids['emailaddress'] = document.getElementById( 'msg-emailaddress' );
	
	//------------------------------------------
	// Set up onblur
	//------------------------------------------
	
	if( register_method == 'username' )
	{
		reg_field_ids['name'].onblur             = check_user_name;
	}

	reg_field_ids['password-check'].onblur   = check_passwords;
	reg_field_ids['emailaddress'].onblur     = check_email_addresses_one;
	reg_field_ids['emailaddress-two'].onblur = check_email_addresses;
	
	//------------------------------------------
	// Already got error messages?
	//------------------------------------------
	
	if ( register_method == 'username' )
	{
		if ( ! reg_msg_ids['name'].innerHTML )
		{
			reg_box_ids['name'].style.display = 'none';
		}
	}
	
	if ( ! reg_msg_ids['password'].innerHTML )
	{
		reg_box_ids['password'].style.display = 'none';
	}
	
	if ( ! reg_msg_ids['emailaddress'].innerHTML )
	{
		reg_box_ids['emailaddress'].style.display = 'none';
	}
	
	//------------------------------------------
	// Display name. ..
	//------------------------------------------
	
	if ( reg_got_dname )
	{
		reg_field_ids['dname'] = document.getElementById( 'reg-members-display-name' );
		reg_box_ids['dname']   = document.getElementById( 'box-dname' );
		reg_msg_ids['dname']   = document.getElementById( 'msg-dname' );
		reg_img_ids['dname']   = document.getElementById( 'img-members-display-name' );
		
		if ( ! reg_msg_ids['dname'].innerHTML )
		{
			reg_box_ids['dname'].style.display = 'none';
		}
	
		reg_field_ids['dname'].onblur = check_display_name;
	}
};

/*-------------------------------------------------------------------------*/
// INIT Reg form
/*-------------------------------------------------------------------------*/

function init_complete_login_form()
{
	//------------------------------------------
	// INIT objects
	//------------------------------------------
	
	reg_got_dname = got_dname;
	
	if ( ! reg_email_ok )
	{
		//------------------------------------------
		// Fields...
		//------------------------------------------

		reg_field_ids['emailaddress']     = document.getElementById( 'reg-emailaddress' );
		reg_field_ids['emailaddress-two'] = document.getElementById( 'reg-emailaddress-two' );
	
		//------------------------------------------
		// Images
		//------------------------------------------
	
		reg_img_ids['emailaddress'] = document.getElementById( 'img-emailaddress' );
	
		//------------------------------------------
		// Boxes
		//------------------------------------------
	
		reg_box_ids['emailaddress'] = document.getElementById( 'box-emailaddress' );
	
		//------------------------------------------
		// Messages
		//------------------------------------------
	
		reg_msg_ids['emailaddress'] = document.getElementById( 'msg-emailaddress' );
	
		//------------------------------------------
		// Set up onblur
		//------------------------------------------
	
		reg_field_ids['emailaddress'].onblur     = check_email_addresses_one;
		reg_field_ids['emailaddress-two'].onblur = check_email_addresses;

		//------------------------------------------
		// Already got error messages?
		//------------------------------------------
	
		if ( ! reg_msg_ids['emailaddress'].innerHTML )
		{
			reg_box_ids['emailaddress'].style.display = 'none';
		}
	}
	
	//------------------------------------------
	// Display name...
	//------------------------------------------
	
	if ( reg_got_dname )
	{
		reg_field_ids['dname'] = document.getElementById( 'reg-members-display-name' );
		reg_box_ids['dname']   = document.getElementById( 'box-dname' );
		reg_msg_ids['dname']   = document.getElementById( 'msg-dname' );
		reg_img_ids['dname']   = document.getElementById( 'img-members-display-name' );
		
		if ( ! reg_msg_ids['dname'].innerHTML )
		{
			reg_box_ids['dname'].style.display = 'none';
		}
	
		reg_field_ids['dname'].onblur = check_display_name;
	}
	
	in_member_id = member_id;
};

/*-------------------------------------------------------------------------*/
// Check email address validity
/*-------------------------------------------------------------------------*/

function check_email_addresses_one( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//----------------------------------
	// INIT
	//----------------------------------
	
	var error_found = '';
	
	//----------------------------------
	// Ajax: check for existing email address
	//----------------------------------
	
	if ( use_enhanced_js && reg_field_ids['emailaddress'].value )
	{
		var url = ipb_var_base_url+'act=xmlout&do=check-email-address&email='+encodeURIComponent( reg_field_ids['emailaddress'].value );
	
		/*--------------------------------------------*/
		// Main function to do on request
		// Must be defined first!!
		/*--------------------------------------------*/
		
		do_request_function = function()
		{
			//----------------------------------
			// Ignore unless we're ready to go
			//----------------------------------
			
			if ( ! xmlobj.readystate_ready_and_ok() )
			{
				// Could do a little loading graphic here?
				return;
			}
			
			//----------------------------------
			// INIT
			//----------------------------------
			
			var html = xmlobj.xmlhandler.responseText;

			if ( html == 'found' )
			{
				error_found += reg_error_email_taken + "<br />";
			}
			if ( html == 'banned' )
			{
				error_found += reg_error_email_ban + "<br />";
			}
			if ( html == 'invalid' )
			{
				error_found += reg_error_email_invalid + "<br />";
			}			
			
			//----------------------------------
			// Show errors
			//----------------------------------
			
			if ( error_found )
			{
				reg_field_ids['emailaddress'].className   = input_red;
				reg_img_ids['emailaddress'].src           = ipb_var_image_url + '/' + img_cross;
				reg_msg_ids['emailaddress'].innerHTML     = error_found;
				reg_box_ids['emailaddress'].style.display = 'block';
				error_email = 1;
			}
			else
			{
				error_email = 0;
			}
			
			error_found = '';
		};
		
		//----------------------------------
		// LOAD XML
		//----------------------------------
		
		xmlobj = new ajax_request();
		xmlobj.onreadystatechange( do_request_function );
		xmlobj.process( url );
	}
};

/*-------------------------------------------------------------------------*/
// Check email addresses
/*-------------------------------------------------------------------------*/

function check_email_addresses( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//----------------------------------
	// INIT
	//----------------------------------
	
	var error_found = '';
	
	//----------------------------------
	// Check
	//----------------------------------
	
	if ( ! reg_field_ids['emailaddress'].value.match( /[@\.]/ ) )
	{
		error_found += reg_error_email_missing + "<br />";
	}
	
	if ( ! error_found && ( ! reg_field_ids['emailaddress'].value || ! reg_field_ids['emailaddress-two'].value ) )
	{
		error_found += reg_error_email_missing + "<br />";
	}
	
	if ( reg_field_ids['emailaddress'].value.toLowerCase() != reg_field_ids['emailaddress-two'].value.toLowerCase() )
	{
		error_found += reg_error_email_nm + "<br />";
	}
	
	if ( error_found )
	{
		reg_field_ids['emailaddress'].className       = input_red;
		reg_field_ids['emailaddress-two'].className   = input_red;
		reg_img_ids['emailaddress'].src               = ipb_var_image_url + '/' + img_cross;
		reg_msg_ids['emailaddress'].innerHTML         = error_found;
		reg_box_ids['emailaddress'].style.display     = 'block';
		error_email = 1;
	}
	else
	{
		error_email = 0;
	}
	
	//----------------------------------
	// No error....
	//----------------------------------
	
	if ( error_email == 0 && event.type != 'submit' )
	{
		check_email_addresses_one();
	}
	
	//----------------------------------
	// Still no errors...
	//----------------------------------
	
	if ( error_email == 0 )
	{
		reg_field_ids['emailaddress'].className       = input_green;
		reg_field_ids['emailaddress-two'].className   = input_green;
		reg_img_ids['emailaddress'].src               = ipb_var_image_url + '/' + img_tick;
		reg_box_ids['emailaddress'].style.display     = 'none';
		reg_msg_ids['emailaddress'].innerHTML         = '';
	}

	if( reg_field_ids['emailaddress'].className   == input_red )
	{
		reg_field_ids['emailaddress-two'].className   = input_red;
	}		
};


/*-------------------------------------------------------------------------*/
// Check passwords match
/*-------------------------------------------------------------------------*/

function check_passwords( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//----------------------------------
	// INIT
	//----------------------------------
	
	var error_found = '';
	
	//----------------------------------
	// Check
	//----------------------------------
	
	if ( ! reg_field_ids['password'].value || ! reg_field_ids['password-check'].value )
	{
		error_found += reg_error_no_pass + "<br />";
	}
	
	if ( reg_field_ids['password'].value != reg_field_ids['password-check'].value )
	{
		error_found += reg_error_pass_nm + "<br />";
	}
	
	if ( error_found )
	{
		reg_field_ids['password'].className       = input_red;
		reg_field_ids['password-check'].className = input_red;
		reg_img_ids['password'].src               = ipb_var_image_url + '/' + img_cross;
		reg_msg_ids['password'].innerHTML         = error_found;
		reg_box_ids['password'].style.display     = 'block';
	}
	else
	{
		reg_field_ids['password'].className       = input_green;
		reg_field_ids['password-check'].className = input_green;
		reg_img_ids['password'].src               = ipb_var_image_url + '/' + img_tick;
		reg_box_ids['password'].style.display     = 'none';
		reg_msg_ids['password'].innerHTML         = '';
	}
};

/*-------------------------------------------------------------------------*/
// Check display name
/*-------------------------------------------------------------------------*/

function check_display_name( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//----------------------------------
	// INIT
	//----------------------------------
	
	var error_found = '';
	
	//----------------------------------
	// Make sure we have sommat
	//----------------------------------
	
	if ( ! reg_field_ids['dname'].value || reg_field_ids['dname'].value.length < 3 || reg_field_ids['dname'].value.length > user_display_name_max_length )
	{
		error_found += reg_error_no_name + "<br />";
	}
	
	//----------------------------------
	// Check for illegal chars
	//----------------------------------
	
	if ( reg_field_ids['dname'].value.match( new RegExp( "[" + ucp_dname_illegal_regex + "]" ) ) )
	{
		error_found += reg_error_chars + "<br />";
	}
	
	if ( allowed_chars != "" )
	{
		var test_regex = new RegExp();
		test_regex.compile( "^[" + RegExp.escape(allowed_chars) + "]+$" );
		
		if ( !test_regex.test( reg_field_ids['dname'].value ) )
		{
			error_found += allowed_error + "<br />";
		}
	}
	
	//----------------------------------
	// Ajax: check for existing member name
	//----------------------------------
	
	if ( use_enhanced_js && reg_field_ids['dname'].value && event.type != 'submit' )
	{
		var url = ipb_var_base_url+'act=xmlout&do=check-display-name&name='+escape( reg_field_ids['dname'].value );
		
		// Complete reg form?
		
		if ( in_member_id )
		{
			url += '&id=' + in_member_id;
		}
		
		/*--------------------------------------------*/
		// Main function to do on request
		// Must be defined first!!
		/*--------------------------------------------*/
		
		do_request_function = function()
		{
			//----------------------------------
			// Ignore unless we're ready to go
			//----------------------------------
			
			if ( ! xmlobj.readystate_ready_and_ok() )
			{
				// Could do a little loading graphic here?
				return;
			}
			
			//----------------------------------
			// INIT
			//----------------------------------
			
			var html = xmlobj.xmlhandler.responseText;
			
			if ( html == 'found' )
			{
				error_found += reg_error_taken + "<br />";
			}
			
			//----------------------------------
			// Show errors
			//----------------------------------
			
			if ( error_found )
			{
				reg_field_ids['dname'].className   = input_red;
				reg_img_ids['dname'].src           = ipb_var_image_url + '/' + img_cross;
				reg_msg_ids['dname'].innerHTML     = error_found;
				reg_box_ids['dname'].style.display = 'block';
			}
			else
			{
				reg_field_ids['dname'].className   = input_green;
				reg_img_ids['dname'].src           = ipb_var_image_url + '/' + img_tick;
				reg_box_ids['dname'].style.display = 'none';
				reg_msg_ids['dname'].innerHTML     = '';
			}
			
			error_found = '';
		};
		
		//----------------------------------
		// LOAD XML
		//----------------------------------
		
		xmlobj = new ajax_request();
		xmlobj.onreadystatechange( do_request_function );
		xmlobj.process( url );
	}
	else
	{
		//----------------------------------
		// Show errors
		//----------------------------------
		
		if ( error_found )
		{
			reg_field_ids['dname'].className   = input_red;
			reg_img_ids['dname'].src           = ipb_var_image_url + '/' + img_cross;
			reg_msg_ids['dname'].innerHTML     = error_found;
			reg_box_ids['dname'].style.display = 'block';
		}
		else
		{
			reg_field_ids['dname'].className   = input_green;
			reg_img_ids['dname'].src           = ipb_var_image_url + '/' + img_tick;
			reg_box_ids['dname'].style.display = 'none';
			reg_msg_ids['dname'].innerHTML     = '';
		}
		
		error_found = '';
	}
};


/*-------------------------------------------------------------------------*/
// Check username
/*-------------------------------------------------------------------------*/

function check_user_name( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//----------------------------------
	// INIT
	//----------------------------------
	
	var error_found = '';
	
	//----------------------------------
	// Make sure we have sommat
	//----------------------------------
	
	if ( ! reg_field_ids['name'].value || reg_field_ids['name'].value.length < 3 || reg_field_ids['name'].value.length > user_display_name_max_length )
	{
		error_found += reg_error_username_none + "<br />";
	}
	
	if ( allowed_chars != "" )
	{
		var test_regex = new RegExp();
		test_regex.compile( "^[" + RegExp.escape(allowed_chars) + "]+$" );
		
		if ( !test_regex.test( reg_field_ids['name'].value ) )
		{
			error_found += allowed_error + "<br />";
		}
	}	
	
	//----------------------------------
	// Ajax: check for existing member name
	//----------------------------------
	
	if ( use_enhanced_js && reg_field_ids['name'].value && event.type != 'submit' )
	{
		// + symbols get stripped due to escape
		reg_field_ids['name'].value = reg_field_ids['name'].value.replace("+", "&#43;");
		
		//----------------------------------
		// Get new xhttp obj
		//----------------------------------
		
		var url = ipb_var_base_url+'act=xmlout&do=check-user-name&name='+escape( reg_field_ids['name'].value );
	
		/*--------------------------------------------*/
		// Main function to do on request
		// Must be defined first!!
		/*--------------------------------------------*/
		
		do_request_function = function()
		{
			//----------------------------------
			// Ignore unless we're ready to go
			//----------------------------------
			
			if ( ! xmlobj.readystate_ready_and_ok() )
			{
				// Could do a little loading graphic here?
				return;
			}
			
			//----------------------------------
			// INIT
			//----------------------------------
			
			var html = xmlobj.xmlhandler.responseText;
		
			if ( html == 'found' )
			{
				error_found += reg_error_username_taken + "<br />";
			}
			
			//----------------------------------
			// Show errors
			//----------------------------------
			
			if ( error_found )
			{
				reg_field_ids['name'].className   = input_red;
				reg_img_ids['name'].src           = ipb_var_image_url + '/' + img_cross;
				reg_msg_ids['name'].innerHTML     = error_found;
				reg_box_ids['name'].style.display = 'block';
			}
			else
			{
				reg_field_ids['name'].className   = input_green;
				reg_img_ids['name'].src           = ipb_var_image_url + '/' + img_tick;
				reg_box_ids['name'].style.display = 'none';
				reg_msg_ids['name'].innerHTML     = '';
			}
			
			error_found = '';
		};
		
		//----------------------------------
		// LOAD XML
		//----------------------------------
		
		xmlobj = new ajax_request();
		xmlobj.onreadystatechange( do_request_function );
		xmlobj.process( url );
	}
	else
	{
		//----------------------------------
		// Show errors
		//----------------------------------
		
		if ( error_found )
		{
			reg_field_ids['name'].className   = input_red;
			reg_img_ids['name'].src           = ipb_var_image_url + '/' + img_cross;
			reg_msg_ids['name'].innerHTML     = error_found;
			reg_box_ids['name'].style.display = 'block';
		}
		else
		{
			reg_field_ids['name'].className   = input_green;
			reg_img_ids['name'].src           = ipb_var_image_url + '/' + img_tick;
			reg_box_ids['name'].style.display = 'none';
			reg_msg_ids['name'].innerHTML     = '';
		}
		
		error_found = '';
	}
	
	reg_field_ids['name'].value = reg_field_ids['name'].value.replace("&#43;", "+");
};

/*-------------------------------------------------------------------------*/
// Validate the complete log in form
/*-------------------------------------------------------------------------*/

function validate_complete_login_form( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//------------------------------------------
	// Simply run the functions
	//------------------------------------------
	
	reg_oktogo = 1;
	
	if ( ! email_ok )
	{
		check_email_addresses( event );
	
		//------------------------------------------
		// Got error messages
		//------------------------------------------
	
		if ( reg_msg_ids['emailaddress'].innerHTML )
		{
			reg_oktogo = 0;
		}
	}

	//------------------------------------------
	// Display name...
	//------------------------------------------
	
	if ( reg_got_dname )
	{
		check_display_name( event );
		
		if ( reg_msg_ids['dname'].innerHTML )
		{
			reg_oktogo = 0;
		}
	}
	
	//------------------------------------------
	// Return
	//------------------------------------------
	
	return reg_oktogo ? true : false;
};

/*-------------------------------------------------------------------------*/
// Validate the registration form
/*-------------------------------------------------------------------------*/

function validate_reg_form( event )
{
	if( !event )
	{
		event = window.event;
	}
	
	//------------------------------------------
	// Simply run the functions
	//------------------------------------------

	reg_oktogo = 1;
	
	check_email_addresses( event );

	check_passwords( event );
	
	if( register_method == 'username' )
	{
		check_user_name( event );
	}

	//------------------------------------------
	// Got error messages
	//------------------------------------------
	
	if( register_method == 'username' )
	{
		if ( reg_msg_ids['name'].innerHTML )
		{
			reg_oktogo = 0;
		}
	}
	
	if ( reg_msg_ids['password'].innerHTML )
	{
		reg_oktogo = 0;
	}
	
	if ( reg_msg_ids['emailaddress'].innerHTML )
	{
		reg_oktogo = 0;
	}
	
	//------------------------------------------
	// Display name...
	//------------------------------------------
	
	if ( reg_got_dname )
	{
		check_display_name( event );
		
		if ( reg_msg_ids['dname'].innerHTML )
		{
			reg_oktogo = 0;
		}
	}
	
	//------------------------------------------
	// Return
	//------------------------------------------
	
	return reg_oktogo ? true : false;
};

/*-------------------------------------------------------------------------*/
// They are a COPPA :D
/*-------------------------------------------------------------------------*/

function coppa_save()
{
	//------------------------------------------
	// Set cookie
	//------------------------------------------
	
	my_setcookie( 'coppa', 'yes', 1 );
	my_setcookie( 'coppabday', bday_m + '-' + bday_d + '-' + bday_y, 1 );
};

/*-------------------------------------------------------------------------*/
// Run from the COPPA
/*-------------------------------------------------------------------------*/

function coppa_cancel()
{
	//------------------------------------------
	// Set cookie
	//------------------------------------------
	
	//my_setcookie( 'coppa', '0', 1 );
	locationjump( '' );
};

/*-------------------------------------------------------------------------*/
// Run from the COPPA
/*-------------------------------------------------------------------------*/

function coppa_check()
{
	//------------------------------------------
	// Set cookie
	//------------------------------------------
	
	var coppa_check = my_getcookie( 'coppa' );
	
	if ( coppa_check == 'yes' )
	{
		alert( coppa_bounce );
		
		var bdays = my_getcookie( 'coppabday' );
		var dates = bdays.split( '-' );
		
		locationjump( 'act=reg&CODE=coppa_two&m=' + dates[0] + '&d=' + dates[1] + '&y=' + dates[2] );
	}
};

/*-------------------------------------------------------------------------*/
// Show a more info button?
/*-------------------------------------------------------------------------*/


function reg_get_more_check()
{
	var dropdown = document.getElementById( 'subspackage' );
	var chosenid = dropdown.options[dropdown.selectedIndex].value;
	
	if ( ! chosenid )
	{
		chosenid = 0;
	}
	
	if ( subdesc[chosenid] )
	{
		document.getElementById( 'reg-get-more-info' ).style.display = '';
	}
	else
	{
		document.getElementById( 'reg-get-more-info' ).style.display = 'none';
	}
};


/*-------------------------------------------------------------------------*/
// Show subs more info form
/*-------------------------------------------------------------------------*/

function get_more_info()
{
	var dropdown = document.getElementById( 'subspackage' );
	var chosenid = dropdown.options[dropdown.selectedIndex].value;
	
	if ( ! chosenid )
	{
		chosenid = 0;
	}
	
	//------------------------------------------
	// Toggle view...
	//------------------------------------------
	
	if ( subdesc[chosenid] )
	{
		document.getElementById('pkdesc').innerHTML = subdesc[chosenid];
	
		toggleview('subspkdiv');
	}
};