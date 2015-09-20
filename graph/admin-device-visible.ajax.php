<?php
/******************************************************************************/
/*** File    : admin-device-visible.ajax.php                                ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : Feb - Mar 2014 : Initial release                             ***/
/*** Note    : Administration page                                          ***/
/******************************************************************************/

//*** Include necessary files
require 'config.inc.php';

try {
	//*** Get URL parameter
	if ( isset($_GET['type']) && $_GET['type']>0 && isset($_GET['device']) && is_numeric($_GET['device']) && isset($_GET['visible']) && is_numeric($_GET['visible']) ) {

		//*** Connect to database
		$bdd = new PDO('mysql:host='.$server.';dbname='.$database, $login, $password);

		//*** SQL Query
		$sql = $bdd->prepare('UPDATE domotique_device_type SET visible = :visible WHERE type_id = :typeid AND device_id = :deviceid');
		$sql->execute(array(
			'typeid'   => $_GET['type'],
			'deviceid' => $_GET['device'],
			'visible'  => $_GET['visible']
		));
		if ($sql->rowCount())
			echo 'OK';
		else
			echo 'Error while updating data into MySQL Database';
	}
	else
		echo 'Error : wrong parameters';
}
catch(Exception $e) {
	echo $e->getMessage();
}
?>
