//------------------------------------------------------------------------------
// IPS Cross-Browser Global Text Editor Functions
//------------------------------------------------------------------------------
// Supports all "v5" browsers (inc. Opera, Safari, etc)
// Used for non RTE applications.
// (c) 2005 Invision Power Services, Inc.
// http://www.invisionpower.com
//------------------------------------------------------------------------------

//-----------------------------------------------------------
// This setting makes hitting the enter key in IE turn into a
// newline (br tag) instead of the default action of using a
// paragraph tag.  This causes the indent and list options not
// to work properly in IE, so do not enable this unless you
// don't care about those options.  This functionality will be
// expanded upon in a future release, however this is not due
// to an issue with IPB, but rather due to how IE handles the
// javascript exec_command functions, and is beyond our control
// without a lot of javascript rewriting of those functions,
// specific to IE behaviors.
//-----------------------------------------------------------

var ie_ptags_to_newlines = true;

var IPS_editor = new Array();

//-------------------------------
// Define which buttons change
// when clicked
//-------------------------------

var buttons_update = new Array(
	"bold",
	"italic",
	"underline",
	"justifyleft",
	"justifycenter",
	"justifyright",
	"insertorderedlist",
	"insertunorderedlist"
);

var ips_primary_colors = new Array(
	'#000000' ,
	'#A0522D' ,
	'#556B2F' ,
	'#006400' ,
	'#483D8B' ,
	'#000080' ,
	'#4B0082' ,
	'#2F4F4F' ,
	'#8B0000' ,
	'#FF8C00' ,
	'#808000' ,
	'#008000' ,
	'#008080' ,
	'#0000FF' ,
	'#708090' ,
	'#696969' ,
	'#FF0000' ,
	'#F4A460' ,
	'#9ACD32' ,
	'#2E8B57' ,
	'#48D1CC' ,
	'#4169E1' ,
	'#800080' ,
	'#808080' ,
	'#FF00FF' ,
	'#FFA500' ,
	'#FFFF00' ,
	'#00FF00' ,
	'#00FFFF' ,
	'#00BFFF' ,
	'#9932CC' ,
	'#C0C0C0' ,
	'#FFC0CB' ,
	'#F5DEB3' ,
	'#FFFACD' ,
	'#98FB98' ,
	'#AFEEEE' ,
	'#ADD8E6' ,
	'#DDA0DD' ,
	'#FFFFFF'
);

//-------------------------------
// Define font faces
//-------------------------------

var ips_primary_fonts = new Array(
	"Arial",
	"Arial Black",
	"Arial Narrow",
	"Book Antiqua",
	"Century Gothic",
	"Comic Sans MS",
	"Courier New",
	"Franklin Gothic Medium",
	"Garamond",
	"Georgia",
	"Impact",
	"Lucida Console",
	"Lucida Sans Unicode",
	"Microsoft Sans Serif",
	"Palatino Linotype",
	"Tahoma",
	"Times New Roman",
	"Trebuchet MS",
	"Verdana"
);

//-------------------------------
// Remap font sizes
//-------------------------------

var ips_primary_sizes = new Array(
	1,
	2,
	3,
	4,
	5,
	6,
	7
);

//-----------------------------------------
// Insert Special Items
//-----------------------------------------

var	ips_format_items =
{
	'cmd_subscript'     : 'Sub-script',
	'cmd_superscript'   : 'Super-script',
	'cmd_strikethrough' : 'Strikethrough'
};

//-----------------------------------------
// Special item images
//-----------------------------------------

var ips_format_item_images =
{
	'cmd_subscript'     : 'rte-subscript.gif',
	'cmd_superscript'   : 'rte-superscript.gif',
	'cmd_strikethrough' : 'rte-strike.gif'
};

//-----------------------------------------
// Language array
//-----------------------------------------

ips_language_array = {};

