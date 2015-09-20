<?php
/******************************************************************************/
/*** File    : admin-device-sort.ajax.php                                   ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : Feb - Mar 2014 : Initial release                             ***/
/*** Note    : Administration page                                          ***/
/******************************************************************************/

//*** Include necessary files
require 'config.inc.php';

try {
	if ( isset($_POST) ) {

		//*** Connect to database
		$bdd = new PDO('mysql:host='.$server.';dbname='.$database, $login, $password);

		//*** Get URL parameter
		$key = key($_POST);
		$data = explode("_", $key);
		$typeid = $data[1];

		//*** SQL Query
		foreach( $_POST[$key] as $index => $deviceid ) {
			$sql = $bdd->prepare('UPDATE domotique_device_type SET ordre = :ordre WHERE type_id = :typeid AND device_id = :deviceid');
			$result = $sql->execute(array(
				'typeid'   => $typeid,
				'deviceid' => $deviceid,
				'ordre'    => $index + 1
			));
			if ( ! $result ) {
				echo 'Error while updating data into MySQL Database';
				die;
			}
			//$sql->closeCursor();
		}
		echo 'OK';
	}
	else
		echo 'Error : wrong parameters';
}
catch(Exception $e) {
	echo $e->getMessage();
}
?>
