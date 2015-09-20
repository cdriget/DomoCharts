<?php
/******************************************************************************/
/*** File    : install.php                                                  ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : September 2015  : Initial release                            ***/
/*** Note    : Script to install/migrate database                           ***/
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

/*function fatal_handler() {
	$error = error_get_last();
	if( $error !== NULL) {
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		$response = array();
		$response['success'] = false;
		$response['error']['code'] = $errno;
		$response['error']['message'] = $errstr;
		if (DEBUG)
			header('Content-type: text/html; charset=utf-8');
		else
			header('Content-type: application/json; charset=utf-8');
		echo json_encode($response, JSON_NUMERIC_CHECK);
	}
}
register_shutdown_function('fatal_handler');*/


//*** 
//*** Function ExecuteQuery()
//***
function ExecuteQuery($bdd, $query) {
		if (DEBUG)
			echo $query.PHP_EOL;
		$sql = $bdd->prepare($query);
		//$sql->debugDumpParams();
		if ( $sql->execute() ) {
			if (DEBUG)
				echo 'OK'.PHP_EOL;
		}
		else {
			$erreur = $sql->errorInfo();
			if (!DEBUG)
				echo $query.PHP_EOL;
			echo 'MySQL Error #'.$erreur[1].' : SQLSTATE['.$erreur[0].'] '.$erreur[2].PHP_EOL;
		}
}


