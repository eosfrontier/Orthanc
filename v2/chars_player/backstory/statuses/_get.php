<?php

// CHECK BY CHARACTER ID

if ( !isset( $input['type'] ) ) {
	http_response_code( 400 );
	die(json_encode("You must include a 'type'" ));
}

else {
	$types = ['concept', 'backstory', 'concept_changes' ,'backstory_changes'];
	if ( ! in_array( $input['type'], $types ) ){
		http_response_code( 400 );
		die(json_encode("Invalid type. 'type' must be 'concept' or 'backstory'3"));
	}
$a_result = $c_fetch->get_statuses( $input['type'] );
if ( empty( $a_result ) ) {
	http_response_code( 404 );
	die(json_encode('None found.'));
}

http_response_code( 200 );
echo json_encode( $a_result );
die();

}
