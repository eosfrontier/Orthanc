<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/orthanc/includes/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/orthanc/includes/token.php';
$atm = new Atm();

switch ( $method ) {
	case 'POST':
		require_once './_post.php';
		break;
	case 'GET':
		require_once './_get.php';
		break;
	case 'PATCH':
		require_once './_patch.php';
		break;
	case 'OPTIONS':
		http_response_code( 200 );
		break;
	default:
		require_once './_get.php';
		break;
}
