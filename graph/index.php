<?php header('Access-Control-Allow-Origin: *'); ?>

<!DOCTYPE HTML>
<html style="height: 98%">
	<head>
		<title>DomoCharts</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="application-name" content="DomoCharts">
		<meta name="author" content="Christophe DRIGET">
		<meta name="description" content="Graphiques domotique">
		<meta name="keywords" content="Graphique,Chart,Domotique,Fibaro">
		<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
		<script src="js/config.js"></script>
		<script src="js/graph.js"></script>
		<script src="js/highchart/highstock.js"></script>
		<script src="js/highchart/modules/exporting.js"></script>
		<script src="js/highchart/highcharts-more.js"></script>
		<!-- Additional files for the Highslide popup effect -->
		<script type="text/javascript" src="js/highchart/highslide-full.min.js"></script>
		<script type="text/javascript" src="js/highchart/highslide.config.js" charset="utf-8"></script>
		<link rel="stylesheet" type="text/css" href="js/highchart/highslide.css" />
	</head>

	<body bgcolor="#000000" style="height: 100%">

		<div id="container" style="height: 95%; width: 100%"></div>

		<font color=FFFFFF>Type: </a></font>
		<select id="update">
			<option value="temperature">Température [°C]</option>
			<option value="temperature_day">Température [°C] (moyenne journalière)</option>
			<option value="temperature_month">Température [°C] (moyenne mensuelle)</option>
			<option value="humidity">Humidité [%]</option>
			<option value="humidity_day">Humidité [%] (moyenne journalière)</option>
			<option value="humidity_month">Humidité [%] (moyenne mensuelle)</option>
			<option value="power">Puissance [W]</option>
			<option value="power_day">Puissance [W] (moyenne journalière)</option>
			<!-- <option value="battery">Batterie [%]</option> --> <!-- Removed in DomoCharts v5.0 -->
			<option value="battery_day">Batterie [%] (moyenne journalière)</option>
			<option value="light">Luminosité [Lux]</option>
			<option value="light_day">Luminosité [Lux] (moyenne journalière)</option>
			<option value="energy_day">Energie [Wh] (moyenne journalière)</option>
			<option value="water_day">Eau [L] (moyenne journalière)</option>
			<option value="water_month">Eau [L] (moyenne mensuelle)</option>
			<option value="co2">CO2 [ppm]</option>
			<option value="co2_day">CO2 [ppm] (moyenne journalière)</option>
			<option value="pressure">Pression atmosphérique [hPa]</option>
			<option value="pressure_day">Pression atmosphérique [hPa] (moyenne journalière)</option>
			<option value="noise">Bruit [dB]</option>
			<option value="noise_day">Bruit [dB] (moyenne journalière)</option>
			<option value="rain">Pluie [mm]</option>
			<!-- <option value="rain_day">Pluie [mm] (moyenne journalière)</option> --> <!-- Not yet available / planned for future release -->
		</select>

		<font color=FFFFFF>Graph: </a></font>
		<select id="chartType">
			<option value="spline">spline</option>
			<option value="line">line</option>
			<option value="column">column</option>
			<option value="stacked column">stacked column</option>
		</select>

<!--
		<font color=FFFFFF>Grouping: </a></font>
		<select id="data-grouping-type">
			<option value="month">month</option>
			<option value="week">week</option>
			<option value="day">day</option>
			<option value="hour">hour</option>
			<option value="mintute">minute</option>
		</select>
		
		<font color=FFFFFF>Interval: </a></font>
		<select id="data-grouping-interval">
			<option value="1">defaut 15</option>
			<option value="1">1</option>
			<option value="5">6</option>
			<option value="15">15</option>
			<option value="30">24</option>
			<option value="60">30</option>
		</select>
 -->

		<button id="data-labels">Toggle data labels</button>
		<button id="markers">Toggle point markers</button>
		<button id="hide-show">Toggle series visibility</button>

	</body>
</html>