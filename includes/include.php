<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Inject Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, cache-control, token');

$method = $_SERVER['REQUEST_METHOD']; //Grab HTTP REST Method
if ($method = 'OPTIONS') {
   http_response_code(200);
   die();
}
else {
    function __autoload($classname) {
        include("classes/$classname.php");
    }

    function token($token){
        $stmt = database::$conn->prepare("SELECT * FROM eos_tokens WHERE token = ?");
        $res = $stmt->execute(array($token));
        $res = $stmt->fetch(PDO::FETCH_ASSOC); 
        
        if($res != null){
            return "valid";
        }else{
            return false;
        }
    }

    $APP = array(); 
    $APP["includes"] = array(); // opens an array to be filled later with the CSS and JS, which will eventually be included by PHP.
    $APP["header"] = "/api/orthanc"; // location of the application. for example: http://localhost/api/orthanc/ == '/api/orthanc'. If the application is in the ROOT, you can leave this blank.
    $APP["root"] = $_SERVER["DOCUMENT_ROOT"] . $APP["header"]; // define the root folder by adding the header (location) to the server root, defined by PHP.
    $input = json_decode(file_get_contents('php://input'), true); //Store Input
    if (!isset($input)) {
        $input = apache_request_headers();
    }
}
?>