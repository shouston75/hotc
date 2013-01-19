/**
 * (e32) ibEconomy
 * ibEconomy Payment
 * @ ibEco Checkout
 * + Stole IPB Topic Poll JS
 */

var _payment = window.IPBoard;

_payment.prototype.payment = {
	maxQuestions: 0,
	maxChoices: 0,
	
	questions: $H(),
	choices: $H(),
	_choices: $H(),
	
	effectDuration: 0.3,
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing ips.payment.js");
		
		document.observe("dom:loaded", function(){
			ipb.payment.initpayment();
		});
	},
	
	/* ------------------------------ */
	/**
	 * Initialize the payment options
	*/
	initpayment: function()
	{

		// What should we show?
		if( ipb.payment.showOnLoad ){
			$('add_payment').hide();
			$('payment_wrap').show();
		} else {
			$('payment_wrap').hide();
			$('add_payment').show();
		}
		
		$('add_payment').observe('click', ipb.payment.toggleForm);
		$('close_payment').observe('click', ipb.payment.toggleForm);
	},
		
	/* ------------------------------ */
	/**
	 * Toggle the poll form
	*/
	toggleForm: function(e)
	{
		Event.stop(e);
		
		if( $('payment_wrap').visible() ){
			$('add_payment').show();

		} else {
			$('add_payment').hide();
			
			// Add a default question
			if( ipb.payment._choices.size() == 0 )
			{
				ipb.payment.addQuestion( e, 1 );
			}
		}
		
		Effect.toggle( $('payment_wrap'), 'blind', { duration: ipb.payment.effectDuration } );
	},
	
	
	/* ------------------------------ */
	/**
	 * Add a new choice
	 * 
	 * @param	{event}		event		The event
	 * @param	{int}		qid			The question ID
	 * @param	{boolean}	instant		Slide down or show instantly?
	*/
	addChoice: function(e, qid, instant)
	{
		Event.stop(e);
		
		if( !qid || !$('choices_for_' + qid) ){ return; }
		
		var newid = ipb.payment.getNextID( 'c', qid );
		
		if( ipb.payment._choices.get( qid ).size() >= ipb.payment.maxChoices ){
			alert( "No More!" );
			return;
		}
		
		var choice = ipb.templates['payment_choice'].evaluate( { qid: qid, cid: newid, choice: '', votes: 0 } );
		$('choices_for_' + qid ).insert( choice );
		
		// Time to show
		if( instant ){
			$('payment_' + qid + '_' + newid + '_wrap').show();
		} else {
			new Effect.BlindDown( $('payment_' + qid + '_' + newid + '_wrap'), { duration: ipb.payment.effectDuration } );
		}
		
		// Add event
		if( $('remove_' + qid + '_' + newid) ){
			$('remove_' + qid + '_' + newid ).observe('click', ipb.payment.removeChoice.bindAsEventListener( this, qid, newid ) );
		}
		
		// Add to array
		ipb.payment._choices.get( qid ).set( newid, $H({ value: '', votes: 0 }) );
	},
	
	/* ------------------------------ */
	/**
	 * Removes a choice
	*/
	removeChoice: function(e, qid, cid)
	{
		Event.stop(e);
		
		if( !qid || Object.isUndefined( cid ) || !$('payment_' + qid + '_' + cid) ){ return; }			
		
		// Hide it
		new Effect.BlindUp( $('payment_' + qid + '_' + cid + '_wrap' ), { duration: ipb.payment.effectDuration, afterFinish: function(){ $('payment_' + qid + '_' + cid + '_wrap').remove(); } } );
		
		// remove it from array
		ipb.payment._choices.get( qid ).unset( cid );
	},
	
	/* ------------------------------ */
	/**
	 * Add a new question
	 * 
	 * @param	{event}		e			The event
	 * @param	{boolean}	instant		Show instantly?
	*/
	addQuestion: function(e, instant)
	{
		Event.stop(e);
		
		var newid = ipb.payment.getNextID('q');

		var item = ipb.templates['payment_question'].evaluate( { qid: newid, value: '' } );
		$( 'payment_container' ).insert( item );
		
		if( $('remove_question_' + newid) ){
			$('remove_question_' + newid).observe('click', ipb.payment.removeQuestion.bindAsEventListener( this, newid ) );
		}
		
		// Show it
		if( instant ){
			$('question_' + newid + '_wrap').show();
		} else {
			new Effect.BlindDown( $('question_' + newid + '_wrap'), { duration: ipb.payment.effectDuration } );
		}
		
		// Add it to array
		ipb.payment._choices.set( newid, $H() );
		
		// Lets add a choice to start them off
		ipb.payment.addChoice(e, newid, 1);
		
		// Add events on the question wrap
		if( $('add_choice_' + newid ) )
		{
			$('add_choice_' + newid).observe('click', ipb.payment.addChoice.bindAsEventListener( this, newid ) );
		}
	},
	
	/* ------------------------------ */
	/**
	 * Returns the next highest ID
	 * 
	 * @param	{string}	type		Type of ID, either q for question or c for choice
	 * @param	{int}		qid			Question ID if type is c
	*/
	getNextID: function( type, qid )
	{
		//Debug.dir( ipb.payment._choices );
		
		if( type == 'q' )
		{
			if( Object.isUndefined( ipb.payment._choices ) ){
				var max = 1;
			}
			else
			{
				var max = parseInt( ipb.payment._choices.max( function(q){
						return parseInt( q.key );
					}) ) + 1;
				
				if ( isNaN( max ) )
				{
					var max = 1;
				}
			}
		}
		else
		{
			if( Object.isUndefined( qid ) ){ return false; }
			
			if( Object.isUndefined( ipb.payment._choices.get( qid ) ) ){
				var max = 0;
			}
			else
			{
				var max = parseInt( ipb.payment._choices.get( qid ).max( function(c){
					return parseInt( c.key );
				}) ) + 1;
				
				if( isNaN( max ) ){
					max = 1;
				}
			}
		}
		
		Debug.write( max );
		return max;
	}
	
}
ipb.payment.init();