/**
* Main function.
* Creates instance of an IPS editor
*/
function ips_text_editor( editor_id, mode, use_bbcode, file_path, initial_text )
{
	/**
	* @var string	Unique editor ID (allows multiple editors)
	* @var INT		Is Rich Text Mode (WYSIWYG)
	* @var boolean	Is initialized
	* @var array	Buttons to track
	* @var array	Fonts to track
	* @var array	Item State
	* @var object	Text Area object
	* @var object	Controls Bar
	* @var object	Font drop down object
	* @var object	Size drop down object
	* @var object	Special drop down object
	* @var object	Font format drop down object
	* @var object	Main bar object
	* @var boolean	Use IPS menus
	* @var boolean	Is Internet Explorer
	* @var boolean	Is Mozilla
	* @var boolean	Is Opera
	* @var boolean	Is Safari
	* @var string	RTE files path
	* @var string	Current font-face
	* @var string	Current font-size
	* @var boolean	Using BBCode inplace of HTML
	* @var string	Opening brace
	* @var string	Closing brace
	* @var boolean	Allow advanced options
	* @var string	IPS Editor Frame HTML
	* @var array	Array of pop-ups
    * @var string   Character set of page
	* @var int      Fix up IE <p> to <br /> tags
	* @var object   Emoticon window ID
	* @var int		Editor is loading
	* @var array    Array of hidden objects
	*/
	this.editor_id             = editor_id;
	this.is_rte                = mode;
	this.initialized           = false;
	this.buttons               = new Array();
	this.fonts                 = new Array();
	this.state		           = new Array();
	this.text_obj	           = document.getElementById( this.editor_id + '_textarea' );
	this.control_obj           = document.getElementById( this.editor_id + '_controls' );
	this.font_obj              = document.getElementById( this.editor_id + '_out_fontname' );
	this.size_obj              = document.getElementById( this.editor_id + '_out_fontsize' );
	this.special_obj           = document.getElementById( this.editor_id + '_out_special' );
	this.format_obj            = document.getElementById( this.editor_id + '_out_format' );
	this.mainbar               = document.getElementById( this.editor_id + '_main-bar');
	this.use_menus             = ( typeof( ipsmenu ) == 'undefined' ? false : true );
	this.is_ie		           = is_ie;
	this.is_moz                = is_moz;
	this.is_opera			   = is_opera;
	this.is_safari			   = is_safari;
	this.file_path             = file_path ? file_path : global_rte_includes_url;
	this.font_state            = null;
	this.size_state            = null;
	this.use_bbcode            = use_bbcode;
	this.open_brace            = this.use_bbcode ? '[' : '<';
	this.close_brace           = this.use_bbcode ? ']' : '>';
	this.allow_advanced        = this.use_bbcode ?  0  :  1;
	this.ips_frame_html        = '';
	this.popups                = new Array();
	this.char_set              = global_rte_char_set ? global_rte_char_set : 'UTF-8';
	this.forum_fix_ie_newlines = 0;
	this.emoticon_window_id    = '';
	this.is_loading            = 0;
	this.hidden_objects        = new Array();

	/**
	* History stuff
	*/
	this.history_pointer    = -1;
	this.history_recordings = new Array();
	/**
	* HTML Toggle status
	*/
	this._showing_html = 0;
	
	/**
	* IE Range cache.
	* Prevents selection being lost when
	* iframe / popup is loaded
	*/
	this._ie_cache      = null;
	
	/**
	* Current open bar object
	*/
	this.current_bar_object    = null;
	
	/**
	* Special items menu
	*/
	this.ips_special_items =
	{
		/*'cmd_loader_link'  : new Array( ips_language_array['js_rte_link']   ? ips_language_array['js_rte_link']   : 'Insert <strong>Link...</strong>'   , 'rte-hyperlink.png' ),
		'cmd_loader_unlink': new Array( ips_language_array['js_rte_unlink'] ? ips_language_array['js_rte_unlink'] : '<strong style="color:red">Unlink</strong> Text...'  , 'rte-unlink.png', 'this.format_text( e, "unlink", false );' ),
		'cmd_loader_image' : new Array( ips_language_array['js_rte_image']  ? ips_language_array['js_rte_image']  : 'Insert <strong>Image...</strong>'  , 'rte-image.png'     ),
		'cmd_loader_email' : new Array( ips_language_array['js_rte_email']  ? ips_language_array['js_rte_email']  : 'Insert <strong>Email...</strong>'  , 'rte-email.png'     ),
		'cmd_loader_table' : new Array( ips_language_array['js_rte_table']  ? ips_language_array['js_rte_table']  : 'Insert <strong>Table...</strong>'  , 'rte-table.png'     ),
		'cmd_loader_div'   : new Array( ips_language_array['js_rte_div']    ? ips_language_array['js_rte_div']    : 'Insert <strong>Div...</strong>'    , 'rte-div.png'       )*/
	};
	
	/**
	* Reset lang on format items menu
	*/	
	ips_format_items['cmd_subscript']		= ips_language_array['js_rte_sub']    ? ips_language_array['js_rte_sub']    : 'Sub-script';
	ips_format_items['cmd_superscript']		= ips_language_array['js_rte_sup']    ? ips_language_array['js_rte_sup']    : 'Super-script';
	ips_format_items['cmd_strikethrough']	= ips_language_array['js_rte_strike'] ? ips_language_array['js_rte_strike'] : 'Strikethrough';
	ips_language_array['emos_show_all']		= ips_language_array['emos_show_all'] ? ips_language_array['emos_show_all'] : 'Show All';
	
	/**
	* INIT Text editor
	*/
	this.init = function()
	{
		if ( this.initialized )
		{
			return;
		}
		
		//-----------------------------------------
		// Show control bar that's hidden for non-js
		//-----------------------------------------
		
		this.control_obj.style.display = '';
		
		//-----------------------------------------
		// Reset WYSIWYG flag
		//-----------------------------------------
		
		try
		{
			document.getElementById( this.editor_id + '_wysiwyg_used' ).value = parseInt(this.is_rte);
		}
		catch(err) { }
		
		//-----------------------------------------
		// Get default frame HTML
		//-----------------------------------------
		
		this.ips_frame_html = this.get_frame_html();
		
		//-----------------------------------------
		// Set editor up
		//-----------------------------------------

		this.editor_set_content( initial_text );
		
		//-----------------------------------------
		// Set mouse events
		//-----------------------------------------
		
		this.editor_set_functions();
		
		//-----------------------------------------
		// Set controls up
		//-----------------------------------------
		
		this.editor_set_controls();
	
		
		this.initialized = true;
	};
	
	/**
	* Returns frame HTML
	*
	* @return string Frame HTML
	*/
	this.get_frame_html = function()
	{
		var ips_frame_html = "";
		ips_frame_html += "<html id=\""+this.editor_id+"_html\">\n";
		ips_frame_html += "<head>\n";
		ips_frame_html += "<meta http-equiv=\"content-type\" content=\"text/html; charset=" + this.char_set + "\" />";
		ips_frame_html += "<style type='text/css' media='all'>\n";
		ips_frame_html += "body {\n";
		ips_frame_html += "	background: #FFFFFF;\n";
		ips_frame_html += "	margin: 0px;\n";
		ips_frame_html += "	padding: 4px;\n";
		ips_frame_html += "	font-family: Verdana, arial, sans-serif;\n";
		ips_frame_html += "	font-size: 10pt;\n";
		ips_frame_html += "}\n";
		ips_frame_html += "</style>\n";
		ips_frame_html += "</head>\n";
		ips_frame_html += "<body>\n";
		ips_frame_html += "{:content:}\n";
		ips_frame_html += "</body>\n";
		ips_frame_html += "</html>";
	
		return ips_frame_html;
	};
	
	/**
	* Refocus editor check
	*/
	this.editor_check_focus = function()
	{
		if ( ! this.editor_window.has_focus )
		{
			if ( this.is_opera )
			{
				this.editor_window.focus();
			}
			
			this.editor_window.focus();
		}
	};
	
	/**
	* INIT control bar
	* Goes through control bar images and sets up buttons
	* and drop down menus, adds classnames and sets up
	* onmouse events
	*/
	this.editor_set_controls = function()
	{
		var controls = new Array();
		var _c       = 0;
		
		if ( ! this.control_obj )
		{
			return;
		}
		
		//-----------------------------------------
		// Grab all buttons
		// 2 step 'cos moz goes into infinite loop otherwise
		//-----------------------------------------
		
		var items = this.control_obj.getElementsByTagName("DIV");
	
		for ( var i = 0 ; i < items.length ; i++ )
		{
			if ( ( items[i].className == 'rte-normal' || items[i].className == 'rte-menu-button' || items[i].className == 'rte-normal-menubutton' ) && items[i].id )
			{ 
				controls[ controls.length ] = items[i].id;
			}
		}
		
		for ( var i = 0 ; i < controls.length ; i++ )
		{
			var control = document.getElementById( controls[i] );
		
			if ( control.className == 'rte-normal' )
			{
				this.init_editor_button( control );
			}
			else if ( control.className == 'rte-menu-button' || control.className == 'rte-normal-menubutton' )
			{
				this.init_editor_popup_button( control );
			}
		}
		
		ipsclass.set_unselectable( this.control_obj );
	};
	
	/**
	* INIT Popup Menu
	* Dynamically builds up the context sensitive options
	*
	* @param	object	Button object
	*
	*/
	this.init_editor_popup_button = function( obj )
	{		
		//-----------------------------------------
		// Add to IPS Menu system
		//-----------------------------------------
		
		if ( ! this.use_menus )
		{
			return;
		}
		
		ipsmenu.register( obj.id );
		
		obj.cmd       = obj.id.replace( new RegExp( '^' + this.editor_id + '_popup_(.+?)$' ), '$1' );
		obj.editor_id = this.editor_id;
		obj.state     = false;
	
		//-----------------------------------------
		// Add to buttons array
		//-----------------------------------------
		
		this.buttons[ obj.cmd ] = obj;
		
		//-----------------------------------------
		// Set Up Popup button display
		//-----------------------------------------
		
		if (obj.cmd == 'fontname')
		{
			this.fontout           = this.font_obj;
			this.fontout.innerHTML = obj.title;
			this.fontoptions = {'' : this.fontout};

			for (var option in ips_primary_fonts)
			{
				var div           = document.createElement('div');
				div.id            = this.editor_id + '_fontoption_' + ips_primary_fonts[option];
				div.style.width   = this.fontout.style.width;
				div.style.display = 'none';
				div.innerHTML     = ips_primary_fonts[option];
				this.fontoptions[ips_primary_fonts[option]] = this.fontout.parentNode.appendChild(div);
			}
		}
		else if (obj.cmd == 'fontsize')
		{
			this.sizeout           = this.size_obj;
			this.sizeout.innerHTML = obj.title;
			this.sizeoptions = {'' : this.sizeout};

			for (var option in ips_primary_sizes)
			{
				var div           = document.createElement('div');
				div.id            = this.editor_id + '_sizeoption_' + ips_primary_sizes[option];
				div.style.width   = this.sizeout.style.width;
				div.style.display = 'none';
				div.innerHTML     = ips_primary_sizes[option];
				this.sizeoptions[ips_primary_sizes[option]] = this.sizeout.parentNode.appendChild(div);
			}
		}
		
		obj._onmouseover = obj.onmouseover;
		obj._onclick     = obj.onclick;
		obj.onmouseover  = obj.onmouseout = obj.onclick = ips_editor_events.prototype.popup_button_onmouseevent;
		
		ipsmenu.menu_registered[obj.id]._open = ipsmenu.menu_registered[obj.id].open;
		ipsmenu.menu_registered[obj.id].open  = ips_editor_events.prototype.popup_button_show;
	};
	
	
	/**
	* Dynamically builds the HTML for the IPS Menu system
	*
	* @var object	Javascript object clicked
	*/
	this.init_editor_menu = function( obj )
	{ 
		//-----------------------------------------
		// Create menu wrapper
		//-----------------------------------------
		
		var menu = document.createElement('div');

		menu.id             = this.editor_id + '_popup_' + obj.cmd + '_menu';
		menu.className      = 'rte-popupmenu';
		menu.style.display  = 'none';
		menu.style.cursor   = 'default';
		menu.style.padding  = '3px';
		menu.style.width    = 'auto';
		menu.style.height   = 'auto';
		menu.style.overflow = 'hidden';

		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		
		switch( obj.cmd )
		{
			case 'fontsize':
				for( var i in ips_primary_sizes )
				{ 
					if ( typeof( ips_primary_sizes[i] ) == 'function' )
					{
						continue;
					}
					
					var option             = document.createElement('div');

					option.style.paddingTop = ips_primary_sizes[ i ]*2 + 'px';
					option.style.paddingBottom = ips_primary_sizes[ i ]*2 + 'px';
					
					option.innerHTML       = '<font size="' + ips_primary_sizes[ i ] + '">' + ips_primary_sizes[ i ] + '</font>';
					option.className       = 'rte-menu-size';
					option.title           = ips_primary_sizes[ i ];
					option.cmd             = obj.cmd;
					option.editor_id       = this.editor_id;
					option.onmouseover     = option.onmouseout = option.onmouseup = option.onmousedown = ips_editor_events.prototype.menu_option_onmouseevent;
					option.onclick         = ips_editor_events.prototype.font_format_option_onclick;
					
					menu.style.width       = this.size_obj.style.width;
					
					menu.appendChild(option);
				}
				break;
				
			case 'fontname':
				for( var i in ips_primary_fonts )
				{
					if ( typeof( ips_primary_fonts[i] ) == 'function' )
					{
						continue;
					}
					
					var option             = document.createElement('div');
					option.innerHTML       = '<font face="' + ips_primary_fonts[ i ] + '">' + ips_primary_fonts[ i ] + '</font>';
					option.className       = 'rte-menu-face';
					option.title           = ips_primary_fonts[ i ];
					option.cmd             = obj.cmd;
					option.editor_id       = this.editor_id;
					option.onmouseover     = option.onmouseout = option.onmouseup = option.onmousedown = ips_editor_events.prototype.menu_option_onmouseevent;
					option.onclick         = ips_editor_events.prototype.font_format_option_onclick;
					
					menu.style.width       = this.font_obj.style.width;
					menu.appendChild(option);
				}
				break;
				
			case 'special':
				for( var i in this.ips_special_items )
				{
					if ( typeof( this.ips_special_items[i] ) == 'function' )
					{
						continue;
					}
					
					var option             = document.createElement('div');
					var img                = ( typeof this.ips_special_items[i][1] != 'undefined' )  ? '<img src="' + global_rte_images_url + '/' + this.ips_special_items[i][1] + '" style="vertical-align:middle" border="" /> ' : '';
					option.innerHTML       = img + this.ips_special_items[i][0];
					option.className       = 'rte-menu-face';
					option.cmd             = 'module_load',
					option.loader_key      = i.replace( 'cmd_loader_', '' );
					option.editor_id       = this.editor_id;
					option.onmouseover     = option.onclick = option.onmouseout = option.onmouseup = option.onmousedown = ips_editor_events.prototype.special_onmouse_event;

					menu.style.width       = this.special_obj.style.width;
					menu.appendChild(option);
				}
				break;
			case 'format':
				for( var i in ips_format_items )
				{
					if ( typeof( ips_format_items[i] ) == 'function' )
					{
						continue;
					}
					
					var option             = document.createElement('div');
					var img                = ( typeof ips_format_item_images[i] != 'undefined' )  ? '<img src="' + global_rte_images_url + '/' + ips_format_item_images[i] + '" style="vertical-align:middle" border="" /> ' : '';
					option.innerHTML       = img + ips_format_items[i];
					option.className       = 'rte-menu-face';
					option.cmd             = i.replace( 'cmd_', '' );
					option.editor_id       = this.editor_id;
					option.onmouseover     = option.onclick = option.onmouseout = option.onmouseup = option.onmousedown = ips_editor_events.prototype.special_onmouse_event;

					menu.style.width       = '130px';
					menu.appendChild(option);
				}
				break;
				
			case 'emoticons':
				var table          = document.createElement('table');
				table.cellPadding  = 0;
				table.cellSpacing  = 0;
				table.border       = 0;
				//table.width		   = 'auto';
				
				if ( this.is_ie )
				{
					table.style.paddingRight = '15px'; // Scrollbar fun
				}			 	
				
				var i      = 0;
				var perrow = 3;
				
				var tr       = table.insertRow(-1);
				var td       = tr.insertCell(-1);
				td.colSpan   = perrow;
				td.align     = 'center';
			 	td.cellPadding = 0;
				td.innerHTML = '<div class="rte-menu-emo-header"><a href="#" style="text-decoration:none" onclick="return show_all_emoticons(\'' + this.editor_id + '\')">' + ips_language_array['emos_show_all'] + '</a></div>'; 
				
				for( var emo in ips_smilie_items )
				{
					if ( i % perrow == 0 )
					{
						var tr = table.insertRow(-1);
					}
				
					i++;

					var div       = document.createElement('div');
					var _tmp      = ips_smilie_items[ emo ].split( "," );
					var img       = '<img src="' + global_rte_emoticons_url + '/' + _tmp[1] + '" style="vertical-align:middle" border="0" id="smid_' + _tmp[0] + '" /> ';
					div.innerHTML = img;
					
					var option           = tr.insertCell(-1);
				 	option.className     = 'rte-menu-emo';
					option.appendChild(div);
			
					option.cmd             = obj.cmd;
					option.editor_id       = this.editor_id;
				 	option.id              = this.editor_id + '_emoticon_' + _tmp[0];
					option.emo_id		   = _tmp[0];
					option.emo_image       = _tmp[1];
					option.emo_code        = emo;
					option.onmouseover     = option.onmouseout = option.onmouseup = option.onmousedown = ips_editor_events.prototype.menu_option_onmouseevent;
					option.onclick         = ips_editor_events.prototype.emoticon_onclick;
				}
				
				if ( i > 0 )
				{
					menu.style.width     = 'auto';

					if( this.is_ie )
					{
						menu.style.paddingRight = '15px'; // Scrollbar fun
					}
									
					menu.style.height    = '200px';
					menu.style.overflow  = 'auto';
					menu.style.overflowX = 'hidden';
					menu.appendChild(table);
					break;
				}
				else
				{
					// No emo's, but show the 'show all' link
					menu.style.width    = 'auto';
					menu.style.height   = '40px';
					menu.style.overflow = 'auto';
					menu.appendChild(table);
					break;
										
				//	document.getElementById( this.editor_id + '_popup_' + obj.cmd ).style.display = 'hidden';
				//	document.getElementById( this.editor_id + '_popup_' + obj.cmd + '_menu' ).style.display = 'hidden';
				}
					
			case 'forecolor':
			case 'backcolor':
				var table          = document.createElement('table');
				table.cellPadding  = 0;
				table.cellSpacing  = 0;
				table.border       = 0;
				var i = 0;
				
				for ( var hex in ips_primary_colors )
				{
					if ( typeof( ips_primary_colors[hex] ) == 'function' )
					{
						continue;
					}
					
					if ( i % 8 == 0 )
					{
						var tr = table.insertRow(-1);
					}
					
					i++;

					var div = document.createElement('div');
					div.style.backgroundColor = ips_primary_colors[hex];
					div.innerHTML          = '&nbsp;';
					var option             = tr.insertCell(-1);
				 	option.className       = 'rte-menu-color';
					option.appendChild(div);
				
					option.cmd             = obj.cmd;
					option.editor_id       = this.editor_id;
					option.colorname       = ips_primary_colors[hex];
				 	option.id              = this.editor_id + '_color_' + ips_primary_colors[hex];
					option.onmouseover     = option.onmouseout = option.onmouseup = option.onmousedown = ips_editor_events.prototype.menu_option_onmouseevent;
					option.onclick         = ips_editor_events.prototype.color_cell_onclick;
				}
			
				menu.style.overflow = 'visible';
				menu.appendChild(table);
				break;
		}
		
		this.popups[ obj.cmd ] = this.control_obj.appendChild(menu);
		
		ipsclass.set_unselectable( menu );
	};
	
	/**
	* INIT Editor Button
	* Initialize an editor  button (bold, italic, etc)
	*
	* @param	object	Button DIV object
	*/
	this.init_editor_button = function( obj )
	{ 
		obj.cmd       = obj.id.replace( new RegExp( '^' + this.editor_id + '_cmd_(.+?)$' ), '$1' );
		obj.editor_id = this.editor_id;
		
		//-----------------------------------------
		// Add to buttons array
		//-----------------------------------------
		
		this.buttons[ obj.cmd ] = obj;
		
		//-----------------------------------------
		// Set up defaults
		//-----------------------------------------
		
		obj.state     = false;
		obj.mode      = 'normal';
		obj.real_type = 'button';
		
		obj.onclick     = ips_editor_events.prototype.button_onmouse_event;
		obj.onmousedown = ips_editor_events.prototype.button_onmouse_event;
		obj.onmouseover = ips_editor_events.prototype.button_onmouse_event;
		obj.onmouseout  = ips_editor_events.prototype.button_onmouse_event;
	};
	
	/**
	* Toggle menu context
	* Toggles the menu's class for mouseover, select, etc
	*
	* @param	object Menu object
	* @param	object state
	*/
	this.set_menu_context = function(obj, state)
	{
		//-----------------------------------------
		// Show HTML? Return...
		//-----------------------------------------
		
		if ( this._showing_html )
		{
			return false;
		}
		
		switch (obj.state)
		{
			case true:
			{
				this.editor_set_ctl_style(obj, 'menubutton', 'down');
				break;
			}

			default:
			{
				switch (state)
				{
					case 'mouseout':
					{
						this.editor_set_ctl_style(obj, 'menubutton', 'normal');
						break;
					}
					case 'mousedown':
					{
						this.editor_set_ctl_style(obj, 'menubutton', 'down');
						break;
					}
					case 'mouseup':
					case 'mouseover':
					{
						this.editor_set_ctl_style(obj, 'menubutton', 'hover');
						break;
					}
				}
			}
		}
	};
	
	/**
	* Toggle button context
	* Toggles the menu's class for mouseover, select, etc
	*
	* @param	object menu object
	* @param	object state
	* @param	object type
	*/
	this.set_button_context = function(obj, state, type)
	{
		//-----------------------------------------
		// Showing HTML?
		//-----------------------------------------
		
		if ( this._showing_html )
		{
			return false;
		}
		
		if (typeof type == 'undefined')
		{
			type = 'button';
		}
		
		if ( state == 'mousedown' && ( obj.cmd == 'undo' || obj.cmd == 'redo' ) )
		{
			return false;
		}
		
		switch (obj.state)
		{
			case true:
			{
				switch (state)
				{
					case 'mouseout':
					{
						this.editor_set_ctl_style(obj, 'button', 'selected');
						break;
					}
					case 'mouseover':
					case 'mousedown':
					case 'mouseup':
					{
						this.editor_set_ctl_style(obj, type, 'down');
						break;
					}
				}
				break;
			}
			default:
			{
				switch (state)
				{
					case 'mouseout':
					{
						this.editor_set_ctl_style(obj, type, 'normal');
						break;
					}
					case 'mousedown':
					{
						this.editor_set_ctl_style(obj, type, 'down');
						break;
					}
					case 'mouseover':
					case 'mouseup':
					{
						this.editor_set_ctl_style(obj, type, 'hover');
						break;
					}
				}
				break;
			}
		}
	};
	
	/**
	* Sets the editor button control style
	*
	* @var	object	Button Object
	* @var	string	Control Type
	* @var	string	Control Mode
	*/
	this.editor_set_ctl_style = function( obj, type, mode )
	{
		if ( obj.mode != mode )
		{
			//-----------------------------------------
			// Add in -menu class
			//-----------------------------------------
			
			var extra = '';
			
			if ( type == 'menu' )
			{
				extra = '-menu';
			}
			else if ( type == 'menubutton' )
			{
				extra = '-menubutton';
			}
			
			//-----------------------------------------
			// Add in -color if it's a color box
			//-----------------------------------------
			
			extra     += obj.colorname ? '-color' : '';
			
			//-----------------------------------------
			// Add in -emo if it's an emo
			//-----------------------------------------
			
			extra     += obj.emo_id ? '-emo' : '';
			
			//-----------------------------------------
			// Set mote...
			//-----------------------------------------
			
			obj.mode  = mode;
			
			try
			{
				switch (mode)
				{
					case "normal":
					{
						// Normal
						obj.className = 'rte-normal' + extra;
					}
					break;

					case "hover":
					{
						// Hover
						obj.className = 'rte-hover' + extra;
					}
					break;

					case "selected":
					case "down":
					{
						// On
						obj.className = 'rte-selected' + extra;
					}
					break;
				}
			}
			catch (e)
			{
			}
		}
	};
	
	/**
	* Format Text
	*
	* @var	event	Event Object
	* @var	string	Formatted command
	* @var	string	Formatting command extra
	*/
	this.format_text = function(e, command, arg)
	{
		e = ipsclass.cancel_bubble( e, true );
		
		//-----------------------------------------
		// Special considerations...
		//-----------------------------------------
		
		if ( command.match( /resize_/ ) )
		{
			this.resize_editorbox( command.replace( /resize_(up|down)/, "$1" ) );
		}
		
		//-----------------------------------------
		// Switch editor...
		//-----------------------------------------
		
		if ( command.match( /switcheditor/i ) )
		{
			switch_editor_mode( this.editor_id );
		}	
		
		//-----------------------------------------
		// Manually recording history?
		//-----------------------------------------
		
		if ( ! this.is_rte )
		{
			if ( command != 'redo' )
			{
				this.history_record_state( this.editor_get_contents() );
			}
		}
		
		//-----------------------------------------
		// Continue...
		//-----------------------------------------
		
		this.editor_check_focus();
		
		if ( this[ command ] )
		{
			var return_val = this[ command ](e);
		}
		else
		{
			try
			{
				var return_val = this.apply_formatting( command, false, (typeof arg == 'undefined' ? true : arg) );
			}
			catch(e)
			{
				var return_val = false;
			}
		}
		
		//-----------------------------------------
		// Manually recording history?
		//-----------------------------------------
		
		if ( ! this.is_rte )
		{
			if ( command != 'undo' )
			{
				this.history_record_state( this.editor_get_contents() );
			}
		}

		//-----------------------------------------
		// Set context
		//-----------------------------------------
		
		this.set_context(command);
		
		//-----------------------------------------
		// Check focus
		//-----------------------------------------
		
		this.editor_check_focus();

		return return_val;
	};
	
	/**
	* Check spelling
	*/
	this.spellcheck = function()
	{
		if ( this.is_moz || this.is_opera )
		{
			return false;
		}
		try
		{
			if ( this.rte_mode )
			{
				var tmpis = new ActiveXObject("ieSpell.ieSpellExtension").CheckDocumentNode( this.editor_document );
			}
			else
			{
				var tmpis = new ActiveXObject("ieSpell.ieSpellExtension").CheckAllLinkedDocuments( this.editor_document );
			}
		}
		catch( exception )
		{
			if ( exception.number == -2146827859 )
			{
				if ( confirm( ips_language_array['js_rte_erroriespell'] ? ips_language_array['js_rte_erroriespell'] : "ieSpell not detected.  Click Ok to go to download page." ) )
				{
					window.open("http://www.iespell.com/download.php", "DownLoad");
				}
			}
			else
			{
				alert( ips_language_array['js_rte_errorliespell'] ? ips_language_array['js_rte_errorliespell'] : "Error Loading ieSpell: Exception " + exception.number);
			}
		}
	};
	
	/**
	* Removes items that you don't wish to have
	* @param	string	Command key
	*/
	this.module_remove_item = function( key )
	{
		//-----------------------------------------
		// Remove...
		//-----------------------------------------
		
		var tmp = this.ips_special_items;
		
		this.ips_special_items = new Array();
		
		for( var i in tmp )
		{
			if ( i != 'cmd_loader_' + key )
			{
				this.ips_special_items[ i ] = tmp[i];
			}
		}
	};
	
	/**
	* Add to special items array
	* @param	string	Command key
	* @param	string	Menu text
	* @param	string	Menu Image
	* @param	string	Javascript code to eval (Optional)
	*/
	this.module_add_item = function( key, text, image, evalcode )
	{
		//-----------------------------------------
		// Add...
		//-----------------------------------------
		
		this.ips_special_items[ 'cmd_loader_' + key ] = new Array( text, image, evalcode );
	};
	
	/**
	* Load module
	* @param	object	Object
	* @param	object	Mouse event
	* @param	string	Loader function
	*/
	this.module_load = function( obj, e, loader_key )
	{
		if ( ! loader_key )
		{
			return false;
		}
		
		e = ipsclass.cancel_bubble( e, true );

		this.editor_check_focus();
		
		this.preserve_ie_range();
		
		menu_action_close();
		
		//-----------------------------------------
		// Extra args?
		//-----------------------------------------
		
		var _m    = loader_key.match( /\{(.+?)\}$/ );
		var _args = '';
		
		try
		{
			if ( _m[1] )
			{
				_args = _m[1];
			}
		}
		catch(internetexplorer)
		{
			
		}
		
		//-----------------------------------------
		// Eval or show in control bar?
		//-----------------------------------------
		
		if ( typeof this.ips_special_items[ 'cmd_loader_' + loader_key ][2] != "undefined" )
		{
			eval( this.ips_special_items[ 'cmd_loader_' + loader_key ][2] );
			return false
		}
		else
		{
			this.module_show_control_bar( loader_key, _args );
		}
	};
	
	/**
	* Show new control bar
	*
	* @param	type	Type of control bar (must be same as function key)
	*/
	this.module_show_control_bar = function( type, _args )
	{
		if ( ! this.control_obj )
		{
			return;
		}
		
		type  = type.replace( /(\{.+?\})$/, '' );
		_args = ( typeof( _args ) != 'undefined' ) ? _args : '';
		
		//-----------------------------------------
		// Already open? Kill it
		//-----------------------------------------
		
		if ( this.current_bar_object )
		{
			this.module_remove_control_bar();
		}
		
		//-----------------------------------------
		// Spawn new DIV object
		//-----------------------------------------
		
		var newdiv = document.createElement('div');
		
		newdiv.id             = this.editor_id + '_htmlblock_' + type + '_menu';
		newdiv.style.display  = '';
		newdiv.className      = 'rte-buttonbar';
		newdiv.style.zIndex   = parseInt( this.control_obj.style.zIndex ) + 1;
		newdiv.style.position = 'absolute';
		newdiv.style.width    = '320px';
		newdiv.style.height   = '400px';
		newdiv.style.top      = ipsclass.get_obj_toppos(  this.mainbar ) + 'px';
		
		var _left = ipsclass.get_obj_leftpos( this.mainbar ) - ( parseInt(newdiv.style.width) + 10 );
		
		if ( _left < 1 )
		{
			_left = ipsclass.get_obj_leftpos( this.mainbar );
		}
		
		newdiv.style.left     = ipsclass.get_obj_leftpos( this.mainbar ) - ( parseInt(newdiv.style.width) + 10 ) + 'px';
		newdiv.style.left     = _left + 'px';
		
		var tmpheight 		  = parseInt(newdiv.style.height) - 16;
		newdiv.innerHTML      = this.module_wrap_html_panel( "<iframe id='"+ this.editor_id + '_iframeblock_' + type + '_menu' + "' src='"+global_rte_includes_url + "module_" + type + ".php?editorid="+this.editor_id+"&" + _args + "' frameborder='0' style='text-align:left;background:transparent;border:0px;overflow:auto;width:98%;height:" + tmpheight + "px'></iframe>" );
		
		//-----------------------------------------
		// Add and show
		//-----------------------------------------
		
		this.mainbar.appendChild(newdiv);
		
		if ( is_ie )
		{
			document.getElementById( this.editor_id + '_iframeblock_' + type + '_menu' ).style.backgroundColor = 'transparent';
			document.getElementById( this.editor_id + '_iframeblock_' + type + '_menu' ).allowTransparency     = true;
		}
		
		ipsclass.set_unselectable( newdiv );
		
		//-----------------------------------------
		// INIT Drag
		//-----------------------------------------
		
		Drag.init( document.getElementById( this.editor_id + '_pallete-handle' ), newdiv );
		
		this.current_bar_object = newdiv;
	};
	
	/**
	* Remove control bar
	* Removes the control bar.
	*/
	this.module_remove_control_bar = function()
	{
		if ( ! this.current_bar_object )
		{
			return;
		}
		
		//-----------------------------------------
		// Kill old bar
		//-----------------------------------------
		
		this.mainbar.removeChild( this.current_bar_object );
		
		//-----------------------------------------
		// Reset bar object
		//-----------------------------------------
		
		this.current_bar_object = null;
	};
	
	/**
	* Wrap HTML block with basic control panel stuff
	*/
	this.module_wrap_html_panel = function( html )
	{
		var newhtml = "";
		newhtml    += " <div id='"+this.editor_id + "_pallete-wrap'>";
		newhtml    += "   <div id='" + this.editor_id + "_pallete-main'>";
		newhtml    += "    <div class='rte-cb-bg' id='" + this.editor_id + "_pallete-handle'>";
		newhtml    += "			<div align='left'><img id='" + this.editor_id + "_cb-close-window' src='"+global_rte_images_url+"rte-cb-close.gif' alt='' class='ipd' border='0' /></div>";
		newhtml    += "	   </div>";
		newhtml    += "    <div>" + html + "</div>";
		newhtml    += "  </div>";
		newhtml    += " </div>";
		
		return newhtml;
	};
	
	/**
	* Resize editor
	*
	* @param string	 Up (smaller) Down (Larger)
	*/
	
	this.resize_editorbox = function( direction )
	{
		var inc_value	   = 100;
		var current_height = parseInt( this.editor_box.style.height );
		var new_height     = 0;
		current_height     = current_height ? current_height : 300;
		
		//-----------------------------------------
		// If we're not going smaller than 300...
		//-----------------------------------------
	
		if ( current_height >= 50 )
		{
			if ( direction == 'up' )
			{
				new_height = current_height - inc_value;
			}
			else
			{
				new_height = current_height + inc_value;
			}
			
			if ( new_height > 249 )
			{
				this.editor_box.style.height = new_height + 'px';
			
				ipsclass.my_setcookie( 'ips_rte_height', new_height, 1 );
			}
		}
	};
	
	/**
	* Make HTML entities safe
	* @param	string	Raw HTML
	* @return	string	Treated HTML
	*/
	this.htmlspecialchars = function( html )
	{
		html = html.replace(/&/g, "&amp;");
		html = html.replace(/"/g, "&quot;");
		html = html.replace(/</g, "&lt;");
		html = html.replace(/>/g, "&gt;");
		
		return html;
	};
	
	/**
	* Make HTML entities unsafe
	* @param	string	Raw HTML
	* @return	string	Treated HTML
	*/
	this.unhtmlspecialchars = function( html )
	{
		html = html.replace( /&quot;/g, '"' );
		html = html.replace( /&lt;/g  , '<' );
		html = html.replace( /&gt;/g  , '>' );
		html = html.replace( /&amp;/g , '&' );
		
		return html;
	};
	
	/**
	* Remove HTML tags. Ugly.
	* @param	string	Raw HTML
	* @return	string	Treated HTML
	*/
	this.strip_html = function( html )
	{
		html = html.replace( /<\/?([^>]+?)>/ig, "");
		return html;
	};
	
	/**
	* Remove empty HTML tags. Ugly.
	* @param	string	Raw HTML
	* @return	string	Treated HTML
	*/
	this.strip_empty_html = function( html )
	{
		html = html.replace( '<([^>]+?)></([^>]+?)>', "");
		return html;
	};
	
	/**
	* Format HTML a bit nicely
	*/
	this.clean_html = function( t )
	{
		if ( t == "" || typeof t == 'undefined' )
		{
			return t;
		}
		
		//-------------------------------
		// Sort out BR tags
		//-------------------------------

		t = t.replace( /<br>/ig, "<br />");

		//-------------------------------
		// Remove empty <p> tags
		//-------------------------------

		t = t.replace( /<p>(\s+?)?<\/p>/ig, "");

		//-------------------------------
		// HR issues
		//-------------------------------

		t = t.replace( /<p><hr \/><\/p>/ig                    , "<hr />"); 
		t = t.replace( /<p>&nbsp;<\/p><hr \/><p>&nbsp;<\/p>/ig, "<hr />");

		//-------------------------------
		// Attempt to fix some formatting
		// issues....
		//-------------------------------

		t = t.replace( /<(p|div)([^&]*)>/ig     , "\n<$1$2>\n" );
		t = t.replace( /<\/(p|div)([^&]*)>/ig   , "\n</$1$2>\n");
		t = t.replace( /<br \/>(?!<\/td)/ig     , "<br />\n"   );

		//-------------------------------
		// And some table issues...
		//-------------------------------

		t = t.replace( /<\/(td|tr|tbody|table)>/ig  , "</$1>\n");
		t = t.replace( /<(tr|tbody|table(.+?)?)>/ig , "<$1>\n" );
		t = t.replace( /<(td(.+?)?)>/ig             , "\t<$1>" );
		
		//-------------------------------
		// Newlines
		//-------------------------------

		t = t.replace( /<p>&nbsp;<\/p>/ig     , "<br />");
		t = t.replace( /<br \/>/ig            , "<br />\n");
		t = t.replace( /<br>/ig               , "<br />\n");
		
		t = t.replace( /<td><br \/>\n<\/td>/ig  , "<td><br /></td>" );
		
		//-----------------------------------------
		// Script tags
		//-----------------------------------------

		t = t.replace( /<script/g , "&lt;script" );
		t = t.replace( /<\/script>/g , "&lt;/script&gt;" );
		
		return t;
	};
	
	
	/**
	* Caches RNG for IE so when it loses focus,
	* The range is automatically preserved.
	*/
	this.preserve_ie_range = function()
	{ 
		if ( this.is_ie )
		{
			this._ie_cache = this.is_rte ? this.editor_document.selection.createRange() : document.selection.createRange();
		}
	};
	
	/**
	* Wrap tag: Lite
	* Quick method of below
	*
	* @param	string	Start text
	* @param	string	Close text
	*/
	this.wrap_tags_lite = function( start_text, close_text )
	{
		//-----------------------------------------
		// Got selected text?
		//-----------------------------------------
		
		selected_text = this.get_selection();
		selected_text = (selected_text === false) ? '' : new String(selected_text);

		this.insert_text( start_text + selected_text + close_text );
		
		return false;
	};
	
	/**
	* Destroy Text Editor
	*/
	this.destruct = function()
	{
		for ( var i in this.buttons )
		{ 
			var _type = ( this.buttons[i].real_type == 'button' ) ? 'button' : 'menubutton';
			
			this.editor_set_ctl_style( this.buttons[i], _type, 'normal' );
		}
		
		if (this.fontoptions)
		{
			for (var i in this.fontoptions)
			{
				if (i != '')
				{
					this.fontoptions[i].parentNode.removeChild(this.fontoptions[i]);
				}
			}
			this.fontoptions[''].style.display = '';
		}

		if (this.sizeoptions)
		{
			for (var i in this.sizeoptions)
			{
				if (i != '')
				{
					this.sizeoptions[i].parentNode.removeChild(this.sizeoptions[i]);
				}
			}
			this.sizeoptions[''].style.display = '';
		}
		
		//-----------------------------------------
		// Make any hidden items appear
		//-----------------------------------------
		
		for ( var i in this.hidden_objects )
		{
			try
			{
				document.getElementById( i ).style.display = '';
			}
			catch(me)
			{
			}
		}
	};
		
	/**
	* Wrap Tags
	*
	* @param	string	Tag to wrap
	* @param	boolean	Has optional params
	* @param	string	Selected Text
	*/
	this.wrap_tags = function(tag_name, has_option, selected_text)
	{
		//-----------------------------------------
		// Fix up for HTML use
		//-----------------------------------------
		
		var tag_close = tag_name;
		
		if ( ! this.use_bbcode )
		{
			switch( tag_name )
			{
				case 'url':
					tag_name  = 'a href';
					tag_close = 'a';
					break;
			 	case'email':
					tag_name   = 'a href';
					tag_close  = 'a';
					has_option = 'mailto:' + has_option;
					break;
				case 'img':
					tag_name  = 'img src';
					tag_close = '';
					break;
				case 'font':
					tag_name  = 'font face';
					tag_close = 'font';
					break;
				case 'size':
					tag_name  = 'font size';
					tag_close = 'font';
					break;
				case 'color':
					tag_name  = 'font color';
					tag_close = 'font';
					break;
				case 'background':
					tag_name  = 'font bgcolor';
					tag_close = 'font';
					break;
				case 'indent':
					tag_name = tag_close = 'blockquote';
					break;
				case 'left':
				case 'right':
				case 'center':
					has_option = tag_name;
					tag_name   = 'div align';
					tag_close  = 'div';
					break;
			}
		}
		
		//-----------------------------------------
		// Got selected text?
		//-----------------------------------------
		
		if ( typeof selected_text == 'undefined' )
		{
			selected_text = this.get_selection();
			
			selected_text = (selected_text === false) ? '' : new String(selected_text);
		}
		
		//-----------------------------------------
		// Using option?
		//-----------------------------------------
		
		if ( has_option === true )
		{
			var option = prompt( ips_language_arrayp['js_rte_optionals'] ? ips_language_arrayp['js_rte_optionals'] : "Enter the optional arguments for this tag", '');
			
			if ( option )
			{
				var opentag = this.open_brace + tag_name + '="' + option + '"' + this.close_brace;
			}
			else
			{
				return false;
			}
		}
		else if ( has_option !== false )
		{
			var opentag = this.open_brace + tag_name + '="' + has_option + '"' + this.close_brace;
		}
		else
		{
			var opentag = this.open_brace + tag_name + this.close_brace;
		}

		var closetag = this.open_brace + '/' + tag_close + this.close_brace;
		
		var text     = opentag + selected_text + closetag;

		this.insert_text( text );
		
		return false;
	};
	
	/**
	* HISTORY: Record state
	* @param string	Editor contents
	*/
	this.history_record_state = function( content )
	{
		//-----------------------------------------
		// Make sure we're not recording twice
		//-----------------------------------------
		
		if ( this.history_recordings[ this.history_pointer ] != content )
		{
			this.history_pointer++;
			this.history_recordings[ this.history_pointer ] = content;
			
			//-----------------------------------------
			// Make sure we've not gone back in time
			//-----------------------------------------
			
			if (typeof this.history_recordings[this.history_pointer + 1] != 'undefined')
			{
				this.history_recordings[this.history_pointer + 1] = null;
			}
		}
	};
	
	/**
	* HISTORY: Move in time!
	* @param	int	Cursor movement
	*/
	this.history_time_shift = function( inc )
	{
		var i = this.history_pointer + inc;
		
		if ( i >= 0 && this.history_recordings[ i ] != null && typeof this.history_recordings[ i ] != 'undefined' )
		{
			this.history_pointer += inc;
		}
	};
	
	/**
	* HISTORY: return recorded state
	*/
	this.history_fetch_recording = function()
	{
		if ( typeof this.history_recordings[ this.history_pointer ] != 'undefined' && this.history_recordings[ this.history_pointer ] != null )
		{
			return this.history_recordings[ this.history_pointer ];
		}
		else
		{
			return false;
		}
	};
	
	// =============================================================================
	// RICH TEXT EDITOR (Override and create functions for the RTE)
	// =============================================================================
	
	if ( this.is_rte )
	{
		/**
		* RTE: NON MOZ
		* Adds text into the editor window / area
		*
		* @var	string	Text to add
		* @var	boolean	Do init
		*/
		this.editor_write_contents = function( text, do_init )
		{
			if ( text == '' && this.is_moz )
			{
				text = '<br />';
			}
			
			if ( this.editor_document && this.editor_document.initialized )
			{
				this.editor_document.body.innerHTML = text;
			}
			else
			{
				if ( do_init )
				{
					this.editor_document.designMode = 'on';
				}
				
				this.editor_document = this.editor_window.document;
				this.editor_document.open( 'text/html', 'replace' );
				this.editor_document.write( this.ips_frame_html.replace( '{:content:}', text ) );
				this.editor_document.close();
				
				if ( do_init )
				{
					this.editor_document.body.contentEditable = true;
					this.editor_document.initialized          = true;
				}
			}
		};
		
		/**
		* RTE: NON MOZ
		* Set content, set design mode
		*
		* @var	string	Initial text
		*/
		this.editor_set_content = function( init_text )
		{ 
			//-----------------------------------------
			// Get iFrame object
			//-----------------------------------------
			
			var iframe_obj = null;
			
			try
			{
				iframe_obj = document.getElementById( this.editor_id + '_iframe' );
			}
			catch(error)
			{
				//alert( error );
			}
			
			if ( iframe_obj )
			{
				this.editor_box = iframe_obj;
			}
			else
			{
				var iframe = document.createElement('iframe');
				
				if ( this.is_ie && window.location.protocol == 'https:' )
				{
					iframe.src = this.file_path + '/index.html';
				}
				
				this.editor_box           = this.text_obj.parentNode.appendChild(iframe);
				this.editor_box.id        = this.editor_id + '_iframe';
				this.editor_box.tabIndex  = 3;
			}
			
			if ( ! this.is_ie )
			{
				this.editor_box.style.border = '2px inset';
			}
			
			//-----------------------------------------
			// Cookie'd height?
			//-----------------------------------------
			
			var test_height = parseInt( ipsclass.my_getcookie( 'ips_rte_height' ) );
			
			if ( ! isNaN(test_height) && test_height > 50 )
			{
				this.text_obj.style.height = test_height + 'px';
			}
			
			//-----------------------------------------
			// Set up
			//-----------------------------------------
			
			this.editor_box.style.width  = this.text_obj.style.width;
			this.editor_box.style.height = this.text_obj.style.height;
			this.editor_box.className    = this.text_obj.className;
			this.text_obj.style.display  = 'none';
			
			this.editor_window   = this.editor_box.contentWindow;
			this.editor_document = this.editor_window.document;
		
			this.editor_write_contents( (typeof init_text == 'undefined' || ! init_text ?  this.text_obj.value : init_text), true );

			this.editor_document.editor_id = this.editor_id;
			this.editor_window.editor_id   = this.editor_id;
			this.editor_window.has_focus   = false;
			
			//-----------------------------------------
			// Kill tags
			//-----------------------------------------
			
			document.getElementById( this.editor_id + '_cmd_justifyfull' ).style.display  = 'none';
		};
		
		/**
		* RTE: NON MOZ
		* Set editor functions
		*/
		this.editor_set_functions = function()
		{
			this.editor_document.onmouseup = ips_editor_events.prototype.editor_document_onmouseup;
			this.editor_document.onkeyup   = ips_editor_events.prototype.editor_document_onkeyup;
			
			//-----------------------------------------
			// Make <p /> into <br />
			//-----------------------------------------

			this.editor_document.onkeydown = function()
			{
				if ( IPS_editor[ this.editor_id ].forum_fix_ie_newlines && IPS_editor[ this.editor_id ].is_ie && IPS_editor[ this.editor_id ].editor_window.event.keyCode == 13 ) 
				{
					var _test = new Array( 'Indent', 'Outdent', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'InsertOrderedList', 'InsertUnorderedList' );
			        
					for ( var i in _test )
					{
						if ( IPS_editor[ this.editor_id ].editor_window.document.queryCommandState( _test[ i ] ) )
						{
							return true;
						}
					}
					
					var sel   = IPS_editor[ this.editor_id ].editor_document.selection; 
			        var ts    = IPS_editor[ this.editor_id ].editor_document.selection.createRange();
					var t     = ts.htmlText.replace(/<p([^>]*)>(.*)<\/p>/i, '$2');
					
			        if ( (sel.type == "Text" || sel.type == "None") ) 
			        { 
			             ts.pasteHTML( "<br />" + t + "\n" ); 
			        } 
			        else 
			        { 
			             IPS_editor[ this.editor_id ].editor_document.innerHTML += "<br />\n"; 
			        } 

			        IPS_editor[ this.editor_id ].editor_window.event.returnValue = false; 
			        ts.select(); 
			        IPS_editor[ this.editor_id ].editor_check_focus();
			    }
			};
		
			this.editor_window.onblur  = ips_editor_events.prototype.editor_window_onblur;
			this.editor_window.onfocus = ips_editor_events.prototype.editor_window_onfocus;
		};
		
		/**
		* RTE: NON MOZ
		* Set context of buttons
		*/
		this.set_context = function( cmd )
		{
			//-----------------------------------------
			// Showing HTML?
			//-----------------------------------------

			if ( this._showing_html )
			{
				return false;
			}
			
			for (var i in buttons_update)
			{
				var obj = document.getElementById( this.editor_id + '_cmd_' + buttons_update[i] );
				
				if ( obj != null )
				{
					try
					{ 
						var state = this.editor_document.queryCommandState( buttons_update[i] );
					
						if ( obj.state != state )
						{
							obj.state = state;
							this.set_button_context( obj, (obj.cmd == cmd ? 'mouseover' : 'mouseout') );
						}
					}
					catch(error)
					{
						//alert( error );
					}
				}
			}
			
			//-----------------------------------------
			// Check and set font context
			//-----------------------------------------
			
			this.button_set_font_context();
			
			//-----------------------------------------
			// Check and set size context
			//-----------------------------------------
			
			this.button_set_size_context();
		};
		
		/**
		* RTE: NON MOZ
		* Button Context: FONT
		*
		* @var	string	fontstate
		*/
		this.button_set_font_context = function( font_state )
		{
			//-----------------------------------------
			// Showing HTML?
			//-----------------------------------------
			
			if ( this._showing_html )
			{
				return false;
			}
			
			if ( this.buttons['fontname'] )
			{ 
				if ( typeof font_state == 'undefined' )
				{
					font_state = this.editor_document.queryCommandValue('fontname');
				}
				switch ( font_state )
				{
					case '':
					{
						if ( ! this.is_ie && window.getComputedStyle )
						{
							font_state = this.editor_document.body.style.fontFamily;
						}
					}
					break;

					case null:
					{
						font_state = '';
					}
					break;
				}
				
				if ( font_state != this.font_state )
				{
					this.font_state = font_state;
					var fontword    = font_state;
					
					var commapos    = fontword.indexOf(",");

					if ( commapos != -1 )
					{
						fontword = fontword.substr(0, commapos);
					}
				
					fontword = fontword.toLowerCase();
					
					for (var i in this.fontoptions)
					{ 
						this.fontoptions[i].style.display = (i.toLowerCase() == fontword ? '' : 'none');
					}
				}
			}
		};

		/**
		* RTE: NON MOZ
		* Button Context: SIZE
		*
		* @var	string	sizestate
		*/
		this.button_set_size_context = function( size_state )
		{
			if ( this.buttons['fontsize'] )
			{
				if (typeof size_state == 'undefined')
				{
					size_state = this.editor_document.queryCommandValue('fontsize');
				}
				switch (size_state)
				{
					case null:
					case '':
					{
						if ( this.is_moz )
						{
							size_state = this.moz_convert_fontsize( this.editor_document.body.style.fontSize );
							
							if ( ! size_state )
							{
								size_state = '2';
							}
						}
					}
					break;
				}
				
				if ( size_state != this.size_state )
				{
					this.size_state = size_state;

					for (var i in this.sizeoptions)
					{
						this.sizeoptions[i].style.display = (i == this.size_state ? '' : 'none');
					}
				}
			}
		};

		/**
		* RTE: NON MOZ
		* Apply Formatting
		*
		* @var	string	Command
		* @var	string	Default Args
		* @var	string	Extra arguments
		*/
		this.apply_formatting = function(cmd, dialog, argument)
		{
			dialog   = (typeof dialog   == 'undefined' ? false : dialog);
			argument = (typeof argument == 'undefined' ? true  : argument);
			
			if ( this.is_ie && this.forum_fix_ie_newlines )
			{
				if ( cmd == 'justifyleft' || cmd == 'justifycenter' || cmd == 'justifyright' )
				{
					var _a  = cmd.replace( "justify", "" );

					this.wrap_tags_lite( "[" + _a + "]", "[/" + _a + "]" );
					return true;
				}
				else if ( cmd == 'outdent' || cmd == 'indent' || cmd == 'insertorderedlist' || cmd == 'insertunorderedlist' ) 
				{
					this.editor_check_focus();

					var sel = this.editor_document.selection;
					var ts  = this.editor_document.selection.createRange();

					var t   = ts.htmlText.replace(/<p([^>]*)>(.*)<\/p>/i, '$2');

					if ( (sel.type == "Text" || sel.type == "None") )
					{
						ts.pasteHTML( t + "<p />\n" );
					}
					else
					{
						this.editor_document.body.innerHTML += "<p />";
					}
				}
			}
		
			this.editor_document.execCommand( cmd, dialog, argument );
			return false;
		};
		
		/**
		* RTE: NON MOZ
		* Remove formatting : Overwrite
		* Unlink too
		*/
		this.removeformat = function(e)
		{
			this.apply_formatting( 'unlink'      , false, false );
			this.apply_formatting( 'removeformat', false, false );
			/*this.apply_formatting( 'killword', false, false );*/
			
			var text = this.get_selection();

			if ( text )
			{
				text = this.strip_html( text );
				text = this.strip_empty_html( text );
				text = text.replace( /\r/g, "" );
				text = text.replace( /\n/g, "<br />" );
				text = text.replace( /<!--(.*?)-->/g, "" );
				text = text.replace( /&lt;!--(.*?)--&gt;/g, "" );

				this.insert_text( text );
			}
		};
		
		/**
		* RTE: NON MOZ
		* Get Editor Contents
		*
		* @return string	Editor HTML
		*/
		this.editor_get_contents = function()
		{
			return this.editor_document.body.innerHTML;
		};

		/**
		* RTE: NON MOZ
		* Get Selected Text
		*
		* @return string	Returned HTML
		*/
		this.get_selection = function()
		{
			var rng = this._ie_cache ? this._ie_cache : this.editor_document.selection.createRange();
			
			if ( rng.htmlText )
			{
				return rng.htmlText;
			}
			else
			{
				var rtn = '';
				
				for (var i = 0; i < rng.length; i++)
				{
					rtn += rng.item(i).outerHTML;
				}
			}
			
			return rtn;
		};

		/**
		* RTE: NON MOZ
		* Insert HTML
		*
		* @var string	HTML to paste
		*/
		this.insert_text = function(text)
		{
			this.editor_check_focus();

			if ( typeof( this.editor_document.selection )    != 'undefined'
					  && this.editor_document.selection.type != 'Text'
				      && this.editor_document.selection.type != 'None' )
			{
				this.editor_document.selection.clear();
			}
			
			var sel = this._ie_cache ? this._ie_cache : this.editor_document.selection.createRange();
			
			sel.pasteHTML(text);
			
			sel.select();
			
			this._ie_cache = null;
		};
		
		/**
		* RTE: NON MOZ
		* Insert an emoticon
		*/
		this.insert_emoticon = function( emo_id, emo_image, emo_code, event )
		{
			try
			{
				// INIT
				var _emo_url  = global_rte_emoticons_url + "/" + emo_image;
				var _emo_html = ' <img src="' + _emo_url + '" border="0" alt="" style="vertical-align:middle" emoid="' + this.unhtmlspecialchars( emo_code ) + '" />';
				
				this.wrap_tags_lite( "" + _emo_html, "");
			}
			catch( error )
			{
				//alert( error );
			}
			
			if ( IPS_editor[ this.editor_id ].emoticon_window_id != '' && typeof( IPS_editor[ this.editor_id ].emoticon_window_id ) != 'undefined' )
			{
				IPS_editor[ this.editor_id ].emoticon_window_id.focus();
			}
		};
		
		/**
		* RTE: NON MOZ
		* Cancel HTML replacement
		*/
		this.togglesource_cancel = function()
		{
			this.togglesource( true );
		};
		
		/**
		* RTE: NON MOZ
		* Toggle HTML Source
		* Shows / Hides HTML Source stuff
		*
		* @param	boolean	Don't replace the HTML (from cancel button)
		* 
		*/
		this.togglesource = function( no_replace )
		{
			if ( this._showing_html )
			{
				//-----------------------------------------
				// Toggle ta off and eb on
				//-----------------------------------------
			
				var ta = document.getElementById( this.editor_id + '_htmlsource' );
				var ba = document.getElementById( this.editor_id + '_html_control_bar' );
				
				//-----------------------------------------
				// Convert...
				//-----------------------------------------
				
				if ( no_replace !== true )
				{
					this.editor_document.body.innerHTML = ta.value;
				}
				
				//-----------------------------------------
				// Show 'em
				//-----------------------------------------
				
				this.editor_box.style.display   = '';
				this.control_obj.style.display  = '';
				
				//-----------------------------------------
				// Kill em
				//-----------------------------------------
				
				ba.parentNode.removeChild(ba);
				ta.parentNode.removeChild(ta);
				
				//-----------------------------------------
				// Toggle to RTE mode
				//-----------------------------------------
				
				this.togglesource_post_show_html();
				this._showing_html = 0;
			}
			else
			{
				//-----------------------------------------
				// Toggle to HTML mode
				//-----------------------------------------
				
				this._showing_html = 1;
				
				this.togglesource_pre_show_html();
				
				//-----------------------------------------
				// Spawn new text area object from iframe
				//-----------------------------------------
				
				var textarea = document.createElement('textarea');
				
				var new_ta          = this.text_obj.parentNode.appendChild(textarea);
				new_ta.id           = this.editor_id + '_htmlsource';
				new_ta.className    = this.text_obj.className;
				new_ta.tabIndex     = 3;
				new_ta.style.width  = this.text_obj.style.width;
				
				new_ta.style.height = this.text_obj.style.height;
				
				new_ta.value        = this.clean_html( this.editor_get_contents() );
				new_ta.focus();
			
				//-----------------------------------------
				// Spawn new control bar
				//-----------------------------------------
				
				var new_div = document.createElement('DIV');
				
				new_div.id           = this.editor_id + '_html_control_bar';
				new_div.className    = this.control_obj.className;
				new_div.style.width  = this.control_obj.style.width;
				new_div.style.height = this.control_obj.style.height;
				new_div.style.paddingBottom = '8px';
				
				var savebutton           = document.createElement('input');
				savebutton.className     = 'rte-menu-button';
				savebutton.type          = 'button';
				savebutton.value         = ' Save HTML ';
				savebutton.cmd           = 'togglesource';
				savebutton.editor_id     = this.editor_id;
				savebutton.onclick       = ips_editor_events.prototype.button_onmouse_event;
				
				var cancelbutton           = document.createElement('input');
				cancelbutton.className     = 'rte-menu-button';
				cancelbutton.type          = 'button';
				cancelbutton.value         = ' CANCEL ';
				cancelbutton.cmd           = 'togglesource_cancel';
				cancelbutton.editor_id     = this.editor_id;
				cancelbutton.onclick       = ips_editor_events.prototype.button_onmouse_event;
				
				new_div.appendChild( savebutton   );
				new_div.appendChild( cancelbutton );
				
				this.control_obj.parentNode.appendChild( new_div );
				
				//-----------------------------------------
				// Hide old control bar
				//-----------------------------------------
				
				this.control_obj.style.display = 'none';
				
				//-----------------------------------------
				// Hide normal edit box
				//-----------------------------------------
				
				this.editor_box.style.display  = 'none';
				
				//-----------------------------------------
				// Fix up Toggle HTML button
				//-----------------------------------------
				
				this.buttons[ 'togglesource' ].state     = false;
				this.buttons[ 'togglesource' ].className = 'rte-normal';
				
				//-----------------------------------------
				// Reset bar
				//-----------------------------------------
				
				this.editor_check_focus();
				this.set_context();
			}
		};
		
		/**
		* RTE: NON MOZ
		* Pre-show-html
		*/
		this.togglesource_pre_show_html = function()
		{
		};
		
		/**
		* RTE: NON MOZ
		* Pre-show-html
		*/
		this.togglesource_post_show_html = function()
		{
		};
		
		/**
		* RTE: NON MOZ
		* Copies the HTML from the editor into the text area
		*/
		this.update_for_form_submit = function()
		{
			this.text_obj.value = this.editor_get_contents();

			return true;
		};
		
		// =============================================================================
		// IPB Override functions
		// =============================================================================
		
		this.___OPERA_FUNCTIONS = function() { };
		
		// =============================================================================
		// Overwrite default functions with Opera functions
		// =============================================================================
		
		if ( this.is_opera )
		{
			/**
			* RTE:  OPERA SPECIFIC
			* Set editor contents
			*
			* @param string 	initial text
			*/
			this._ORIGINAL_editor_set_content = this.editor_set_content;
			
			this.editor_set_content = function(initial_text)
			{
				this._ORIGINAL_editor_set_content(initial_text);
				
				//-----------------------------------------
				// Opera doesn't auto 100% the height, so
				// lets force the body to be 100% high
				//-----------------------------------------
				
				this.editor_document.body.style.height = '95%';
				this.editor_document.addEventListener( 'keypress', ips_editor_events.prototype.editor_document_onkeypress, true );
				
				//-----------------------------------------
				// Remove spellcheck button
				//-----------------------------------------
				
				document.getElementById( this.editor_id + '_cmd_spellcheck' ).style.display = 'none';
				
				this.hidden_objects[ this.editor_id + '_cmd_spellcheck' ] = 1;
				
				//-----------------------------------------
				// Kill tags
				//-----------------------------------------

				if ( this.use_bbcode )
				{
					document.getElementById( this.editor_id + '_cmd_justifyfull' ).style.display  = 'none';
					
					this.hidden_objects[ this.editor_id + '_cmd_justifyfull' ] = 1;
				}
				
				//-----------------------------------------
				// Go on.. cursor, flash you bastard
				//-----------------------------------------
				
				try
				{
					var _y = parseInt( window.pageYOffset );
					
					// Sometimes moves the focus to the RTE
					this.editor_document.execCommand("inserthtml", false, "-");
					this.editor_document.execCommand("undo"      , false, null);
					
					// Restore Y
					scroll( 0, _y );
				}
				catch(error)
				{
				}
			};

			/**
			* RTE:  OPERA SPECIFIC
			* Inserts text
			*
			* @param	string	string to insert
			*/
			this.insert_text = function(str)
			{
				this.editor_document.execCommand('insertHTML', false, str);
				
				//window.event.stopPropagation();
			};
			
			/**
			* RTE:  OPERA SPECIFIC
			* Get Selection
			* Overwrites default function for moz browsers
			*/
			this.get_selection = function()
			{
				var selection = this.editor_window.getSelection();
				
				this.editor_check_focus();
				
				var range = selection ? selection.getRangeAt(0) : this.editor_document.createRange();
				
				var lsserializer = document.implementation.createLSSerializer();
				
				return lsserializer.writeToString(range.cloneContents());
			};
			
			/**
			* RTE:  OPERA SPECIFIC
			* Insert an emoticon
			*/
			this.insert_emoticon = function( emo_id, emo_image, emo_code, event )
			{
				this.editor_check_focus();

				try
				{
					// INIT
					var _emo_url  = global_rte_emoticons_url + "/" + emo_image;
					
					this.editor_document.execCommand('InsertImage', false, _emo_url);

					var images = this.editor_document.getElementsByTagName('img');

					//----------------------------------
					// Sort through and fix emo
					//----------------------------------
					
					if ( images.length > 0 )
					{
						for ( var i = 0 ; i <= images.length ; i++ )
						{
							if ( images[i].src.match( new RegExp( _emo_url + "$" ) ) )
							{
								if ( ! images[i].getAttribute('emoid') )
								{
									images[i].setAttribute( 'emoid', this.unhtmlspecialchars( emo_code ) );
									images[i].setAttribute( 'border', '0'  );
									images[i].style.verticalAlign = 'middle';
								}
							}
						}
					}
				}
				catch(error)
				{
					//alert( error );
				}
				
				if ( IPS_editor[ this.editor_id ].emoticon_window_id != '' && typeof( IPS_editor[ this.editor_id ].emoticon_window_id ) != 'undefined' )
				{
					IPS_editor[ this.editor_id ].emoticon_window_id.focus();
				}
			};
			
			/**
			* RTE: OPERA SPECIFIC
			* Set editor functions
			*/
			this.editor_set_functions = function()
			{
				this.editor_document.addEventListener('mouseup', ips_editor_events.prototype.editor_document_onmouseup, true);
				this.editor_document.addEventListener('keyup'  , ips_editor_events.prototype.editor_document_onkeyup  , true);
				this.editor_window.addEventListener('focus'    , ips_editor_events.prototype.editor_window_onfocus    , true);
				this.editor_window.addEventListener('blur'     , ips_editor_events.prototype.editor_window_onblur     , true);
			};
		}
				
		this.___MOZ_FUNCTIONS = function() { };
		
		// =============================================================================
		// Overwrite default functions with Mozilla functions
		// =============================================================================
		
		if ( this.is_moz )
		{
			/**
			* RTE:  MOZ SPECIFIC
			* Pre-show-html
			*/
			this.togglesource_pre_show_html = function()
			{
				this.editor_document.designMode = 'off';
			};
			
			/**
			* RTE:  MOZ SPECIFIC
			* Pre-show-html
			*/
			this.togglesource_post_show_html = function()
			{
				this.editor_document.designMode = 'on';
			};
			
			/**
			* RTE:  MOZ SPECIFIC
			* Set editor contents
			*
			* @param string 	initial text
			*/
			this._ORIGINAL_editor_set_content = this.editor_set_content;
			
			this.editor_set_content = function(initial_text)
			{
				this._ORIGINAL_editor_set_content(initial_text);
				
				this.editor_document.addEventListener( 'keypress', ips_editor_events.prototype.editor_document_onkeypress, true );
				
				//-----------------------------------------
				// Remove spellcheck button
				//-----------------------------------------
				
				document.getElementById( this.editor_id + '_cmd_spellcheck' ).style.display = 'none';
				
				this.hidden_objects[ this.editor_id + '_cmd_spellcheck' ] = 1;
				
				//-----------------------------------------
				// Kill tags
				//-----------------------------------------

				if ( this.use_bbcode )
				{
					document.getElementById( this.editor_id + '_cmd_justifyfull' ).style.display  = 'none';
					
					this.hidden_objects[ this.editor_id + '_cmd_justifyfull' ] = 1;
				}
				
				//-----------------------------------------
				// Go on.. cursor, flash you bastard
				//-----------------------------------------
				
				try
				{
					var _y = parseInt( window.pageYOffset );
					
					// Sometimes moves the focus to the RTE
					this.editor_document.execCommand("inserthtml", false, "-");
					this.editor_document.execCommand("undo"      , false, null);
					
					// Restore Y
					scroll( 0, _y );
				}
				catch(error)
				{
				}
				
			};

			/**
			* RTE:  MOZ SPECIFIC
			* Translate CSS fontSize to HTML Font Size
			*
			* @param	string	Raw size
			* @return	string	Formatted size
			*/
			this.moz_convert_fontsize = function( in_size )
			{
				switch ( in_size)
				{
					case '7.5pt':
					case '10px': return 1;
					case '10pt': return 2;
					case '12pt': return 3;
					case '14pt': return 4;
					case '18pt': return 5;
					case '24pt': return 6;
					case '36pt': return 7;
					default:     return '';
				}
			};

			/**
			* RTE:  MOZ SPECIFIC
			* Apply Formatting
			* Kills CSS formatting for Moz browsers
			*/
			this._ORIGINAL_apply_formatting = this.apply_formatting;
			
			this.apply_formatting = function(cmd, dialog, arg)
			{
				// Moz fix to allow list button click before clicking in RTE
				if ( cmd != 'redo' )
				{
					this.editor_document.execCommand("inserthtml", false, "-");
					this.editor_document.execCommand("undo"      , false, null);
				}
				
				this.editor_document.execCommand( 'useCSS', false, true );
				return this._ORIGINAL_apply_formatting(cmd, dialog, arg);
			};

			
			/**
			* RTE:  MOZ SPECIFIC
			* Get Selection
			* Overwrites default function for moz browsers
			*/
			this.get_selection = function()
			{
				var selection = this.editor_window.getSelection();
				
				this.editor_check_focus();
				
				var range     = selection ? selection.getRangeAt(0) : this.editor_document.createRange();
				
				return this.moz_read_nodes( range.cloneContents(), false );
			};

			/**
			* RTE:  MOZ SPECIFIC
			* Inserts text
			*
			* @param	string	string to insert
			*/
			this.insert_text = function(str, len)
			{
				fragment = this.editor_document.createDocumentFragment();
				holder   = this.editor_document.createElement('span');
				
				holder.innerHTML = str;

				while (holder.firstChild)
				{
					fragment.appendChild( holder.firstChild );
				}
				
				var my_length = parseInt( len ) > 0 ? len : 0;

				this.moz_insert_node_at_selection( fragment, my_length );
			};
			
			/**
			* RTE:  MOZ SPECIFIC
			* Insert an emoticon
			*/
			this.insert_emoticon = function( emo_id, emo_image, emo_code, event )
			{
				this.editor_check_focus();

				try
				{
					// INIT
					var _emo_url  = global_rte_emoticons_url + "/" + emo_image;
					
					this.editor_document.execCommand('InsertImage', false, _emo_url);
					
					var images = this.editor_document.getElementsByTagName('img');

					//----------------------------------
					// Sort through and fix emo
					//----------------------------------
					
					if ( images.length > 0 )
					{
						for ( var i = 0 ; i <= images.length ; i++ )
						{
							if ( images[i].src.match( new RegExp( _emo_url + "$" ) ) )
							{
								if ( ! images[i].getAttribute('emoid') )
								{
									images[i].setAttribute( 'emoid', this.unhtmlspecialchars( emo_code ) );
									images[i].setAttribute( 'border', '0'  );
									images[i].style.verticalAlign = 'middle';
								}
							}
						}
					}
				}
				catch(error)
				{
					//alert( error );
				}
				
				if ( IPS_editor[ this.editor_id ].emoticon_window_id != '' && typeof( IPS_editor[ this.editor_id ].emoticon_window_id ) != 'undefined' )
				{
					IPS_editor[ this.editor_id ].emoticon_window_id.focus();
				}
			};
			
			/**
			* RTE:  MOZ SPECIFIC
			* Set editor functions
			*/
			this.editor_set_functions = function()
			{
				this.editor_document.addEventListener('mouseup', ips_editor_events.prototype.editor_document_onmouseup, true);
				this.editor_document.addEventListener('keyup'  , ips_editor_events.prototype.editor_document_onkeyup  , true);
				this.editor_window.addEventListener('focus'    , ips_editor_events.prototype.editor_window_onfocus    , true);
				this.editor_window.addEventListener('blur'     , ips_editor_events.prototype.editor_window_onblur     , true);
				
				this.editor_document.addEventListener( 'keydown', ips_editor_events.prototype.editor_document_onkeydown, true);
					
			};

			/**
			* RTE:  MOZ SPECIFIC
			* Add Range (Mozilla)
			*/
			this.moz_add_range = function(node, text_length)
			{
				this.editor_check_focus();

				var sel   = this.editor_window.getSelection();
				var range = this.editor_document.createRange();
				
				range.selectNodeContents(node);
				
				if ( text_length )
				{
					range.setEnd(  node, text_length);
					range.setStart(node, text_length);
				}
				
				sel.removeAllRanges();
				sel.addRange(range);
			};

			/**
			* RTE:  MOZ SPECIFIC
			* Read Nodes (Mozilla)
			*/
			this.moz_read_nodes = function(root, toptag)
			{
				var html      = "";
				var moz_check = /_moz/i;

				switch (root.nodeType)
				{
					case Node.ELEMENT_NODE:
					case Node.DOCUMENT_FRAGMENT_NODE:
					{
						var closed;
						if (toptag)
						{
							closed   = ! root.hasChildNodes();
							html     = '<' + root.tagName.toLowerCase();
							var attr = root.attributes;
							for (var i = 0; i < attr.length; ++i)
							{
								var a = attr.item(i);
								if (!a.specified || a.name.match(moz_check) || a.value.match(moz_check))
								{
									continue;
								}

								html += " " + a.name.toLowerCase() + '="' + a.value + '"';
							}
							html += closed ? " />" : ">";
						}
						for (var i = root.firstChild; i; i = i.nextSibling)
						{
							html += this.moz_read_nodes(i, true);
						}
						if (toptag && !closed)
						{
							html += "</" + root.tagName.toLowerCase() + ">";
						}
					}
					break;

					case Node.TEXT_NODE:
					{
						html = this.htmlspecialchars(root.data);
					
					}
					break;
				}

				return html;
			};
			
			/**
			* RTE:  MOZ SPECIFIC
			* Fix to stop Moz from inserting text in the HTML
			* portion of the document
			*/
			this.moz_goto_parent_then_body = function( n )
			{
				var o = n;

				while (n.parentNode != null && n.parentNode.nodeName == 'HTML')
				{
					n = n.parentNode;
				}

				if (n)
				{
					for ( var c = 0; c < n.childNodes.length; c++ )
					{
						if ( n.childNodes[c].nodeName == 'BODY' )
						{
							return n.childNodes[c];
						}
					}
				}

				return o;
			};

			/**
			* RTE:  MOZ SPECIFIC
			* Insert Node at Selection (Mozilla)
			*/
			this.moz_insert_node_at_selection = function(text, text_length)
			{
				this.editor_check_focus();

				var sel   = this.editor_window.getSelection();
				var range = sel ? sel.getRangeAt(0) : this.editor_document.createRange();
				
				sel.removeAllRanges();
				range.deleteContents();

				var node = range.startContainer;
				var pos  = range.startOffset;
				
				text_length = text_length ? text_length : 0;
				
				if ( node.nodeName == 'HTML' )
				{
					node = this.moz_goto_parent_then_body( node );
				}
				
				switch (node.nodeType)
				{
					case Node.ELEMENT_NODE:
					{ 
						if (text.nodeType == Node.DOCUMENT_FRAGMENT_NODE)
						{
							selNode = text.firstChild;
						}
						else
						{
							selNode = text;
						}
						node.insertBefore(text, node.childNodes[pos]);
						this.moz_add_range(selNode, text_length);
					}
					break;

					case Node.TEXT_NODE:
					{
						if (text.nodeType == Node.TEXT_NODE)
						{
							var text_length = pos + text.length;
							node.insertData(pos, text.data);
							range = this.editor_document.createRange();
							range.setEnd(node, text_length);
							range.setStart(node, text_length);
							sel.addRange(range);
						}
						else
						{
							node = node.splitText(pos);
							var selNode;
							if (text.nodeType == Node.DOCUMENT_FRAGMENT_NODE)
							{
								selNode = text.firstChild;
							}
							else
							{
								selNode = text;
							}
							node.parentNode.insertBefore(text, node);
							this.moz_add_range(selNode, text_length);
						}
					}
					break;
				}
			};
		}
	}
	else
	{
		this.___STD_FUNCTIONS = function() { };
		
		// =============================================================================
		// Standard Editor
		// =============================================================================
		
		/**
		* STD:
		* Writes contents to the <textarea>
		*
		* @param	object	<textarea>
		* @param	string	Initial text
		*/
		this.editor_write_contents = function(text)
		{
			this.text_obj.value = text;
		};

		/**
		* STD:
		* Put the text into the editor
		*/
		this.editor_set_content = function( init_text )
		{
			var iframe = this.text_obj.parentNode.getElementsByTagName('iframe')[0];
			
			if ( iframe )
			{
				this.text_obj.style.display = '';
				this.text_obj.style.width   = iframe.style.width;
				this.text_obj.style.height  = iframe.style.height;

				iframe.style.width  = '0px';
				iframe.style.height = '0px';
				iframe.style.border = 'none';
			}

			this.editor_window   = this.text_obj;
			this.editor_document = this.text_obj;
			this.editor_box      = this.text_obj;

			if ( typeof init_text != 'undefined' )
			{
				this.editor_write_contents( init_text );
			}

			this.editor_document.editor_id = this.editor_id;
			this.editor_window.editor_id   = this.editor_id;
			
			//-----------------------------------------
			// Kill off buttons...
			//-----------------------------------------
			
			if ( ! this.is_ie )
			{
				document.getElementById( this.editor_id + '_cmd_spellcheck' ).style.display   = 'none';
				
				this.hidden_objects[ this.editor_id + '_cmd_spellcheck' ] = 1;
			}
			
			document.getElementById( this.editor_id + '_cmd_togglesource' ).style.display = 'none';
			document.getElementById( this.editor_id + '_cmd_outdent' ).style.display      = 'none';
			document.getElementById( this.editor_id + '_cmd_justifyfull' ).style.display  = 'none';
			
			this.hidden_objects[ this.editor_id + '_cmd_togglesource'] = 1;
			this.hidden_objects[ this.editor_id + '_cmd_outdent' ]     = 1;
			this.hidden_objects[ this.editor_id + '_cmd_justifyfull' ] = 1;
		};

		/**
		* STD:
		* Init Editor Functions
		*/
		this.editor_set_functions = function()
		{
			if ( this.editor_document.addEventListener )
			{
				this.editor_document.addEventListener('keypress', ips_editor_events.prototype.editor_document_onkeypress, false);
			}

			this.editor_window.onfocus = ips_editor_events.prototype.editor_window_onfocus;
			this.editor_window.onblur  = ips_editor_events.prototype.editor_window_onblur;
		};

		/**
		* STD:
		* Set Context
		*/
		this.set_context = function()
		{
		};
		
		/**
		* STD:
		* Remove formatting (ugly)
		*/
		this.removeformat = function()
		{
			var text = this.get_selection();
			
			if ( text )
			{
				text = this.strip_html( text );
				this.insert_text( text );
			}
		};

		/**
		* STD:
		* Apply formatting
		*/
		this.apply_formatting = function(cmd, dialog, argument)
		{
			// undo & redo array pops

			switch (cmd)
			{
				case 'bold':
				case 'italic':
				case 'underline':
				{
					this.wrap_tags(cmd.substr(0, 1), false);
					return;
				}

				case 'justifyleft':
				case 'justifycenter':
				case 'justifyright':
				{
					this.wrap_tags(cmd.substr(7), false);
					return;
				}

				case 'indent':
				{
					this.wrap_tags(cmd, false);
					return;
				}
				
				case 'createlink':
				{
					var sel = this.get_selection();
					
					if (sel)
					{
						this.wrap_tags('url', argument);
					}
					else
					{
						this.wrap_tags('url', argument, argument);
					}
					return;
				}
				
				case 'fontname':
				{
					this.wrap_tags('font', argument);
					return;
				}

				case 'fontsize':
				{
					this.wrap_tags('size', argument);
					return;
				}

				case 'forecolor':
				{
					this.wrap_tags('color', argument);
					return;
				}
				
				case 'backcolor':
				{
					this.wrap_tags('background', argument);
					return;
				}

				case 'insertimage':
				{
					this.wrap_tags('img', false, argument);
					return;
				}
				
				case 'strikethrough':
				{
					this.wrap_tags('s', false);
					return;
				}
				
				case 'superscript':
				{
					this.wrap_tags('sup', false);
					return;
				}
				
				case 'subscript':
				{
					this.wrap_tags('sub', false);
					return;
				}

				case 'removeformat':
				return;
			}
		};
		

		/**
		* STD:
		* Get Editor Contents
		*/
		this.editor_get_contents = function()
		{
			return this.editor_document.value;
		};

		/**
		* STD:
		* Get Selected Text
		*/
		this.get_selection = function()
		{
			if ( typeof(this.editor_document.selectionStart) != 'undefined' )
			{
				return this.editor_document.value.substr(this.editor_document.selectionStart, this.editor_document.selectionEnd - this.editor_document.selectionStart);
			}
			else if ( ( document.selection && document.selection.createRange ) || this._ie_cache )
			{
				return this._ie_cache ? this._ie_cache.text : document.selection.createRange().text;
			}
			else if ( window.getSelection )
			{
				return window.getSelection() + '';
			}
			else
			{
				return false;
			}
		};

		/**
		* STD:
		* Insert HTML
		*/
		this.insert_text = function(text)
		{
			this.editor_check_focus();

			if (typeof(this.editor_document.selectionStart) != 'undefined')
			{
				var open = this.editor_document.selectionStart + 0;
				var st   = this.editor_document.scrollTop;
				var end  = open + text.length;
				
				/* Opera doesn't count the linebreaks properly for some reason */
				if( this.is_opera )
				{
					var opera_len = text.match( /\n/g );

					try
					{
						end += parseInt(opera_len.length);
					}
					catch(e)
					{
					}
				}
				
				this.editor_document.value = this.editor_document.value.substr(0, this.editor_document.selectionStart) + text + this.editor_document.value.substr(this.editor_document.selectionEnd);

				// Don't adjust selection if we're simply adding <b></b>, etc
				if ( ! text.match( new RegExp( "\\" + this.open_brace + "(\\S+?)" + "\\" + this.close_brace + "\\" + this.open_brace + "/(\\S+?)" + "\\" + this.close_brace ) ) )
				{
					this.editor_document.selectionStart = open;
					this.editor_document.selectionEnd   = end;
					this.editor_document.scrollTop      = st;
				}
			}
			else if ( ( document.selection && document.selection.createRange ) || this._ie_cache )
			{
				var sel  = this._ie_cache ? this._ie_cache : document.selection.createRange();
				sel.text = text.replace(/\r?\n/g, '\r\n');
				sel.select();
			}
			else
			{
				this.editor_document.value += text;
			}
			
			this._ie_cache = null;
		};
		
		/**
		* STD:
		* Insert an emoticon
		*/
		this.insert_emoticon = function( emo_id, emo_image, emo_code, event )
		{
			emo_code = this.unhtmlspecialchars( emo_code );
			
			this.wrap_tags_lite( " " + emo_code, " ");
			
			if ( this.is_ie )
			{
				if ( IPS_editor[ this.editor_id ].emoticon_window_id != '' && typeof( IPS_editor[ this.editor_id ].emoticon_window_id ) != 'undefined' )
				{
					IPS_editor[ this.editor_id ].emoticon_window_id.focus();
				}
			}
		};
		
		/**
		* STD:
		* Insert ordered list
		* @param	object	Event object
		*/
		this.insertorderedlist = function(e)
		{
			this.insertlist( 'ol');
		};

		/**
		* STD:
		* Insert Unordered List
		*/
		this.insertunorderedlist = function(e)
		{
			this.insertlist( 'ul');
		};

		/**
		* STD:
		* Insert List
		* @param string	List type (ol|ul)
		*/
		this.insertlist = function( list_type )
		{
			var open_tag;
			var close_tag;
			var item_open_tag  = '<li>';
			var item_close_tag = '</li>';
			var regex          = '';
			var all_add        = '';
			
			if ( this.use_bbcode )
			{
				regex          = new RegExp('([\r\n]+|^[\r\n]*)(?!\\[\\*\\]|\\[\\/?list)(?=[^\r\n])', 'gi');
				open_tag       = list_type == 'ol' ? '[list=1]\n' : '[list]\n';
				close_tag      = '[/list]';
				item_open_tag  = '[*]';
				item_close_tag = '';
			}
			else
			{
				regex     = new RegExp('([\r\n]+|^[\r\n]*)(?!<li>|<\\/?ol|ul)(?=[^\r\n])', 'gi');
				open_tag  = list_type == 'ol'  ? '<ol>\n'  : '<ul>\n';
				close_tag = list_type == 'ol'  ? '</ol>\n' : '</ul>\n';
			}
			
			if ( text = this.get_selection() )
			{
				text = open_tag + text.replace( regex, "\n" + item_open_tag + '$1' + item_close_tag ) + '\n' + close_tag;
				
				if ( this.use_bbcode )
				{
					text = text.replace( new RegExp( '\\[\\*\\][\r\n]+', 'gi' ), item_open_tag );
				}
				
				this.insert_text( text );
			}
			else
			{
				if ( this.is_moz )
				{
					this.insert_text( open_tag + close_tag );
				
					while ( val = prompt( ipb_global_lang['editor_enter_list'], '') )
					{
						this.insert_text( open_tag + all_add + item_open_tag + val + item_close_tag + '\n' + close_tag );
						
						all_add += item_open_tag + val + item_close_tag + '\n';
					}
				}
				else
				{
					var to_insert = open_tag;
					//this.insert_text( open_tag );

					while ( val = prompt( ipb_global_lang['editor_enter_list'], '') )
					{
						//this.insert_text( item_open_tag + val + item_close_tag + '\n' );
						to_insert += item_open_tag + val + item_close_tag + '\n';
					}

					//this.insert_text( close_tag );
					to_insert += close_tag;
					
					this.insert_text( to_insert );
				}
			}
		};
		
		/**
		* STD:
		* Over-ride unlink..
		*/
		this.unlink = function()
		{
			var text       = this.get_selection();
			var link_regex = '';
			var link_text  = '';
			
			if ( text !== false )
			{
				if ( text.match( link_regex ) )
				{ 
					text = ( this.use_bbcode ) ? text.replace( /\[url=([^\]]+?)\]([^\[]+?)\[\/url\]/ig, "$2" )
											   : text.replace( /<a href=['\"]([^\"']+?)['\"]([^>]+?)?>(.+?)<\/a>/ig, "$3" );
				}
				
				this.insert_text( text );
			}
		};
		
		/**
		* STD:
		* Overwrite UNDO
		*/
		this.undo = function()
		{
			this.history_record_state( this.editor_get_contents() );
			
			this.history_time_shift( -1 );
			
			if ( ( text = this.history_fetch_recording() ) !== false )
			{
				this.editor_document.value = text;
			}
		};
		
		/**
		* STD:
		* Overwrite REDO
		*/
		this.redo = function()
		{
			this.history_time_shift( 1 );
			
			if ( ( text = this.history_fetch_recording() ) !== false )
			{
				this.editor_document.value = text;
			}
		};
		
		/**
		* STD:
		* Prepare Form For Submit
		*/
		this.update_for_form_submit = function(subjecttext, minchars)
		{
			return true;
		};
	}
	
	this.___SAFARI_FUNCTIONS = function() { };
	
	// =============================================================================
	// Overwrite default functions with Safari functions
	// =============================================================================
	
	if ( this.is_safari )
	{
		//-----------------------------------------
		// Remove toggle editor button
		//-----------------------------------------
		
		try
		{
			document.getElementById( this.editor_id + '_cmd_switcheditor' ).style.display = 'none';
		}
		catch(error)
		{
			
		}
	}
	
	// =============================================================================
	// Overwrite default functions with IPB functions
	// =============================================================================
	
	this.___IPB_FUNCTIONS = function() { };
	
	/**
	* STD:
	* Create link override
	*/
	this.createlink = function( e )
	{
		var _text = this.get_selection();
		_text     = _text.replace( /\n|\r|<br \/>/g, '' );
	
		if ( _text.match( /(<a href|\[url)/ig ) )
		{
			this.format_text( e, "unlink", false );
		}
		else
		{
			var _url  = prompt( ipb_global_lang['editor_enter_url'], 'http://' );
			
			if ( ! _url || _url == null || _url == 'http://' )
			{
				return false;
			}
			
			_text     = _text ? _text : prompt( ipb_global_lang['editor_enter_title'], ipb_global_lang['visit_my_website'] );
			
			if( !_text || _text == null )
			{
				return false;
			}
						
			this.wrap_tags( 'url', _url, _text);
		}
	};
	
	/**
	* STD:
	* Create email link override
	*/
	this.insertemail = function( e )
	{
		var _text = this.get_selection();
		_text     = _text.replace( /\n|\r|<br \/>/g, '' );
	
		if ( _text.match( /(<a href|\[email)/ig ) )
		{
			this.format_text( e, "unlink", false );
		}
		else
		{
			var _url  = prompt( ipb_global_lang['editor_enter_email'], '' );
			
			if( !_url || _url == null )
			{
				return false;
			}
						
			_text     = _text ? _text : prompt( ipb_global_lang['editor_enter_title'] );
			
			if ( !_text || _text == null )
			{
				return false;
			}
						
			this.wrap_tags( 'email', _url, _text);
		}
	};

	/**
	* STD:
	* Insert image override
	*/
	this.insertimage = function()
	{
		var _text = this.get_selection();
		_text     = _text.replace( /\n|\r|<br \/>/g, '' );
		
		// Did they highlight an html image in the RTE?
		// If so, it will pull the whole <img ...> tag instead of just the url
		if( this.is_rte )
		{
			if( _text.match( /<img(.+?)src=['"](.+?)["'](.*?)>/g ) )
			{
				_text = _text.replace( /<img(.+?)src=['"](.+?)["'](.*?)>/g, '$2' );
			}
		}
		
		var _url  = prompt( ipb_global_lang['editor_enter_image'], _text ? _text : "http://" );
		
		if ( ! _url || _url == null || _url == 'http://' )
		{
			return false;
		}
					
		if ( ! this.is_rte )
		{
			this.wrap_tags( 'img', false, _url );
		}
		else
		{
			this.wrap_tags( 'img', _url, '' );
		}
	};
/**
	* STD:
	* Insert video override
	*/
	this.insertvideo = function()
	{
		this.wrap_tags_lite(  '[video]', '[/video]', 0)
	};
	/**
	* STD:
	* IPB Quote override
	*/
	this.ipb_quote = function()
	{
		this.wrap_tags_lite(  '[quote]', '[/quote]', 0)
	};

	/**
	* STD:
	* IPB code override
	*/
	this.ipb_code = function()
	{
		this.wrap_tags_lite(  '[code]', '[/code]', 0)
	};
	
	this.init();
}

// =============================================================================
// Text editor event functions
// =============================================================================

/**
* Class initialization
*/
function ips_editor_events()
{
}

/**
* Handles mouse events for buttons
*
* @param	event Event Object
*/
ips_editor_events.prototype.button_onmouse_event = function(e)
{
	if ( is_ie )
	{
		e = ipsclass.cancel_bubble(e, true);
	}
	
	if ( e.type == 'click' )
	{
		IPS_editor[this.editor_id].format_text(e, this.cmd, false, true);
	}
	
	IPS_editor[this.editor_id].set_button_context(this, e.type);
};

/**
* Handles mouse events for special items menu
*
* @param	event Event Object
*/
ips_editor_events.prototype.special_onmouse_event = function(e)
{
	e = ipsclass.cancel_bubble(e, true);
	
	if (e.type == 'click')
	{
		if ( ! this.loader_key )
		{
			IPS_editor[this.editor_id].format_text(e, this.cmd, false, true);
			ipsmenu.close();
		}
		else
		{
			IPS_editor[this.editor_id].module_load(this, e, this.loader_key);
		}
	}
	
	IPS_editor[this.editor_id].set_button_context(this, e.type, 'menu');
};

/**
* Sets the focus for the editor window
*
* @param	event Event Object
*/
ips_editor_events.prototype.editor_window_onfocus = function(e)
{
	this.has_focus = true;
};

/**
* Sets unfocus for the editor window
*
* @param	event Event Object
*/

ips_editor_events.prototype.editor_window_onblur = function(e)
{
	this.has_focus = false;
};

/**
* Closes menu and re-sets menu context
*
* @param	event Event Object
*/
ips_editor_events.prototype.editor_document_onmouseup = function(e)
{
	// Moz fix.. sometimes this.editor_id is undefined
	// for reasons I don't care to research.
	// Bastard browsers
	
	try
	{
		if ( typeof( this.editor_id == 'undefined' ) && is_moz )
		{
			this.editor_id = e.view.editor_id;
		}
	}
	catch(me)
	{
	}
	
	IPS_editor[this.editor_id].set_context();
	menu_action_close();
};

/**
* Handles editor document key-up event
*
* @param	event Event Object
*/
ips_editor_events.prototype.editor_document_onkeyup = function(e)
{
	IPS_editor[this.editor_id].set_context();
};

/**
* Handles editor document key-press event
*
* @param	event Event Object
*/
ips_editor_events.prototype.editor_document_onkeypress = function(e)
{	
	if ( e.ctrlKey )
	{
		switch (String.fromCharCode(e.charCode).toLowerCase())
		{
			case 'b': cmd = 'bold';      break;
			case 'i': cmd = 'italic';    break;
			case 'u': cmd = 'underline'; break;
			default: return;
		}

		e.preventDefault();
		
		IPS_editor[this.editor_id].apply_formatting(cmd, false, null);
		
		return false;
	}
};

/**
* Handles mouse events for pop-up menu buttons
*
* @param	event Event Object
*/
ips_editor_events.prototype.popup_button_onmouseevent = function(e)
{
	e = ipsclass.cancel_bubble(e, true);
	
	if (e.type == 'click')
	{ 
		this._onclick(e);
		IPS_editor[this.editor_id].set_menu_context(this, 'mouseover');
	}
	else
	{
		IPS_editor[this.editor_id].set_menu_context(this, e.type);
	}
};



/**
* Shows the pop-up menu
* First, dynamically writes menu - then passes to ipsmenu class to show
*
* @param	Object Object
*/
ips_editor_events.prototype.popup_button_show = function(obj)
{
	if (typeof IPS_editor[obj.editor_id].popups[obj.cmd] == 'undefined' || IPS_editor[obj.editor_id].popups[obj.cmd] == null)
	{
		IPS_editor[obj.editor_id].init_editor_menu(obj);
	}
	this._open(obj);
};

/**
* Handles mouse events for pop-up menu options
*
* @param	event Event Object
*/
ips_editor_events.prototype.menu_option_onmouseevent = function(e)
{
	e = ipsclass.cancel_bubble(e, true);
	
	IPS_editor[this.editor_id].set_button_context(this, e.type, 'menu');
};

/**
* Handles font size/face menu click
*
* @param	event Event Object
*/
ips_editor_events.prototype.font_format_option_onclick = function(e)
{
	IPS_editor[this.editor_id].format_text(e, this.cmd, this.firstChild.innerHTML);
	ipsmenu.close();
};

/**
* Handles emoticon click
*
* @param	event Event Object
*/
ips_editor_events.prototype.emoticon_onclick = function(e)
{
	e = ipsclass.cancel_bubble(e, true);

	IPS_editor[this.editor_id].insert_emoticon( this.emo_id, this.emo_image, this.emo_code, e );
	ipsmenu.close();
};

/**
* Handles a click on a color cell from the color pop-up menu
*
* @param	event Event Object
*/
ips_editor_events.prototype.color_cell_onclick = function(e)
{
	IPS_editor[this.editor_id].format_text(e, this.cmd, this.colorname);
	ipsmenu.close();
};

ips_editor_events.prototype.editor_document_onkeydown = function(e)
{
	/*if ( e.keyCode == 35 && e.CONTROL_MASK == 2 )
	{
		
		
	}*/
};