//*** 
//*** Main loop
//***
try {

	//*** Raw text format
	echo '<pre>'.PHP_EOL;

	//*** Include necessary files
	require 'config.inc.php';

	//*** MySQL connection
	if ( ! isset($bdd) )
		$bdd = new PDO('mysql:host='.$server.';charset=UTF8', $login, $password);

	//*** Create Database
	$sql = $bdd->prepare("SELECT count(*) as Count FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=:database");
	$sql->execute(array('database' => $database));
	if ($sql->fetchColumn() < 1) {
		$sql = 'CREATE DATABASE IF NOT EXISTS `'.$database.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;';
		ExecuteQuery($bdd, $sql);
	}
	elseif (DEBUG)
		echo 'Database '.$database.' already exists'.PHP_EOL;

	//*** Use database
	$sql = 'USE `'.$database.'`;';
	ExecuteQuery($bdd, $sql);

	//*** Read SQL file
	$sql_file = 'database.sql';
	$contents = file_get_contents($sql_file);
	//if (DEBUG)
		//echo $contents;

	//*** Force use of Unix style newlines
	$contents = preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $contents);

	//*** Remove comments
	$comment_patterns = array('/\/\*.*(\n)*.*(\*\/)?/', //C comments
														'/\s*--.*\n/', //inline comments start with --
														'/\s*#.*\n/', //inline comments start with #
														);
	$contents = preg_replace($comment_patterns, "\n", $contents);
	//if (DEBUG)
		//echo $contents;

	//*** Parse queries
	$statements = explode(";\n", $contents);
	$statements = preg_replace('/\s/', ' ', $statements);
	//print_r($statements);
	foreach ($statements as $query) {

		$query = trim($query);
		if (DEBUG)
			echo 'Query : ' . $query . PHP_EOL;

		if ($query != '') {

			//*** Create table
			if ( strstr($query, 'CREATE TABLE') ) {
				//echo 'CREATE TABLE'.PHP_EOL;
				ExecuteQuery($bdd, $query);
			}

			//*** Modify table
			elseif ( strstr($query, 'ALTER TABLE') ) {

				//*** Add index or column
				if ( strstr($query, 'ADD') ) {

					//echo 'ALTER TABLE ... ADD'.PHP_EOL;
					$substring = explode('ADD', $query);
					$tablename = '';
					foreach ($substring as $key => $value) {
						//if (DEBUG)
							//echo $value.PHP_EOL;
						if ($key == 0) {
							$arr = explode('ALTER TABLE', $value);
							$tablename = str_replace('`', '', trim($arr[1]));
							//echo 'tablename = '.$tablename.PHP_EOL;
						}
						elseif ($key > 0) {
							if ( strstr($value, 'PRIMARY KEY') ) {
								//echo 'PRIMARY KEY'.PHP_EOL;
								$sql = $bdd->prepare("SELECT count(*) as CountIndex FROM information_schema.STATISTICS WHERE table_schema=:database AND table_name=:tablename AND index_name='PRIMARY'");
								$sql->execute(array(
									'database' => $database,
									'tablename' => $tablename)
								);
								$data = $sql->fetch(PDO::FETCH_ASSOC);
								//echo $data['CountIndex'].PHP_EOL;
								if ( $data['CountIndex'] == 0 ) {
									$sql = 'ALTER TABLE `'.$tablename.'` ADD '.rtrim(trim($value), ',');
									ExecuteQuery($bdd, $sql);
								}
							}
							elseif ( strstr($value, 'UNIQUE KEY') ) {
								//echo 'UNIQUE KEY'.PHP_EOL;
								$arr = explode(' ', trim($value));
								$indexname = str_replace('`', '', trim($arr[2]));
								//echo 'indexname = '.$indexname.PHP_EOL;
								$sql = $bdd->prepare('SELECT count(*) as CountIndex FROM information_schema.STATISTICS WHERE table_schema=:database AND table_name=:tablename AND index_name=:indexname');
								$sql->execute(array(
									'database' => $database,
									'tablename' => $tablename,
									'indexname' => $indexname)
								);
								$data = $sql->fetch(PDO::FETCH_ASSOC);
								//echo $data['CountIndex'].PHP_EOL;
								if ( $data['CountIndex'] == 0 ) {
									$sql = 'ALTER TABLE `'.$tablename.'` ADD '.rtrim(trim($value), ',');
									ExecuteQuery($bdd, $sql);
								}
							}
							elseif ( strstr($value, 'KEY') ) {
								//echo 'KEY'.PHP_EOL;
								$arr = explode(' ', trim($value));
								$indexname = str_replace('`', '', trim($arr[1]));
								//echo 'indexname = '.$indexname.PHP_EOL;
								$sql = $bdd->prepare('SELECT count(*) as CountIndex FROM information_schema.STATISTICS WHERE table_schema=:database AND table_name=:tablename AND index_name=:indexname');
								$sql->execute(array(
									'database' => $database,
									'tablename' => $tablename,
									'indexname' => $indexname)
								);
								$data = $sql->fetch(PDO::FETCH_ASSOC);
								//echo $data['CountIndex'].PHP_EOL;
								if ( $data['CountIndex'] == 0 ) {
									$sql = 'ALTER TABLE `'.$tablename.'` ADD '.rtrim(trim($value), ',');
									ExecuteQuery($bdd, $sql);
								}
							}
							elseif ( strstr($value, 'COLUMN') ) {
								$arr = explode(' ', trim($value));
								$columnname = str_replace('`', '', trim($arr[1]));
								//echo 'columnname = '.$columnname.PHP_EOL;
								$sql = $bdd->prepare('SELECT count(*) as CountIndex FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=:database AND TABLE_NAME=:tablename AND COLUMN_NAME=:columnname');
								$sql->execute(array(
									'database' => $database,
									'tablename' => $tablename,
									'columnname' => $columnname)
								);
								$data = $sql->fetch(PDO::FETCH_ASSOC);
								//echo $data['CountIndex'].PHP_EOL;
								if ( $data['CountIndex'] == 0 ) {
									$sql = 'ALTER TABLE `'.$tablename.'` ADD '.rtrim(trim($value), ',');
									ExecuteQuery($bdd, $sql);
								}
							}
							else {
								echo 'ALTER TABLE ... ADD ???'.PHP_EOL;
							}
						}
						else
							echo 'Error : key'.PHP_EOL;
					}

				}

				//*** Modify column type
				elseif ( strstr($query, 'MODIFY') ) {
					//echo 'ALTER TABLE ... MODIFY'.PHP_EOL;
					ExecuteQuery($bdd, $query);
				}

				//*** Change structure
				elseif ( strstr($query, 'CHANGE COLUMN') ) {
					//echo 'ALTER TABLE ... CHANGE COLUMN'.PHP_EOL;
					$arr = explode(' ', trim($query));
					$tablename = str_replace('`', '', trim($arr[2]));
					$columnname = str_replace('`', '', trim($arr[5]));
					$sql = $bdd->prepare('SELECT count(*) as CountIndex FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=:database AND TABLE_NAME=:tablename AND COLUMN_NAME=:columnname');
					$sql->execute(array(
						'database' => $database,
						'tablename' => $tablename,
						'columnname' => $columnname)
					);
					$data = $sql->fetch(PDO::FETCH_ASSOC);
					//echo $data['CountIndex'].PHP_EOL;
					if ( $data['CountIndex'] > 0 ) {
						ExecuteQuery($bdd, $query);
					}
				}

				//*** Remove column
				elseif ( strstr($query, 'DROP COLUMN') ) {
					//echo 'ALTER TABLE ... DROP COLUMN'.PHP_EOL;
					$arr = explode(' ', trim($query));
					$tablename = str_replace('`', '', trim($arr[2]));
					$columnname = str_replace('`', '', trim($arr[5]));
					$sql = $bdd->prepare('SELECT count(*) as CountIndex FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=:database AND TABLE_NAME=:tablename AND COLUMN_NAME=:columnname');
					$sql->execute(array(
						'database' => $database,
						'tablename' => $tablename,
						'columnname' => $columnname)
					);
					$data = $sql->fetch(PDO::FETCH_ASSOC);
					//echo $data['CountIndex'].PHP_EOL;
					if ( $data['CountIndex'] > 0 ) {
						ExecuteQuery($bdd, $query);
					}
				}

				else {
					echo 'ALTER TABLE ... ???'.PHP_EOL;
					echo $query.PHP_EOL;
				}
			}

			//*** Drop table
			elseif ( strstr($query, 'DROP TABLE') ) {
				//echo 'DROP TABLE'.PHP_EOL;
				ExecuteQuery($bdd, $query);
			}

			else {
				echo 'Error : ???'.PHP_EOL;
				echo $query.PHP_EOL;
			}

		}
		//else
			//echo 'Error : empty query'.PHP_EOL;

	}

	//*** Cleanup
	$bdd = null;

	//*** Raw text format
	echo 'Finished'.PHP_EOL;
	echo '</pre>'.PHP_EOL;

}
catch(Exception $e) {
	die('Error : '.$e->getMessage());
}
?>
