<?php
/******************************************************************************/
/*** File    : device_get.php                                               ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : Feb - Mar 2014 : Initial release                             ***/
/***         : September 2015 : UTF-8                                       ***/
/*** Note    : Get devices from database                                    ***/
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
	$sql = $bdd->prepare('SELECT domotique_device.id AS id, CONCAT(domotique_device.name, " ", domotique_room.name) AS device, IF(LENGTH(domotique_device_type.color)=6,CONCAT("#",domotique_device_type.color),"") AS color FROM domotique_device, domotique_device_type, domotique_type, domotique_room WHERE domotique_device.room_id=domotique_room.room_id AND domotique_device.id=domotique_device_type.device_id AND domotique_device_type.type_id=domotique_type.id AND domotique_type.type = :type AND domotique_device_type.visible=1 ORDER BY domotique_device_type.ordre');
	$type = explode('_', filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING)); // explode() removes optional _day suffix
	$sql->execute(array('type' => $type[0]));
	$datas = $sql->fetchAll(PDO::FETCH_NUM);

	//*** Return data
	echo $_GET['callback'] . '(' . json_encode($datas, JSON_NUMERIC_CHECK) . ');';

	//*** Cleanup
	$bdd = null;
}
//*** 
//*** Exception handling
//***
catch (Exception $e) {
	echo $e->getMessage();
}
?>
