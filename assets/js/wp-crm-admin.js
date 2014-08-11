jQuery( document ).ready(function( $ ) {

	$('.sf-get-feeds').on('click', function( event ) {

		event.preventDefault();

		var button = $(this);

		button.prop('disabled', true);

		$.ajax({
      type: "POST",
      url: sf_ajax_object.ajax_url, 
      data: { action: 'sf_get_feeds' },
      success: function(response){ 
        alert("Got this from the server: " + response);
      },
      error: function(MLHttpRequest, textStatus, errorThrown){  
        alert("There was an error: " + errorThrown);  
      },
      complete: function() {
      	button.prop('disabled', false);
      },
      timeout: 60000
  	});    

	});

});