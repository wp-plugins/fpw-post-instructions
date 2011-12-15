jQuery( document ).ready( function( $ ) {

	jQuery("#contextual-help-link").html(fpw_fpi_text.fpw_fpi_help_link_text);

	// Fade out update message
	setTimeout(function(){
  		$("div.updated").fadeOut("slow", function () {
  			$("div.updated").remove();
      	});
	}, 5000);
	
});