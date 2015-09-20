<?php
/******************************************************************************/
/*** File    : functions.inc.php                                            ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : April 2015 : Initial release                                 ***/
/*** Note    : Common functions                                             ***/
/******************************************************************************/


//*** 
//*** Function DebugPDOSQL()
//***
function DebugPDOSQL($query, $data) {
	$indexed = $data == array_values($data);
	foreach ($data as $k => $v) {
		if (is_string($v))
			$v = "'$v'";
		if ($v === null)
			$v = 'NULL';
		if ($indexed)
			$query = preg_replace('/\?/', $v, $query, 1);
		else
			$query = str_replace(':'.$k, $v, $query);
	}
	return $query;
}


//*** 
//*** Function DEBUG_MYSQL()
//***
function DEBUG_MYSQL($bdd, $query, $data = null) {
	//echo "<pre>\r\n\r\n".$query."\r\n\r\n</pre>\r\n<br/><br/>\r\n";
	echo "<pre>\r\n\r\n".DebugPDOSQL($query,$data)."\r\n\r\n</pre>\r\n<br/><br/>\r\n";
	//$sql = $bdd->query($query);
	$sql = $bdd->prepare($query);
	$sql->execute($data);
	echo "<pre>\r\n\r\n";
	print_r($sql->fetchAll(PDO::FETCH_NUM));
	echo "\r\n</pre>\r\n<br/>\r\n";
}


//***
//*** Function json_last_error_msg()
//***
if (!function_exists('json_last_error_msg')) {
	function json_last_error_msg() {
		static $json_errors = array(
			JSON_ERROR_NONE => 'No error has occurred',
			JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
			JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
			JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
			JSON_ERROR_SYNTAX => 'Syntax error',
			JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
			JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
			JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
			JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given'
		);
		$error = json_last_error();
		return array_key_exists($error, $json_errors) ? $json_errors[$error] : 'Unknown JSON error';
	}
}

?>
