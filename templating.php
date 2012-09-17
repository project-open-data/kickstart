<?php
/**
 * Templating functions available in the global scope for convenience
 */
 
function get_kickstart_ballot() {

	global $kickstart;
	
	if ( !$kickstart )
		$kickstart = Kickstart::$instance;
	
	return $kickstart->ballot();
	
}

function kickstart_ballot() {
	
	echo get_kickstart_ballot();
	
}