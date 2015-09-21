<?php
/******************************************************************************/
/*** File    : data_post.php                                                ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : March 2014     : Initial release                             ***/
/***         : Aug / Sep 2015 : JSON                                        ***/
/*** Note    : Insert data into database                                    ***/
/******************************************************************************/

//*** Sample JSON data
//    [{"id":0,"timestamp":"NULL","type":"temperature","value":10},{"id":1,"timestamp":"1427749407","type":"humidity","value":8},{"id":2,"timestamp":"NULL","type":"test","value":27},{"id":4,"timestamp":"1427749407","type":"water","value":0},{"id":1,"timestamp":"NULL","type":"temperature","value":12}]
//    [{"id":2,"timestamp":"NULL","type":"test","value":27},{"id":4,"timestamp":"1427749407","type":"test","value":0},{"id":1,"timestamp":"NULL","type":"test","value":12}]
//    [{"id":0,"date":"NULL","type":"water","value":327},{"id":1,"date":"2015-07-31","type":"water","value":423},{"id":2,"date":"NULL","type":"water","value":283},{"id":4,"date":"2015-08-02","type":"water","value":0}]
//    [{"type":"energy","date":"2015-08-01","id":11,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":14,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":18,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":21,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":24,"value_HP":0,"value_HC":0.01}]
//    [{"type":"energy","date":"2015-08-01","id":11,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":14,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":18,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":21,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":24,"value_HP":0,"value_HC":0.01},{"type":"energy","date":"2015-08-01","id":26,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":33,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":34,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":52,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":53,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":71,"value_HP":0.08,"value_HC":0.04},{"type":"energy","date":"2015-08-01","id":80,"value_HP":0.01,"value_HC":0},{"type":"energy","date":"2015-08-01","id":86,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":91,"value_HP":0.01,"value_HC":0.01},{"type":"energy","date":"2015-08-01","id":92,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":126,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":127,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":131,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":132,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":201,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":202,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":358,"value_HP":0.41,"value_HC":0.21},{"type":"energy","date":"2015-08-01","id":369,"value_HP":0.2,"value_HC":0.07},{"type":"energy","date":"2015-08-01","id":385,"value_HP":0.02,"value_HC":0.03},{"type":"energy","date":"2015-08-01","id":388,"value_HP":0,"value_HC":0}]
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
	$response   = array();
	$SQLqueries = array(); // SQL queries
	$SQLrows    = array(); // SQL snippets
	$SQLvalues  = array(); // SQL values to bind

	//*** Date & Time
	$timestp = time();

	//*** Check
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		throw new Exception('This is not a POST request', 1);
	if ( !($data = file_get_contents('php://input')) )
		throw new Exception('Can not get POST data', 2);
	if ( ! function_exists('json_decode') )
		throw new Exception('Server does not support JSON', 3);

	//*** Sample JSON data
	//$data = '[{"id":0,"timestamp":"NULL","type":"temperature","value":10},{"id":1,"timestamp":"1427749407","type":"humidity","value":8},{"id":2,"timestamp":"NULL","type":"test","value":27},{"id":4,"timestamp":"1427749407","type":"water","value":0},{"id":1,"timestamp":"NULL","type":"temperature","value":12}]';
	//$data = '[{"id":2,"timestamp":"NULL","type":"test","value":27},{"id":4,"timestamp":"1427749407","type":"test","value":0},{"id":1,"timestamp":"NULL","type":"test","value":12}]';
	//$data = '[{"id":0,"date":"NULL","type":"water","value":327},{"id":1,"date":"2015-07-31","type":"water","value":423},{"id":2,"date":"NULL","type":"water","value":283},{"id":4,"date":"2015-08-02","type":"water","value":0}]';
	//$data = '[{"type":"energy","date":"2015-08-01","id":11,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":14,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":18,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":21,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":24,"value_HP":0,"value_HC":0.01}]';
	//$data = '[{"type":"energy","date":"2015-08-01","id":11,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":14,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":18,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":21,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":24,"value_HP":0,"value_HC":0.01},{"type":"energy","date":"2015-08-01","id":26,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":33,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":34,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":52,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":53,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":71,"value_HP":0.08,"value_HC":0.04},{"type":"energy","date":"2015-08-01","id":80,"value_HP":0.01,"value_HC":0},{"type":"energy","date":"2015-08-01","id":86,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":91,"value_HP":0.01,"value_HC":0.01},{"type":"energy","date":"2015-08-01","id":92,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":126,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":127,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":131,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":132,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":201,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":202,"value_HP":0,"value_HC":0},{"type":"energy","date":"2015-08-01","id":358,"value_HP":0.41,"value_HC":0.21},{"type":"energy","date":"2015-08-01","id":369,"value_HP":0.2,"value_HC":0.07},{"type":"energy","date":"2015-08-01","id":385,"value_HP":0.02,"value_HC":0.03},{"type":"energy","date":"2015-08-01","id":388,"value_HP":0,"value_HC":0}]';

	//*** Debug
	if (DEBUG) {
		echo $data.'<br/>'.PHP_EOL;
		//for ($i = 0; $i < strlen($data); $i++)
			//echo ord($data[$i]).' : '.$data[$i].PHP_EOL;
	}

	//*** Decode JSON data
	$feed = json_decode($data);

	//*** Check JSON data
	$json_error = json_last_error();
	if ($json_error)
		throw new Exception('JSON : '.json_last_error_msg(), $json_error);
	if ( ! is_array($feed) )
		throw new Exception('Empty JSON data', 4);

	//*** Parse JSON data
	foreach ($feed as $key => $row) {

		$timestamp = 0;
		$date = '';

		//*** id
		if ( isset($row->id) && is_numeric($row->id) )
			$id = $row->id;
		else
			throw new Exception('Invalid JSON data', 5);

		//*** type
		if ( isset($row->type) && is_string($row->type) )
			$type = $row->type;
		else
			throw new Exception('Invalid JSON data', 5);

		//*** timestamp or date
		if ( isset($row->timestamp) && is_string($row->timestamp) ) {
			if ( $row->timestamp == 'NULL' )
				$timestamp = $timestp;
			else
				$timestamp = intval($row->timestamp);
		}
		else if ( isset($row->date) && is_string($row->date) ) {
			if ( $row->date == 'NULL' )
				$date = date('Y-m-d', $timestp);
			else
				$date = $row->date;
		}
		else
			throw new Exception('Invalid JSON data', 5);

		//*** Prepare SQL data
		switch ($type) {

			/*case 'test':
				break;*/

			case 'temperature':
			case 'humidity':
			case 'light':
			case 'power':
			case 'co2':
			case 'pressure':
			case 'noise':
			case 'rain':
			case 'wind':
				if ($timestamp) {
					$SQLqueries[$type] = 'INSERT IGNORE INTO domotique_'.$type.' (time, device_id, value) VALUES ';
					//$SQLqueries[$type] = 'INSERT IGNORE INTO domotique_test (time, device_id, value) VALUES ';         // ATTENTION : TEMPORAIRE POUR DEBUG
					$SQLvalues[$type]['timestamp'.$key] = $timestamp;
					$SQLvalues[$type]['id'       .$key] = $id;
					$SQLrows[$type][] = '(FROM_UNIXTIME(:timestamp'.$key.'),:id'.$key.',:value'.$key.')';
				}
				else
					throw new Exception('No timestamp value for temperature data type', 7);
				if ( isset($row->value) && is_numeric($row->value) )
					$SQLvalues[$type]['value'.$key] = $row->value;
				else
					throw new Exception('Invalid JSON data', 5);
				break;

			case 'battery':
				if ($date) {
					$SQLqueries[$type] = 'INSERT IGNORE INTO domotique_'.$type.'_day (date, device_id, value) VALUES ';
					$SQLvalues[$type]['date'.$key] = $date;
					$SQLvalues[$type]['id'  .$key] = $id;
					$SQLrows[$type][] = '(:date'.$key.',:id'.$key.',:value'.$key.')';
				}
				else
					throw new Exception('No date value for energy data type', 7);
				if ( isset($row->value) && is_numeric($row->value) )
					$SQLvalues[$type]['value'.$key] = $row->value;
				else
					throw new Exception('Invalid JSON data', 5);
				break;

			case 'water':
				if ($timestamp) {
					$SQLqueries[$type] = 'INSERT IGNORE INTO domotique_'.$type.' (time, device_id, value) VALUES ';
					$SQLvalues[$type]['timestamp'.$key] = $timestamp;
					$SQLvalues[$type]['id'       .$key] = $id;
					$SQLrows[$type][] = '(FROM_UNIXTIME(:timestamp'.$key.'),:id'.$key.',:value'.$key.')';
				}
				elseif ($date) {
					//$SQLqueries[$type] = 'INSERT INTO domotique_'.$type.'_day (date, device_id, value) VALUES ';
					$SQLqueries[$type] = 'INSERT IGNORE INTO domotique_'.$type.'_day (date, device_id, sum_value) VALUES ';
					$SQLvalues[$type]['date'.$key] = $date;
					$SQLvalues[$type]['id'  .$key] = $id;
					$SQLrows[$type][] = '(:date'.$key.',:id'.$key.',:value'.$key.')';
				}
				else
					throw new Exception('No date value for water data type', 7);
				if ( isset($row->value) && is_numeric($row->value) )
					$SQLvalues[$type]['value'.$key] = $row->value;
				else
					throw new Exception('Invalid JSON data', 5);
				break;

			case 'energy':
				if ($date) {
					$SQLqueries[$type] = 'INSERT INTO domotique_'.$type.'_day (date, device_id, base_value, hc_value, hp_value) VALUES ';
					$SQLvalues[$type]['date'.$key] = $date;
					$SQLvalues[$type]['id'  .$key] = $id;
					$SQLrows[$type][] = '(:date'.$key.',:id'.$key.',:value_BASE'.$key.',:value_HC'.$key.',:value_HP'.$key.')';
				}
				else
					throw new Exception('No date value for energy data type', 7);
				if ( isset($row->value_BASE) && is_numeric($row->value_BASE) ) {
					$SQLvalues[$type]['value_BASE'.$key] = $row->value_BASE;
					$SQLvalues[$type]['value_HC'  .$key] = null;
					$SQLvalues[$type]['value_HP'  .$key] = null;
				}
				elseif ( isset($row->value_HC) && is_numeric($row->value_HC) && isset($row->value_HP) && is_numeric($row->value_HP) ) {
					$SQLvalues[$type]['value_BASE'.$key] = null;
					$SQLvalues[$type]['value_HC'  .$key] = $row->value_HC;
					$SQLvalues[$type]['value_HP'  .$key] = $row->value_HP;
				}
				else
				//if ( ! isset($value_BASE) && ! isset($value_HC) && ! isset($value_HP) )
					throw new Exception('Invalid JSON data', 5);
				break;

			default:
				throw new Exception('Invalid "type" value', 6);
		}

	}

	//*** Debug
	if (DEBUG) {
		echo '<pre>'.PHP_EOL;
		print_r($SQLqueries);
		print_r($SQLrows);
		print_r($SQLvalues);
		echo '</pre>'.PHP_EOL;
	}

	//*** MySQL connection
	if ( ! isset($bdd) )
		$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

	foreach ($SQLrows as $type => $SQLparams) {

		//*** Construct SQL statement
		$query = $SQLqueries[$type] . implode(',', $SQLparams);

		//*** Debug
		if (DEBUG) {
			echo 'type : '.$type.'<br/>'.PHP_EOL;
			echo '<pre>'.PHP_EOL;
			print_r($SQLparams);
			echo '</pre>'.PHP_EOL;
			echo $query.'<br/>'.PHP_EOL;
			echo DebugPDOSQL($query, $SQLvalues[$type]).'<br/>'.PHP_EOL;
		}

		//*** Prepare PDO statement
		$sql = $bdd->prepare($query);

		//*** Bind values
		foreach ($SQLvalues[$type] as $param => $val)
			$sql->bindValue(':'.$param, $val);

		//*** Execute statement (Insert data into MySQL database)
		if ( $sql->execute() ) {
			$response['success'] = true;
			if (isset($response['rowcount']))
				$response['rowcount'] = $response['rowcount'] + $sql->rowCount();
			else
				$response['rowcount'] = $sql->rowCount();
		}
		else {
			$errorInfo = $sql->errorInfo();
			throw new Exception('SQLSTATE['.$errorInfo[0].'] '.$errorInfo[2], $errorInfo[1]);
		}

	}

	//*** Cleanup
	$bdd = null;

}
//*** 
//*** Exception handling
//***
catch (Exception $e) {
	//unset($response['rowcount'], $response['data']);
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
