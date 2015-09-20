<?php
/******************************************************************************/
/*** File    : teleinfo_energy_get.php                                      ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : Jul - Aug - Sep 2015 : Initial release                       ***/
/*** Note    : Extract date & times from teleinfo table                     ***/
/******************************************************************************/

//*** Sample URL
// /graph/teleinfo_energy_get.php                                 => From last day in energy_day table to Yesterday
// /graph/teleinfo_energy_get.php?begin=1438898400&end=1438984800 => From 2015-08-07 to 2015-08-08
//
//*** Sample JSON return data
//    {"success":true,"rowcount":3,"data":{"2015-09-19":[[1442613600,"HC"],[1442637215,"HP"],[1442694830,"HC"]]}}
//    {"success":false,"error":{"code":1,"message":"This is not a GET request."}}

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
	$data = array();

	//*** Check
	if ($_SERVER['REQUEST_METHOD'] != 'GET')
		throw new Exception('This is not a GET request.', 1);
	if ( ! function_exists('json_encode') )
		throw new Exception('Server does not support JSON.', 3);

	//*** Get URL parameters
	$begin = filter_input(INPUT_GET, 'begin', FILTER_SANITIZE_NUMBER_INT);
	$end   = filter_input(INPUT_GET, 'end'  , FILTER_SANITIZE_NUMBER_INT);

	//*** Check URL parameters
	if ($begin != NULL)
		$ts_begin = intval($begin);

	if ($end == NULL)  // If "end" parameter is missing or is defined to "now", then use current timestamp
		$ts_end = time();
	else
		$ts_end = intval($end);


	//***
	//*** TimeSource : TELEINFO
	//***
	if ($TimeSource == 'TELEINFO') {

		//*** Prepare SQL statement
		if (isset($ts_begin)) {
			$SQLclause_ts = 'timestp BETWEEN :ts_begin AND :ts_end';
			$data['ts_begin'] = $ts_begin;
			$data['ts_end'] = $ts_end;
		}
		else
			$SQLclause_ts = "rec_date > ( SELECT COALESCE(MAX(`date`), '0001-01-01') FROM domotique_energy_day ) AND rec_date < CURDATE()";

		//*** Construct SQL statement
		$query = "
			select
				min(timestp) as timestp,
				rec_date,
				TIME(FROM_UNIXTIME(MIN(timestp))) AS debut,
				group_concat( distinct T1_PTEC ) as T1_PTECs
			from
				(
					select
						t.timestp,
						t.rec_date,
						t.rec_time,
						t.T1_PTEC,
						@lastSeq := if ( t.T1_PTEC = @lastPTEC AND t.rec_date = @lastDate, @lastSeq, @lastSeq + 1 ) as seq,
						@lastDate := t.rec_date,
						@lastPTEC := t.T1_PTEC
					from
						$teleinfoTable t,
						( select
								@lastDate := ' ',
								@lastPTEC := ' ',
								@lastSeq := 0
						) sqlVars
					where ".$SQLclause_ts."
					order by
						t.timestp
				) PreQuery
			group by
				rec_date,
				seq
			order by
				timestp
			";

		//*** MySQL connection
		if ( ! isset($bdd) )
			$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);

		$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$bdd->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		//*** Debug
		if (DEBUG) {
			if (isset($ts_begin))
				echo 'ts_begin = ' .$ts_begin .'<br/>'.PHP_EOL;
			if (isset($ts_end))
				echo 'ts_end = '   .$ts_end   .'<br/>'.PHP_EOL;
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
		if ( ! $sql )
			throw new Exception('SQL query error', 4);

		//*** Prepare response table
		$response['success'] = true;
		$response['rowcount'] = $sql->rowCount();

		//*** Retrieve data
		while ( $data = $sql->fetch(PDO::FETCH_ASSOC) )
			$response['data'][$data['rec_date']][] = array($data['timestp'], $data['T1_PTECs']);
			//$response['data'][$data['rec_date']][$data['timestp']] = $data['T1_PTECs'];

		//*** Debug
		if (DEBUG) {
			echo "<pre>\r\n";
			print_r($response);
			echo "</pre>\r\n";
		}

		//*** Compute times
		if (isset($response['data'])) {
			foreach ($response['data'] as $jour => $values) {
				foreach ($values as $key => $value) {
					if ($key == 0) {
						// Begin of day
						$response['data'][$jour][$key][0] = mktime( 0, 0, 0, date('n', $value[0]), date('j', $value[0]), date('Y', $value[0]) );
						if (DEBUG)
							echo "<br/>\r\n".$jour.' - '.$response['data'][$jour][$key][1].' : '.date('H:i:s', $response['data'][$jour][$key][0])."<br/>\n";
					}
					else {
						// Time - delay (30s)
						$response['data'][$jour][$key][0] = mktime( date('H', $value[0]), date('i', $value[0]), date('s', $value[0])-$teleinfoDelay, date('n', $value[0]), date('j', $value[0]), date('Y', $value[0]) );
						if (DEBUG)
							echo $jour.' - '.$response['data'][$jour][$key][1].' : '.date('H:i:s', $response['data'][$jour][$key][0]).' - '.date('H:i:s', $response['data'][$jour][$key][0]-$response['data'][$jour][$key-1][0])."<br/>\n";
					}
				}
			}
		}

		//*** Cleanup
		$bdd = null;

	}


	//***
	//*** TimeSource : STATIC
	//***
	elseif ($TimeSource == 'STATIC') {

		if (isset($ts_begin)) {
			//*** Get start day from given parameter
			$startDay = mktime(0, 0, 0, date('n', $ts_begin), date('j', $ts_begin), date('Y', $ts_begin) );
			$lastDay = mktime(0, 0, 0, date('n', $ts_end), date('j', $ts_end), date('Y', $ts_end) );
		}
		else {
			//*** Retrieve start day from SQL database
			if ( ! isset($bdd) )
				$bdd = new PDO('mysql:host='.$server.';dbname='.$database.';charset=UTF8', $login, $password);
			$sql = $bdd->prepare('SELECT UNIX_TIMESTAMP(COALESCE(MAX(`date`), DATE_SUB(curdate(), INTERVAL 1 MONTH))) FROM domotique_energy_day;');
			if ( ! $sql->execute() )
				throw new Exception('SQL query error', 4);
			$day = $sql->fetchColumn();
			if (DEBUG)
				echo 'SQL day = '. $day.'<br/>'.PHP_EOL;
			$startDay = mktime(0, 0, 0, date('n', $day), date('j', $day)+1, date('Y', $day) );
			$bdd = null;
			$lastDay = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		}

		//*** Debug
		if (DEBUG) {
			echo 'startDay = '. $startDay .' '. date('d/m/Y H:i:s e', $startDay) .'<br/>'.PHP_EOL;
			echo 'lastDay = '. $lastDay .' '. date('d/m/Y H:i:s e', $lastDay) .'<br/>'.PHP_EOL;
			echo '<br/>'.PHP_EOL;
		}

		//*** Prepare response table
		$response['success'] = true;
		$response['rowcount'] = 0;

		while ($startDay < $lastDay) {

			//*** Compute time
			$date = date('Y-m-d', $startDay);

			foreach ($TimeHCHP as $heure => $tarif) {

				//*** Compute times
				$explTime = explode(':', $heure);
				$start = mktime( $explTime[0], $explTime[1], 0, date('n', $startDay), date('j', $startDay), date('Y', $startDay) );

				// Test de la première heure
				if ( ( ! isset($response['data'][$date]) ) && ( $start > $startDay ) )
					throw new Exception('Pas de tarif donné pour 00:00', 5);

				//*** Add value to response table
				$response['data'][$date][] = array($start, $tarif);

				//*** Debug
				if (DEBUG) {
					echo 'Heure : '.$heure.' - Tarif : '.$tarif.'<br/>'.PHP_EOL;
					echo 'start = '. $start .' '. date('d/m/Y H:i:s e', $start) .'<br/>'.PHP_EOL;
				}
			}

			//*** Next day
			$startDay = mktime( 0, 0, 0, date('n', $startDay), date('j', $startDay) + 1, date('Y', $startDay) );
			$response['rowcount']++;
		}

	}


	//***
	//*** TimeSource : ???
	//***
	else
		throw new Exception('Invalid value for TimeSource parameter', 6);

}
//*** 
//*** Exception handling
//***
catch (Exception $e) {
	unset($response['rowcount'], $response['data']);
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
echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

?>
