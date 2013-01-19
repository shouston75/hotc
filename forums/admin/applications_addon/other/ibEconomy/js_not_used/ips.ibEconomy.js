/**
 * (e32) ibEconomy
 * ibEconomy Main JS
 * @ Global
 * +  IPB Sidebar
 * +  IPB Balloon
 * +  IPB "Pop-up"
 */

var _ibEconomy = window.IPBoard;

_ibEconomy.prototype.ibEconomy = {

	init: function()
	{
		Debug.write( 'Initializing ips.ibEconomy.js' );

		document.observe("dom:loaded", function(){
			ipb.ibEconomy.initEvents();
			ipb.ibEconomy.initSidebar();
		});
	},
	initEvents: function()
	{
		// Delegate our item popup links
		ipb.delegate.register(".__statuspop", ipb.ibEconomy.statuspopPopup);
		ipb.delegate.register(".__item", ipb.ibEconomy.itemPopup);
		ipb.delegate.register(".ibEconomy_popup", ipb.ibEconomy.bigPopForm);
		if( $('item_checkall') )
		{
			$('item_checkall').observe('click', ipb.ibEconomy.checkAllItems );
		}
		
		ipb.delegate.register('.item_check', ipb.ibEconomy.checkSingleBox );
	},
	
	/* ------------------------------ */
	/**
	 * Sets up the sidebar
	*/
	initSidebar: function()
	{
		if( !$('index_stats') )
		{
			return false;
		}

		if( $('index_stats').visible() )
		{
			Debug.write("Stats are visible");
			$('open_sidebar').hide();
			$('close_sidebar').show();
		}
		else
		{
			Debug.write("Stats aren't visible");
			$('open_sidebar').show();
			$('close_sidebar').hide();
		}
		
		ipb.ibEconomy.animating = false;
		
		if( $('close_sidebar') )
		{
			$('close_sidebar').observe('click', function(e){
				if( ipb.ibEconomy.animating ){ Event.stop(e); return; }
				
				ipb.ibEconomy.animating = true;		
				new Effect.Fade( $('index_stats'), {duration: 0.4, afterFinish: function(){
					new Effect.Morph( $('categories'), { style: 'no_sidebar', duration: 0.4, afterFinish: function(){
						ipb.ibEconomy.animating = false;
					 } } );
				} } );
				
				Event.stop(e);
				$('close_sidebar').toggle();
				$('open_sidebar').toggle();
				ipb.Cookie.set('hide_eco_sidebar', '1', 1);
			});
		}
		if( $('open_sidebar') )
		{
			$('open_sidebar').observe('click', function(e){
				if( ipb.ibEconomy.animating ){ Event.stop(e); return; }
				
				ipb.ibEconomy.animating = true;
				
				new Effect.Morph( $('categories'), { style: 'with_sidebar', duration: 0.4, afterFinish: function(){
					$('categories').removeClassName('with_sidebar').removeClassName('no_sidebar');
					new Effect.Appear( $('index_stats'), { duration: 0.4, queue: 'end', afterFinish: function(){
						ipb.ibEconomy.animating = false;
				 	} } );
				} } );
				
				Event.stop(e);
				$('close_sidebar').toggle();
				$('open_sidebar').toggle();
				ipb.Cookie.set('hide_eco_sidebar', '0', 1);
			});
		}
	},

	/* ------------------------------ */
	/**
	 * Itty bitty investment item popup
	*/	
	itemPopup: function( e, elem )
	{
		Event.stop(e);
		
		var sourceid = elem.identify();
		var item = $( elem ).className.match('__id([0-9]+)');
        var type = $( elem ).className.match('__type_([a-z]+)_([A-Za-z]+)');
		if( item == null || Object.isUndefined( item[1] ) ){ Debug.error("Error showing popup"); return; }
		var popid = 'popup_' + item[1] + '_item';
		var _url 		= ipb.vars['base_url'] + '&app=ibEconomy&module=ajax&secure_key=' + ipb.vars['secure_hash'] + '&section=' + type[1] + '&id=' + item[1] + '&type=' + type[2];
		
		ipb.namePops[ item ]	 = new ipb.Popup( popid, {
			 												type: 'balloon',
			 												ajaxURL: _url,
			 												stem: true,
															hideAtStart: false,
			 												attach: { target: elem, position: 'auto' },
			 												w: '400px'
														});
	},

	/* ------------------------------ */
	/**
	 * Large popup---work to do
	*/	
	bigPopForm: function(e, target)
	{
	 	todo = target.id.match( /do_([0-9a-z]+)/ );

		// Destroy popup if it exists
		if( $('pm_popup_popup') )
		{
			ipb.ibEconomy.popupObj.getObj().remove();
		}

		
		// Pre-make popup
		ipb.ibEconomy.popupObj = new ipb.Popup('pm_popup', { type: 'pane', modal: true, hideAtStart: true, w: '600px' } );
                                                                
		var popup = ipb.ibEconomy.popupObj;

		// Lets get the form
		new Ajax.Request( ipb.vars['base_url'] + "&app=ibEconomy&module=ajax&secure_key=" + ipb.vars['secure_hash'] + '&section=popform&do=' + todo[1],
						{
							method: 'post',
							evalJSON: 'force',
							onSuccess: function(t)
							{				
								popup.update( t.responseJSON['success'] );
									popup.positionPane();	
									popup.show();
									
							}
						}
					);
		
		
		Event.stop(e);
	},

	/* ------------------------------ */
	/**
	 * Checks all messages
	 * 
	 * @param	{event}		e		The event
	*/
	checkAllItems: function(e)
	{
		toCheck = $F('item_checkall');
		
		$$('.item_check').each( function(elem){
			if( toCheck != null )
			{
				elem.checked = true;
			}
			else
			{
				elem.checked = false;
			}
		});
	},
	
	/* ------------------------------ */
	/**
	 * Monitors single checkbox and updates "check all" box appropriately
	 * 
	 * @param	{event}		e		The event
	*/
	checkSingleBox: function(e, elem)
	{
		var totalBoxes		= 0;
		var totalChecked	= 0;
		
		$$('.item_check').each( function(checkbox){
			totalBoxes++;
			
			if( checkbox.checked == true )
			{
				totalChecked++;
			}
		} );
		
		if( totalBoxes == totalChecked )
		{
			$('item_checkall').checked	= true;
		}
		else
		{
			$('item_checkall').checked	= false;
		}				
	}
}

ipb.ibEconomy.init();