<?php
/******************************************************************************/
/*** File    : data_get.php                                                 ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.01                                                         ***/
/*** History : February 2014  : Initial release                             ***/
/***         : May / Sep 2015 : New sensors                                 ***/
/*** Note    : Get data from database                                       ***/
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
	include 'functions.inc.php';

	//*** Variable initialization
	$data = array();

	//*** Get data
	if ( isset($_GET['query']) && $_GET['query']=='dataserie' ) {
		$type   = filter_input(INPUT_GET, 'type',   FILTER_SANITIZE_STRING);
		$device = filter_input(INPUT_GET, 'device', FILTER_SANITIZE_NUMBER_INT);
		if (substr($type, -4) == '_day') {
			if ( $type == 'water_day' )
				$query = 'SELECT UNIX_TIMESTAMP(date)*1000 AS time, sum_value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time';
			elseif ( $type == 'energy_day' )
				$query = 'SELECT UNIX_TIMESTAMP(date)*1000 AS time, hc_value+hp_value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time';
			elseif ( $type == 'battery_day' )
				$query = 'SELECT UNIX_TIMESTAMP(date)*1000 AS time, value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time';
			else
				$query = 'SELECT UNIX_TIMESTAMP(date)*1000 AS time, avg_value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time';
			$data['device'] = $device;
		}
		elseif (substr($type, -6) == '_month') {
			if ( $type == 'water_month' )
				$query = "SELECT UNIX_TIMESTAMP(CONCAT(year,'-',month,'-01 00:00:00'))*1000 AS time, sum_value FROM domotique_".$type.' WHERE device_id = :device ORDER BY time';
			else
				$query = "SELECT UNIX_TIMESTAMP(CONCAT(year,'-',month,'-01 00:00:00'))*1000 AS time, avg_value FROM domotique_".$type.' WHERE device_id = :device ORDER BY time';
			$data['device'] = $device;
		}
		else {
			$query = 'SELECT UNIX_TIMESTAMP(time)*1000 AS time, value FROM domotique_'.$type.' WHERE device_id = :device AND time >= DATE_SUB(curdate(), INTERVAL :interval DAY) ORDER BY time';
			$data['device'] = $device;
			$data['interval'] = $interval;
		}

		//*** MySQL connection
		if ( ! isset($bdd) )
			$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

		//*** Debug
		if (DEBUG) {
			echo 'type = '.$type.'<br/>'.PHP_EOL;
			echo 'device = '.$device.'<br/>'.PHP_EOL;
			echo '<pre>'.PHP_EOL;
			print_r($data);
			echo '</pre>'.PHP_EOL;
			echo DebugPDOSQL($query, $data).'<br/>'.PHP_EOL;
			DEBUG_MYSQL($bdd, $query, $data);
		}

		//*** Prepare PDO statement
		$sql = $bdd->prepare($query);

		//*** Execute statement
		$sql->execute($data);

		//*** Check query success
		if ( ! $sql ) {
			$errorInfo = $sql->errorInfo();
			throw new Exception('SQLSTATE['.$errorInfo[0].'] '.$errorInfo[2], $errorInfo[1]);
		}

		//*** Retrieve data
		$datas = $sql->fetchAll(PDO::FETCH_NUM);

		//*** Return data
		echo $_GET['callback'] . '(' . json_encode($datas, JSON_NUMERIC_CHECK) . ');';
	}

	//*** Cleanup
	$bdd = null;
}
//*** 
//*** Exception handling
//***
catch (Exception $e) {
	die($e->getMessage());
}
?>
