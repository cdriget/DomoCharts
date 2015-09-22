
// System variables
var currentDevices = null;
var currentSeries = null;
var currentMarkers = false;
var dataLabels = false;
var currentStackStatus = '';
var valMin = 9999;
var valMax = 0;
var chart;

function getChartConfig(type) {
	for (i = 0; i < chartsConfig.length; i++) {
		if (chartsConfig[i].type == type)
			return chartsConfig[i];
	}
	return null;
}

function calMinMax (data) {
	for (i = 0; i < data.length; i++) {
		if (data[i][1] > valMax)
			valMax = parseInt(data[i][1]);
		if (data[i][1] < valMin)
			valMin = parseInt(data[i][1]);
	}
}

function typecourbe(device) {
	if (device[1] == consoGeneral)
		currentGraphType = 'line';
	else
		currentGraphType = originalGraphType;
	console.log('Courbe ' + device[1] + ': type = ' + currentGraphType);
	return currentGraphType
}

// create the chart when all data is loaded
function createChart() {
	Highcharts.setOptions({
		global: {
			useUTC: false
		},
		lang: {
			weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
		}
	});
	chart = new Highcharts.StockChart({ 
		chart:{
			animation: {
				duration: 1000
			},
			zoomType: 'x',
			renderTo : 'container',
			events: {
				load: function(event) {
					console.log('Chart loaded');
				},
				redraw: function() {
					console.log('Chart redraw');
				}
			}
		},
		rangeSelector: rangeselector,
		legend: {
			enabled: true,
		},
		yAxis: {
			min: getChartConfig(currentSensorType).min,
			minPadding: 0,
			max: getChartConfig(currentSensorType).max,
			maxPadding: 0,
			title: {
				text: getChartConfig(currentSensorType).yaxis
			},
			stackLabels: {
				enabled: dataLabels,
				style: {
					fontWeight: 'bold',
					color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
				},
				formatter: function() {
					return  Highcharts.numberFormat(this.total, 0, ',');
				}
			},
			plotLines : [{
				value : valMin,
				color : 'green',
				dashStyle : 'shortdash',
				width : 1,
				label : {
					text : 'minimum: ' + valMin + getChartConfig(currentSensorType).tooltip
				}
			}, {
				value : valMin,
				color : 'red',
				dashStyle : 'shortdash',
				width : 1,
				label : {
					text : 'maximum: ' + valMax + getChartConfig(currentSensorType).tooltip
				}
			}],
			plotBands: getChartConfig(currentSensorType).zones
		},
		xAxis: {
			gridLineDashStyle: 'ShortDot',
			gridLineWidth: 1,
			type: 'datetime'
		},
		title: {
			text: getChartConfig(currentSensorType).title
		},
		plotOptions: {
			column: {
				stacking: currentStackStatus
			},
			series: {
				cursor: 'pointer',
				point: {
					events: {
						click: clickableseries,
						remove: function() {return true;}
					}
				},
				marker: {
					lineWidth: 1
				}
			}
		},
		tooltip: {
			shared: true,
			crosshairs: true,
			valueDecimals: 1,
			valueSuffix: getChartConfig(currentSensorType).tooltip
		},
		scrollbar : {
			enabled : false
		},
		series: currentSeries
	});
	console.log('display chart');
};

function loadDataFromType(sensorType) {
	$('#container').html('<table width="100%" height="100%"><tr><td valign="center" align="center"><font size=15 color="#ffffff">Loading, please wait...</font></td></tr></table>');
	valMin = 9999;
	valMax = 0;
	console.log('Load data from type : ' + sensorType);
	currentDevices = [];
	currentSeries = [];

	$.getJSON('device_get.php?type=' + sensorType + '&callback=?', function(data) {
		var series = [],
		yAxisOptions = [],
		seriesCounter = 0;
		//colors = Highcharts.getOptions().colors;
		currentDevices = data;
		if (currentDevices.length>0) {
			$.each(currentDevices, function(i, device) {
				//console.log('i='+i+' device='+device );
				loadDataFromDevice(i, device, sensorType);
			});
		}
		else {
			console.log('No device found');
			$('#container').html('<table width="100%" height="100%"><tr><td valign="center" align="center"><font color="red">No device found</font></td></tr></table>');
		}
	})
	.fail(function(data) {
		console.log(data.responseText);
		$('#container').html('<table width="100%" height="100%"><tr><td valign="center" align="center"><font color="red">'+data.responseText+'</font></td></tr></table>');
	});
}

function loadDataFromDevice(i, device, sensorType) {
	console.log('Load data from device : ' + i + ' ' + device);
	$.getJSON('data_get.php?query=dataserie&device=' + device[0] + '&type=' + sensorType + '&callback=?', function(data) {
		//console.log(data);
		currentSeries.push({
			id: device[0],
			index: i,
			name: device[1],
			sensorType: sensorType,
			color: device[2].length == 7 ? device[2] : '',
			data: data,
			marker: {
				enabled: currentMarkers
			},
			//dataGrouping: {approximation: "average", units:[[currentTypeGrouping, [currentGroupingData]]]},
			//dataGrouping: {units:[['minute', [5]]]},
			//dataGrouping: {enabled: false},
			type: typecourbe(device)
		});
		calMinMax(data);
		//console.log('currentSeries.length=' + currentSeries.length + ' currentDevices.length=' + currentDevices.length);
		//$('#container').html('<table width="100%" height="100%"><tr><td valign="center" align="center"><font size=15 color="#ffffff">'+currentSeries.length+' / '+currentDevices.length+'</font></td></tr></table>');
		if ( currentSeries.length == currentDevices.length )
			createChart();
	});
}

