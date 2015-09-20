<?php
/******************************************************************************/
/*** File    : device_post.php                                              ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : February 2014  : Initial release                             ***/
/***         : September 2015 : JSON                                        ***/
/*** Note    : Insert/Update device into database                           ***/
/******************************************************************************/

//*** Sample JSON data
//    [{"type":"power","name":"Radiateur","roomname":"Salon","roomid":1,"id":14},{"type":"energy","name":"Radiateur","roomname":"Salon","roomid":1,"id":14},{"type":"temperature","name":"Thermomètre","roomname":"Salle%20de%20douche","roomid":10,"id":69},{"type":"humidity","name":"Hygromètre","roomname":"Salle%20de%20douche","roomid":10,"id":70},{"type":"light","name":"Luminosité%20escalier","roomname":"Hall","roomid":9,"id":115},{"type":"battery","name":"SRT321","roomname":"Chambre%20parents","roomid":4,"id":232}]
//
//*** Sample JSON return data
//    {"success":true,"rowcount":4}
//    {"success":false,"error":{"code":1,"message":"This is not a POST request."}}

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
	include 'functions.inc.php';

	//*** Variable initialization
	$response = array();
	$devicecount = 0;

	//*** Check
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		throw new Exception('This is not a POST request', 1);
	if ( !($data = file_get_contents('php://input')) )
		throw new Exception('Can not get POST data', 2);
	if ( ! function_exists('json_decode') )
		throw new Exception('Server does not support JSON', 3);

	//*** Sample JSON data
	//$data = '[{"type":"power","name":"Radiateur","roomname":"Salon","roomid":1,"id":14},{"type":"energy","name":"Radiateur","roomname":"Salon","roomid":1,"id":14},{"type":"temperature","name":"Thermomètre","roomname":"Salle%20de%20douche","roomid":10,"id":69},{"type":"humidity","name":"Hygromètre","roomname":"Salle%20de%20douche","roomid":10,"id":70},{"type":"light","name":"Luminosité%20escalier","roomname":"Hall","roomid":9,"id":115},{"type":"battery","name":"SRT321","roomname":"Chambre%20parents","roomid":4,"id":232}]';

	//*** Debug
	if (DEBUG) {
		echo $data.'<br/>'.PHP_EOL;
		//$file = basename(__FILE__, '.php').'.debug.json';
		//file_put_contents($file, $data);
	}

	//*** Decode JSON data
	$feed = json_decode($data);

	//*** Check JSON data
	$json_error = json_last_error();
	if ($json_error)
		throw new Exception('JSON : '.json_last_error_msg(), $json_error);
	if ( ! is_array($feed) )
		throw new Exception('Empty JSON data', 4);

	//*** MySQL connection
	if ( ! isset($bdd) )
		$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

	//*** Parse JSON data
	foreach ($feed as $key => $row) {

		//*** id
		if ( isset($row->id) && is_numeric($row->id) )
			$id = $row->id;
		else
			throw new Exception('Invalid JSON data', 5);

		//*** name
		if ( isset($row->name) && is_string($row->name) )
			$name = $row->name;
		else
			throw new Exception('Invalid JSON data', 5);

		//*** type
		if ( isset($row->type) && is_string($row->type) )
			$type = $row->type;
		else
			throw new Exception('Invalid JSON data', 5);

		//*** roomid
		if ( isset($row->roomid) && is_numeric($row->roomid) )
			$roomid = $row->roomid;
		else
			throw new Exception('Invalid JSON data', 5);

		//*** roomname
		if ( isset($row->roomname) && is_string($row->roomname) )
			$roomname = $row->roomname;
		else
			throw new Exception('Invalid JSON data', 5);


		//*** Debug
		if (DEBUG)
			echo '<br/>Device id='.$id.' name='.$name.' type='.$type.' Room id='.$roomid.' name='.$roomname.'<br/>'.PHP_EOL;


		//*** Check Type
		$count = $bdd->query("SELECT COUNT(*) FROM domotique_type WHERE type='".$type."'")->fetchColumn();
		if ($count == 0) {
			// Device Type does not exist and need to be created
			$sql = $bdd->prepare('INSERT INTO domotique_type (type) VALUES (:type)');
			$sql->execute(array(
				'type' => $type
			));
			if (DEBUG)
				echo 'Insert type : '.$sql->rowCount().'<br/>'.PHP_EOL;
		}
		elseif (DEBUG)
			echo 'Type exists !'.'<br/>'.PHP_EOL;
		// Get Type ID
		$typeID = $bdd->query("SELECT id FROM domotique_type WHERE type='".$type."' LIMIT 1")->fetchColumn();

		//*** Check Room
		$count = $bdd->query('SELECT COUNT(*) FROM domotique_room WHERE room_id='.$roomid)->fetchColumn();
		if ($count == 0) {
			// Room does not exist and need to be created
			$sql = $bdd->prepare('INSERT INTO domotique_room (room_id, name) VALUES (:id, :name)');
			$sql->execute(array(
				'id' => $roomid,
				'name' => $roomname
			));
			if (DEBUG)
				echo 'Insert room : '.$sql->rowCount().'<br/>'.PHP_EOL;
		}
		else {
			//$count = $bdd->query('SELECT COUNT(*) FROM domotique_room WHERE room_id='.$roomid." AND name='".$roomname."'")->fetchColumn(); // Attention : roomname n'est pas protégé
			$sql = $bdd->prepare('SELECT COUNT(*) FROM domotique_room WHERE room_id=:roomid AND name=:roomname');
			$sql->execute(array(
				'roomid' => $roomid,
				'roomname' => $roomname)
			);
			$count = $sql->fetchColumn();
			if ($count == 0) {
				// Room exists but need to be updated
				$sql = $bdd->prepare('UPDATE domotique_room SET name = :name WHERE room_id = :id');
				$sql->execute(array(
					'id' => $roomid,
					'name' => $roomname
				));
				if (DEBUG)
					echo 'Update room : '.$sql->rowCount().'<br/>'.PHP_EOL;
			}
			elseif (DEBUG)
				echo 'Room exists !'.'<br/>'.PHP_EOL;
		}

		//*** Check Device
		$count = $bdd->query('SELECT COUNT(*) FROM domotique_device WHERE id='.$id)->fetchColumn();
		if ($count == 0) {
			// Device does not exist and need to be created
			$sql = $bdd->prepare('INSERT INTO domotique_device (id, name, room_id) VALUES(:id, :name, :roomid)');
			$sql->execute(array(
				'id' => $id,
				'name' => $name,
				'roomid' => $roomid
			));
			$devicecount++;
			if (DEBUG)
				echo "Insert device ".$id." ".$typeID.' : '.$sql->rowCount().'<br/>'.PHP_EOL;
		}
		else {
			//$count = $bdd->query('SELECT COUNT(*) FROM domotique_device WHERE id='.$id." AND name='".$name."' AND room_id=".$roomid)->fetchColumn(); // Attention : name n'est pas protégé
			$sql = $bdd->prepare('SELECT COUNT(*) FROM domotique_device WHERE id=:id AND name=:name AND room_id=:roomid');
			$sql->execute(array(
				'id' => $id,
				'name' => $name,
				'roomid' => $roomid)
			);
			$count = $sql->fetchColumn();
			if ($count == 0) {
				// Device exists but need to be updated
				$sql = $bdd->prepare('UPDATE domotique_device SET name = :name, room_id = :roomid WHERE id = :id');
				$sql->execute(array(
					'id' => $id,
					'name' => $name,
					'roomid' => $roomid
				));
				if (DEBUG)
					echo "Update device ".$id." ".$typeID.' : '.$sql->rowCount().'<br/>'.PHP_EOL;
			}
			elseif (DEBUG)
				echo "Device exists ".$id." ".$typeID.'<br/>'.PHP_EOL;
		}

		//*** Check Device Type
		$count = $bdd->query('SELECT COUNT(*) FROM domotique_device_type WHERE device_id='.$id.' AND type_id='.$typeID)->fetchColumn();
		if ($count == 0) {
			// Device Type does not exist and need to be created
			$sql = $bdd->prepare('INSERT INTO domotique_device_type (device_id, type_id) VALUES(:deviceid, :typeid)');
			$sql->execute(array(
				'deviceid' => $id,
				'typeid' => $typeID
			));
			if (DEBUG)
				echo 'Insert device type : '.$sql->rowCount().'<br/>'.PHP_EOL;
		}

	}

	//*** Response
	$response['success'] = true;
	$response['rowcount'] = $devicecount;

	//*** Cleanup
	$bdd = null;

}
//*** 
//*** Exception handling
//***
catch (Exception $e) {
	$response['success'] = false;
	$response['error']['code'] = $e->getCode();
	$response['error']['message'] = $e->getMessage();
	//$response['error']['message'] = $e->getMessage().' on line '.$e->getLine().' in file '.basename($e->getFile()).' : '.$e->getTraceAsString();
}

//*** Send result
if (DEBUG)
	header('Content-type: text/html; charset=utf-8');
else
	header('Content-type: application/json; charset=utf-8');
echo json_encode($response, JSON_NUMERIC_CHECK);
?>
