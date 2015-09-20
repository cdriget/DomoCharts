<?php
/******************************************************************************/
/*** File    : admin.php                                                    ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : Feb - Mar 2014 : Initial release                             ***/
/***         : September 2015 : Update jQuery & jQuery-ui                   ***/
/*** Note    : Administration page                                          ***/
/******************************************************************************/

//*** Include necessary files
require 'config.inc.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>DomoCharts Administration</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="application-name" content="DomoCharts">
	<meta name="author" content="Christophe DRIGET">
	<meta name="description" content="Graphiques domotique">
	<meta name="keywords" content="Graphique,Chart,Domotique,Fibaro">
	<link type="text/css" rel="Stylesheet" href="css/smoothness/jquery-ui.min.css" />
	<link type="text/css" rel="Stylesheet" href="css/fibaro/jquery-ui-1.9.2.custom.css" />
	<link type="text/css" rel="stylesheet" href="style.css"/>
	<link type="text/css" rel="stylesheet" href="css/jPicker-1.1.6.min.css"/>
	<link type="text/css" rel="stylesheet" href="css/tzCheckbox.css"/>
	<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.11.4.min.js"></script>
	<script type="text/javascript" src="js/jpicker-1.1.6.min.js"></script>
	<script type="text/javascript" src="js/tzCheckbox.js"></script>
	<script type="text/javascript" src="js/admin.js"></script>
</head>
<body>

	<div class="site">

		<div id="site-columns">
			<div id="site-column-content">

<?php
try {
	$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

	//*** Get type list
	$sql1 = $bdd->prepare('SELECT id, type FROM domotique_type');
	$sql1->execute();
	while ($data1 = $sql1->fetch(PDO::FETCH_ASSOC)) {
?>
				<div class="cadre" id="type_<?php echo $data1['id']?>">
					<div class="cadreTitle"><span><?php echo $data1['type']?></span></div>
					<div class="cadreBody">
						<table class="element device" id="table-device-<?php echo $data1['id']?>" align="center" width="100%">
							<tbody>
<?php
		//*** Get device list
		$sql2 = $bdd->prepare('SELECT d.id AS device_id, d.name AS device_name, r.name AS room_name, dt.visible, dt.color FROM domotique_device d, domotique_device_type dt, domotique_type t, domotique_room r WHERE d.room_id=r.room_id AND d.id=dt.device_id AND dt.type_id=t.id AND t.type = :type ORDER BY dt.ordre');
		$sql2->execute(array('type' => $data1['type']));
		$odd_row = true;
		while ($data2 = $sql2->fetch(PDO::FETCH_ASSOC)) {
			$class = $odd_row ? 'odd' : 'even';
?>
								<tr id="device_<?php echo $data1['id']?>_<?php echo $data2['device_id']?>" class="<?php echo $class?>">
									<td class="blank fibaro-theme" align="center" width="24px"><span class="ui-icon ui-icon-arrowthick-2-n-s" title="Drag & Drop to rearrange elements"></span></td>
									<td class="" align="left" width="275px"><?php echo $data2['device_name']?> (<?php echo $data2['device_id']?>)</td>
									<td class="" align="left" width="275px"><?php echo $data2['room_name']?></td>
									<td class="content" align="left" width="75px"><input type="hidden" class="colorpicker" id="cpicker_<?php echo $data1['id']?>_<?php echo $data2['device_id']?>" value="<?php echo $data2['color']?>"></input></td>
									<td class="content tzcheckbox" align="center" width="100px"><input type="checkbox" id="check_<?php echo $data1['id']?>_<?php echo $data2['device_id']?>" name="check_<?php echo $data1['id']?>_<?php echo $data2['device_id']?>" <?php echo $data2['visible']?'checked="checked"':''?> data-on="Afficher" data-off="Cacher" /></td>
								</tr>
<?php
			$odd_row = ! $odd_row;
		}
?>
							</tbody>
						</table>
					</div>
				</div>
<?php
	}
}
catch(Exception $e) {
	echo $e->getMessage();
}
?>

			</div>
		</div>

	</div>

</body>
</html>
