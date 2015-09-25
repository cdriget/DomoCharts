<?php
/******************************************************************************/
/*** File    : data_delete.php                                              ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.01                                                         ***/
/*** History : March 2014  : Initial release                                ***/
/***         : Sept   2015 : JSON                                           ***/
/*** Note    : Delete data in database                                      ***/
/******************************************************************************/

//*** Debug mode
define('DEBUG', false);

//*** Report all PHP errors
if (DEBUG)
	error_reporting(E_ALL | E_NOTICE | E_STRICT | E_DEPRECATED);
else
	error_reporting(E_ALL);
ini_set('display_errors', 'on');

//*** Generate exception for all errors
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler('exception_error_handler', E_ALL);


//*** 
//*** Main loop
//***
try {

	//*** Include necessary files
	require 'config.inc.php';

	//*** Variable initialization
	$response = array();

	//*** Check
	if ($_SERVER['REQUEST_METHOD'] != 'DELETE')
		throw new Exception('This is not a DELETE request', 1);
	if ( !($data = file_get_contents('php://input')) )
		throw new Exception('Can not get DELETE data', 2);

	//*** Debug
	if (DEBUG)
		echo $data.'<br/>'.PHP_EOL;

	//*** Decode data
	parse_str($data, $feed);

	//*** Check data
	if ( ! is_array($feed) )
		throw new Exception('Empty data', 4);

	//*** Parse data
	//*** id
	if ( isset($feed['id']) && is_numeric($feed['id']) )
		$id = $feed['id'];
	else
		throw new Exception('Invalid data : id', 5);
	//*** timestamp
	if ( isset($feed['timestamp']) && is_numeric($feed['timestamp']) )
		$timestamp = $feed['timestamp'];
	else
		throw new Exception('Invalid data : timestamp', 5);
	//*** type
	if ( isset($feed['type']) && is_string($feed['type']) )
		$type = $feed['type'];
	else
		throw new Exception('Invalid data : type', 5);

	//*** Prepare query
	if (substr($type, -4) == '_day') {
		$query = 'DELETE FROM domotique_'.$type.' WHERE device_id = :deviceid AND UNIX_TIMESTAMP(date) = :timestamp / 1000';
	}
	elseif (substr($type, -6) == '_month') {
		throw new Exception('Month not supported', 7);
	}
	else {
		$query = 'DELETE FROM domotique_'.$type.' WHERE device_id = :deviceid AND UNIX_TIMESTAMP(time) = :timestamp / 1000';
	}

	//*** MySQL connection
	if ( ! isset($bdd) )
		$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

	//*** Execute statement (delete data in MySQL database)
	$sql = $bdd->prepare($query);
	$result = $sql->execute(array(
		'deviceid'  => $id,
		'timestamp' => $timestamp
	));
	if ( $result ) {
		$response['success'] = true;
		$response['rowcount'] = $sql->rowCount();
	}
	else {
		$errorInfo = $sql->errorInfo();
		throw new Exception('SQLSTATE['.$errorInfo[0].'] '.$errorInfo[2], $errorInfo[1]);
	}

	//*** Cleanup
	$bdd = null;

}
//*** 
//*** Exception handling
//***
catch (Exception $e) {
	$response['success'] = false;
	$response['error']['code'] = $e->getCode();
	if (DEBUG)
		$response['error']['message'] = $e->getMessage().' on line '.$e->getLine().' in file '.basename($e->getFile()).' : '.$e->getTraceAsString();
	else
		$response['error']['message'] = $e->getMessage();
}

//*** Send result
if (DEBUG)
	header('Content-type: text/html; charset=utf-8');
else
	header('Content-type: application/json; charset=utf-8');
echo json_encode($response, JSON_NUMERIC_CHECK);
?>
