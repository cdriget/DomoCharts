<?php
/******************************************************************************/
/*** File    : config.inc.php                                               ***/
/*** Author  : Christophe DRIGET                                            ***/
/*** Version : 5.0                                                          ***/
/*** History : February 2014  : Initial release                             ***/
/***         : September 2015 : Add Teleinfo                                ***/
/*** Note    : Configuration file                                           ***/
/******************************************************************************/

// MySQL Server hostname or IP address
$server = 'server';
// MySQL User account
$login = 'user';
// MySQL User password
$password = 'password';
// MySQL Database name
$database = 'domotique';


// Maximum number of days to display in graphs
// Note : increasing this number may considerably slow down graph generation time
$interval = 7; // DAY


//*** Teleinfo time for energy
//
// Variable = 'Value'           // Comment                                    : Allowed values
// --------   -------           // ------------------------------------------ : ---------------
$TimeSource = 'STATIC';         // Source of information for date and time    : TELEINFO|STATIC
// ---------------------------- // Valid only if TELEINFO TimeSource is used  : ---------------
$teleinfoTable = 'teleinfo';    // MySQL table name                           : xxxxxxxx
$teleinfoDelay = 60;            // Teleinfo Delay in Seconds                  : ss
// ---------------------------- // Valid only if STATIC TimeSource is used    : ---------------
$TimeHCHP = array(              // HC/HP start times for each EDF rate        : 'hh:mm' => '<BASE|HC|HP>'
	'00:00' => 'HC',
	'06:30' => 'HP',
	'22:30' => 'HC'
);
/*$TimeHCHP = array(              // HC/HP start times for each EDF rate        : 'hh:mm' => '<BASE|HC|HP>'
	'00:00' => 'BASE'
);*/

?>
