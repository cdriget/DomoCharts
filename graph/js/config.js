
// User variables
var consoGeneral = "General";
var currentSensorType = 'temperature';
var currentGraphType = 'line';
var originalGraphType = 'line';
//var currentGroupingData = 1;
//var currentTypeGrouping = 'hour'
var chartsConfig = [
	{type:'temperature', title: 'Température', yaxis: 'Température (°C)', tooltip: '°C'},
	{type:'temperature_day', title: 'Historique de température (moyenne journalière)', yaxis: 'Température (°C)', tooltip: '°C'},
	{type:'temperature_month', title: 'Historique de température (moyenne mensuelle)', yaxis: 'Température (°C)', tooltip: '°C'},

	{type:'humidity', title: 'Humidité', yaxis: 'Humidité (%)', tooltip: '%', min: 0, max: 100,
		zones: [{color: '#FFE6E6', from: 0, to: 30}, {color: '#FAFABB', from: 30, to: 40}, {color: '#CCFFCC', from: 40, to: 60}, {color: '#FAFABB', from: 60, to: 70}, {color: '#FFE6E6', from: 70, to: 100}]
	},
	{type:'humidity_day', title: "Historique d'humidité (moyenne journalière)", yaxis: 'Humidité (%)', tooltip: '%', min: 0, max: 100,
		zones: [{color: '#FFE6E6', from: 0, to: 30}, {color: '#FAFABB', from: 30, to: 40}, {color: '#CCFFCC', from: 40, to: 60}, {color: '#FAFABB', from: 60, to: 70}, {color: '#FFE6E6', from: 70, to: 100}]
	},
	{type:'humidity_month', title: "Historique d'humidité (moyenne mensuelle)", yaxis: 'Humidité (%)', tooltip: '%', min: 0, max: 100,
		zones: [{color: '#FFE6E6', from: 0, to: 30}, {color: '#FAFABB', from: 30, to: 40}, {color: '#CCFFCC', from: 40, to: 60}, {color: '#FAFABB', from: 60, to: 70}, {color: '#FFE6E6', from: 70, to: 100}]
	},

	{type:'power', title: 'Consommation électrique', yaxis: 'Consommation (Watt)', tooltip: 'W', min: 0},
	{type:'power_day', title: 'Historique de consommation électrique', yaxis: 'Consommation (Watt)', tooltip: 'W', min: 0},
	{type:'battery_day', title: 'Historique de batterie (moyenne journalière)', yaxis: 'Niveau (%)', tooltip: '%', min: 0, max: 100},
	{type:'light', title: 'Luminosité', yaxis: 'Luminosité (Lux)', tooltip: 'lux', min: 0},
	{type:'light_day', title: "Historique de luminosité (moyenne journalière)", yaxis: 'Luminosité (Lux)', tooltip: 'lux', min: 0},
	{type:'energy_day', title: "Historique de consommation d'énergie électrique (total journalier)", yaxis: 'Energie (kWh)', tooltip: 'kWh', min: 0},
	{type:'water_day', title: "Historique de consommation d'eau (total journalier)", yaxis: 'Eau (Litres)', tooltip: 'l', min: 0},
	{type:'water_month', title: "Historique de consommation d'eau (total mensuel)", yaxis: 'Eau (Litres)', tooltip: 'l', min: 0},
	{type:'co2', title: 'CO2', yaxis: 'CO2 (ppm)', tooltip: 'ppm', min: 0,
		zones: [{color: '#CCFFCC', from: 0, to: 1000}, {color: '#FAFABB', from: 1000, to: 2000}, {color: '#FFE6E6', from: 2000, to: 3000}]
	},
	{type:'co2_day', title: 'Historique de CO2 (moyenne journalière)', yaxis: 'CO2 (ppm)', tooltip: 'ppm', min: 0,
		zones: [{color: '#CCFFCC', from: 0, to: 1000}, {color: '#FAFABB', from: 1000, to: 2000}, {color: '#FFE6E6', from: 2000, to: 3000}]
	},
	{type:'pressure', title: 'Pression atmosphérique', yaxis: 'Pression (hPa)', tooltip: 'hPa',
		zones: [{color: '#FFE6E6', from: 868.5, to: 980}, {color: '#FAFABB', from: 980, to: 1013.25}, {color: '#CCFFCC', from: 1013.25, to: 1086.8}]
	},
	{type:'pressure_day', title: 'Historique de pression atmosphérique (moyenne journalière)', yaxis: 'Pression (hPa)', tooltip: 'hPa',
		zones: [{color: '#FFE6E6', from: 868.5, to: 980}, {color: '#FAFABB', from: 980, to: 1013.25}, {color: '#CCFFCC', from: 1013.25, to: 1086.8}]
	},
	{type:'noise', title: 'Bruit', yaxis: 'Bruit (dB)', tooltip: 'dB', min: 0,
		zones: [{color: '#CCFFCC', from: 0, to: 45}, {color: '#FAFABB', from: 45, to: 65}, {color: '#FFE6E6', from: 65, to: 140}]
	},
	{type:'noise_day', title: 'Historique de bruit (moyenne journalière)', yaxis: 'Bruit (dB)', tooltip: 'dB', min: 0,
		zones: [{color: '#CCFFCC', from: 0, to: 45}, {color: '#FAFABB', from: 45, to: 65}, {color: '#FFE6E6', from: 65, to: 140}]
	},
	{type:'rain', title: 'Pluie', yaxis: 'Pluie (mm)', tooltip: 'mm', min: 0},
	{type:'rain_day', title: 'Historique de pluie (moyenne journalière)', yaxis: 'Pluie (mm)', tooltip: 'mm', min: 0}
]
var rangeselector = {
	buttons: [
		{type: 'hour', count: 6,text: '6h'},
		{type: 'hour', count: 12, text: '12h'},
		{type: 'hour', count: 24, text: '24h'},
		{type: 'day', count: 2, text: '2d'},
		{type: 'day', count: 3, text: '3d'},
		{type: 'week', count: 1, text: '1w'},
		{type: 'all', text: 'All'}
	],
	selected: 6
}
