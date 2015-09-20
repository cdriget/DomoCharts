<?php
/******************************************************************************/
/*** File    : data_get.php                                                 ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
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

	//*** Get data
	$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);
	if ( isset($_GET['query']) && $_GET['query']=='dataserie' ) {
		$type   = filter_input(INPUT_GET, 'type',   FILTER_SANITIZE_STRING);
		$device = filter_input(INPUT_GET, 'device', FILTER_SANITIZE_NUMBER_INT);
		if (substr($type, -4) == '_day') {
			if ( $type == 'water_day' )
				$sql = $bdd->prepare('SELECT UNIX_TIMESTAMP(date)*1000 AS time, sum_value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time');
			elseif ( $type == 'energy_day' )
				$sql = $bdd->prepare('SELECT UNIX_TIMESTAMP(date)*1000 AS time, hc_value+hp_value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time');
			elseif ( $type == 'battery_day' )
				$sql = $bdd->prepare('SELECT UNIX_TIMESTAMP(date)*1000 AS time, value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time');
			else
				$sql = $bdd->prepare('SELECT UNIX_TIMESTAMP(date)*1000 AS time, avg_value FROM domotique_'.$type.' WHERE device_id = :device ORDER BY time');
			$sql->execute(array(
				'device' => $device)
			);
		}
		elseif (substr($type, -6) == '_month') {
			if ( $type == 'water_month' )
				$sql = $bdd->prepare("SELECT UNIX_TIMESTAMP(CONCAT(year,'-',month,'-01 00:00:00'))*1000 AS time, sum_value FROM domotique_".$type.' WHERE device_id = :device ORDER BY time');
			else
				$sql = $bdd->prepare("SELECT UNIX_TIMESTAMP(CONCAT(year,'-',month,'-01 00:00:00'))*1000 AS time, avg_value FROM domotique_".$type.' WHERE device_id = :device ORDER BY time');
			$sql->execute(array(
				'device' => $device)
			);
		}
		else {
			$sql = $bdd->prepare('SELECT UNIX_TIMESTAMP(time)*1000 AS time, value FROM domotique_'.$type.' WHERE device_id = :device AND time >= DATE_SUB(curdate(), INTERVAL :interval DAY) ORDER BY time');
			$sql->execute(array(
				'device' => $device,
				'interval' => $interval)
			);
		}
		$datas = $sql->fetchAll(PDO::FETCH_NUM);
		/*$datas = array();
		while ( $data = $sql->fetch(PDO::FETCH_ASSOC) ) {
			$datas['x'][] = $data['time'];
			$datas['y'][]     = $data['value'];
			//array_push($timestamp, $data['time']);
			//array_push($value, $data['value']);
		}*/
		//print_r($datas);

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
