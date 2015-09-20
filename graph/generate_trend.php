<?php
/******************************************************************************/
/*** File    : generate_trend.php                                           ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : February 2014 : Initial release                              ***/
/***         : Sept 2015     : Add new sensors                              ***/
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

	//*** MySQL connection
	if ( ! isset($bdd) )
		$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

	//*** Temperature
	$bdd->prepare("
		INSERT INTO domotique_temperature_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_temperature
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_temperature_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_temperature WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_temperature")->execute();
	$bdd->prepare("
		INSERT INTO domotique_temperature_month (year, month, device_id, min_value, min_day_value, avg_value, max_day_value, max_value)
		SELECT
			YEAR(DATE),
			MONTH(date),
			device_id,
			MIN(min_value),
			MIN(avg_value),
			AVG(avg_value),
			MAX(avg_value),
			MAX(max_value)
		FROM
			domotique_temperature_day
		WHERE
			date > (SELECT COALESCE(MAX(LAST_DAY(STR_TO_DATE(CONCAT(year,',',month,',',1),'%Y,%m,%d'))), '0001-01-01') FROM domotique_temperature_month)
			AND date < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')
		GROUP BY
			YEAR(DATE),
			MONTH(date),
			device_id
	")->execute();

	//*** Humidity
	$bdd->prepare("
		INSERT INTO domotique_humidity_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_humidity
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_humidity_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_humidity WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_humidity")->execute();
	$bdd->prepare("
		INSERT INTO domotique_humidity_month (year, month, device_id, min_value, min_day_value, avg_value, max_day_value, max_value)
		SELECT
			YEAR(DATE),
			MONTH(date),
			device_id,
			MIN(min_value),
			MIN(avg_value),
			AVG(avg_value),
			MAX(avg_value),
			MAX(max_value)
		FROM
			domotique_humidity_day
		WHERE
			date > (SELECT COALESCE(MAX(LAST_DAY(STR_TO_DATE(CONCAT(year,',',month,',',1),'%Y,%m,%d'))), '0001-01-01') FROM domotique_humidity_month)
			AND date < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')
		GROUP BY
			YEAR(DATE),
			MONTH(date),
			device_id
	")->execute();

	//*** Light
	$bdd->prepare("
		INSERT INTO domotique_light_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_light
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_light_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_light WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_light")->execute();

	//*** Power
	$bdd->prepare("
		INSERT INTO domotique_power_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_power
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_power_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_power WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_power")->execute();

	//*** Water
	$bdd->prepare("
		INSERT INTO domotique_water_month (year, month, device_id, min_day_value, sum_value, max_day_value)
		SELECT
			YEAR(DATE),
			MONTH(date),
			device_id,
			MIN(sum_value),
			SUM(sum_value),
			MAX(sum_value)
		FROM
			domotique_water_day
		WHERE
			date > (SELECT COALESCE(MAX(LAST_DAY(STR_TO_DATE(CONCAT(year,',',month,',',1),'%Y,%m,%d'))), '0001-01-01') FROM domotique_water_month)
			AND date < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')
		GROUP BY
			YEAR(DATE),
			MONTH(date),
			device_id
	")->execute();

	//*** CO2
	$bdd->prepare("
		INSERT INTO domotique_co2_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_co2
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_co2_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_co2 WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_co2")->execute();

	//*** Pressure
	$bdd->prepare("
		INSERT INTO domotique_pressure_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_pressure
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_pressure_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_pressure WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_pressure")->execute();

	//*** Noise
	$bdd->prepare("
		INSERT INTO domotique_noise_day (date, device_id, min_value, avg_value, max_value)
		SELECT
			DATE(time) AS date,
			device_id as device_id,
			MIN(value) AS min_value,
			AVG(value) AS avg_value,
			MAX(value) AS max_value
		FROM
			domotique_noise
		WHERE
			DATE(time) > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_noise_day )
			AND DATE(time) < CURDATE()
		GROUP BY
			date,
			device_id
	")->execute();
	$bdd->prepare("DELETE FROM domotique_noise WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_noise")->execute();

	//*** Rain
	$bdd->prepare("DELETE FROM domotique_rain WHERE DATE(time) < SUBDATE(CURDATE(), 21)")->execute();
	$bdd->prepare("OPTIMIZE TABLE domotique_rain")->execute();

}
catch (Exception $e) {
	echo $e->getMessage();
}
?>
