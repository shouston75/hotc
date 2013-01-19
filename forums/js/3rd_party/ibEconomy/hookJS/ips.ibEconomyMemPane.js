/**
 * (e32) ibEconomy
 * ibEconomy Member Pane
 * @ Topic/PM View
 * + Quick View Stats
 * + Quick Donate Points
 */

var _ibEconomy = window.IPBoard;

_ibEconomy.prototype.ibEconomy = {

	init: function()
	{
		Debug.write( 'Initializing ips.ibEconomy.js' );

		document.observe("dom:loaded", function(){
			ipb.ibEconomy.initEvents();
		});
	},
	initEvents: function()
	{
		// Delegate our item popup links
		ipb.delegate.register(".__item", ipb.ibEconomy.itemPopup);
		ipb.delegate.register(".ibEconomy_MemPanePop", ipb.ibEconomy.bigPopForm);
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
	 	todo = target.id.match( /do_([0-9a-z]+)_([0-9a-z]+)/ );
		url = target.id;
		
		// Destroy popup if it exists
		if( $('pm_popup_popup') )
		{
			ipb.ibEconomy.popupObj.getObj().remove();
		}

		
		// Pre-make popup
		ipb.ibEconomy.popupObj = new ipb.Popup('pm_popup', { type: 'pane', modal: true, hideAtStart: true, w: '600px' } );
                                                                
		var popup = ipb.ibEconomy.popupObj;

		// Lets get the form
		new Ajax.Request( ipb.vars['base_url'] + "&app=ibEconomy&module=ajax&secure_key=" + ipb.vars['secure_hash'] + '&section=popform&do=' + todo[1] + '&id=' + todo[2] + '&url=' + url,
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
	}
				
}

ipb.ibEconomy.init();