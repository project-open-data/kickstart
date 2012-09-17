jQuery(document).ready(function($){
	
	$('.kickstart-vote').click( function(){
		
		var score = $(this).siblings( '.kickstart-score');
		
		$.ajax({
			url: kickstart.ajaxEndpoint,
			data: { 
					direction: $(this).attr( 'data-vote-direction' ), 
					action: 'kickstart_vote', 
					post_ID: $(this).parent().attr( 'data-post-id' )
				},	
			success: function( data ) {	score.text( data ); }	
		})
	});
	
});