function clickableseries() {
	//console.log(this);
	var deleteButton = '';
	for (var i=0; i<this.series.xData.length; i++) {
		if (this.series.xData[i] == this.x && this.series.yData[i] == this.y) {
			console.log('Found x index : ' + i + ' for series index : ' + this.series.index + ', deviceid=' + this.series.options.id + ', type=' + this.series.options.sensorType);
			//deleteButton = '<br/><input type="button" onClick="deletePoint('+this.series.index+','+this.series.options.id+','+this.x+') ? parent.window.hs.close() : console.log(\'false\');" value="delete">';
			deleteButton = '<br/><input type="button" onClick="deletePoint('+this.series.index+','+this.series.options.id+','+this.x+',\''+this.series.options.sensorType+'\'); parent.window.hs.close();" value="delete">';
			break;
		}
	}
	//console.log('deleteButton = ' + deleteButton);
	hs.htmlExpand(null, {
	//hs.htmlExpand(document.getElementById(chart.renderTo), {
		pageOrigin: {
			x: this.plotX,
			y: this.plotY
		},
		headingText: this.series.name,
		//maincontentText: Highcharts.dateFormat('%A, %b %e, %Y', this.x) + ':<br/> ' + Highcharts.numberFormat(this.y, 0, ',') + getChartConfig(currentSensorType).tooltip,
		maincontentText: Highcharts.dateFormat('%A %e/%m/%Y %H:%M:%S', this.x) + '<br/> ' + this.y + getChartConfig(currentSensorType).tooltip + deleteButton,
		width: 250
	});
}

function deletePoint(index, deviceid, timestamp, type) {
	console.log('deletePoint(' + index + ',' + deviceid + ',' + timestamp + ',' + type + ')');
	//console.log(chart);
	if (window.confirm('Confirmer la suppression ?')) {
		$.ajax({
			url: 'data_delete.php',
			method: 'DELETE',
			data: { id: deviceid, timestamp: timestamp, type: type },
			dataType: 'json'
		})
		.done(function(data, textStatus) {
			//console.log('Ajax done : ' + JSON.stringify(data));
			//console.log('Ajax done : ' + textStatus);
			//console.log('Ajax done : ' + JSON.stringify(jqXHR));
			if (textStatus == 'success') {
				if ( (typeof data.success !== 'undefined') && (data.success == true) ) {
					if (data.rowcount > 0) {
						console.log('Point has been deleted for series index : ' + index);
						var chartdata = chart.series[index].options.data;
						for (var i=0; i<chartdata.length; i++) {
							if (chartdata[i][0] == timestamp) {
								console.log('Found point index : ' + i);
								chartdata.splice(i, 1);
								chart.series[index].setData(chartdata);
								//chart.redraw();
								return true;
							}
						}
						return false;
					}
					else {
						console.log('Error : data.rowcount = 0');
						alert('Error : No data was deleted');
						return false
					}
				}
				else {
					console.log('Error #' + data.error.code + ' : ' + data.error.message);
					alert('Error #' + data.error.code + ' : ' + data.error.message);
					return false
				}
			}
			else {
				console.log('Error #' + data.error.code + ' : ' + data.error.message);
				alert('Error #' + data.error.code + ' : ' + data.error.message);
				return false
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log('Ajax fail : ' + JSON.stringify(jqXHR));
			console.log('Ajax fail : ' + textStatus);
			console.log('Ajax fail : ' + JSON.stringify(errorThrown));
			alert('Error !!!');
			return false;
		});
	}
	else
		return false
}

$(document).ready(function() {
	loadDataFromType(currentSensorType);

	// Configuration des boutons

	// Toggle grouping data
	/*$('#data-grouping-type').change(function() {
		currentTypeGrouping = this.value;
		loadDataFromType(currentSensorType);
	});*/

	// Toggle grouping data
	/*$('#data-grouping-interval').change(function() {
		currentGroupingData = this.value;
		loadDataFromType(currentSensorType);
	});*/

	// Toggle point markers
	$('#markers').click(function() {
		loadDataFromType(currentSensorType);
		currentMarkers = !currentMarkers; 
	});

	// Toggle data labels
	$('#data-labels').click(function() {
		loadDataFromType(currentSensorType);
		dataLabels = !dataLabels; 
	});

	// Toggle visibility of  series
	$('#hide-show').click(function() {
		//$('html,body').css('cursor', 'progress');
		for (i = 0; i < chart.series.length; i++) {
			var series = chart.series[i];
			if (series.visible)
				series.hide();
			else
				series.show();
		}
		//$('html,body').css('cursor', 'auto');
	});

	$('#update').change(function() {
		/*if (this.value == "power") {
			console.log('changetype: ' + this.value)
			currentSensorType = this.value;
			currentGraphType = 'column';
			originalGraphType = 'column';
			currentStackStatus = 'normal';
		}
		else {*/
			currentGraphType = 'line';
			originalGraphType = 'line';
			currentSensorType = this.value;
			currentStackStatus = '';
		//}
		loadDataFromType(currentSensorType);
	});

	// Set type
	$('#chartType').change(function() {
		if (this.value == "stacked column") {
			currentStackStatus = 'normal';
			currentGraphType = 'column';
			originalGraphType = 'column';
		}
		else {
			currentStackStatus = '';
			currentGraphType = this.value;
			originalGraphType = this.value;
		}
		loadDataFromType(currentSensorType);
	});

});
