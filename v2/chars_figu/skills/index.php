<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/orthanc/includes/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/orthanc/includes/token.php';

$c_fetch    = new Skills();
$sheet_type = $c_fetch->get_char_type_by_id( $input['id'] );
if ( ! ( strpos( $sheet_type, 'figurant' ) !== false ) ) {
	http_response_code( 404 );
	echo 'No result found';
	exit;
}
else {
	switch ( $method ) {
		case 'DELETE':
			require_once './_delete.php';
			break;
		case 'POST':
			// require_once './_post.php';
			break;
		case 'PATCH':
			// require_once './_patch.php';
			http_response_code( 501 );
			break;
		case 'PUT':
			// require_once './_put.php';
			http_response_code( 501 );
			break;
		case 'GET':
			require_once './_get.php';
			break;
		case 'OPTIONS':
			http_response_code( 200 );
		default:
			require_once './_get.php';
			break;
	}
}
