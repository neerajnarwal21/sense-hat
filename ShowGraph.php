<!DOCTYPE HTML>
<html>
<head>  
<meta charset="UTF-8">
<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script>
window.onload = function () {

	var tempData = [];
	var humData = []; // New array for humidity data
	
	var chart = new CanvasJS.Chart("chartContainer", {
		title: {
			text: "Data Chart"
		},
		axisX: {
			valueFormatString: "DD-MM-YY HH:mm:ss"
		},
		axisY: [
			{
				title: "Temperature",
				prefix: "",
				suffix: " C",
				tickLength: 5,
				tickColor: "DarkSlateBlue",
				tickThickness: 1
			},
			{
				title: "Humidity",
				prefix: "",
				suffix: " %",
				tickLength: 5,
				tickColor: "DarkGreen",
				tickThickness: 1
			}
		],
		toolTip: {
			shared: true
		},
		legend: {
			cursor: "pointer",
			verticalAlign: "top",
			horizontalAlign: "center",
			dockInsidePlotArea: true,
			itemclick: toogleDataSeries
		},

		data: [
			{
			
				type:"line",
				axisYIndex: 0,
				name: "Temperature",
				showInLegend: true,
				markerSize: 0,
				yValueFormatString: "#.##",
				dataPoints: tempData
			},
			{
				type:"line",
				axisYIndex: 1, // Assign to humidity axis
				name: "Humidity",
				showInLegend: true,
				markerSize: 0,
				yValueFormatString: "#.##",
				dataPoints: humData
			}
		]
	});

	chart.render();

	function toogleDataSeries(e){

		if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
			e.dataSeries.visible = false;
		} else{
			e.dataSeries.visible = true;
		}

		chart.render();
	}


	function addData(data) {	
		
			tempData = [];
			humData = []; // Clear existing data before adding new data
//			var tl = tempData.length;

			for (var i = 0; i < data.record.length; i++) {

				currentValues = data.record[i];				
//				console.log(currentValues.date.toString());
				[dateValues, timeValues] = currentValues.date.toString().split(' ');
				[month, day, year] = dateValues.split('-');
				[hours, minutes, seconds] = timeValues.split(':');
				date_in = new Date(+year, +month - 1, +day, +hours, +minutes, +seconds);

				tempData.push( {x: date_in, y: (currentValues.temperature * 1.0)});
				humData.push( {x: date_in, y: (currentValues.humidity * 1.0)}); // Push humidity data
			}

//			for(var ix = 0; ix < tl; ix++) {
//				tempData.shift();
//			}
			
			chart.options.data[0].dataPoints = tempData;
			chart.options.data[1].dataPoints = humData;
			
			chart.render();			
			console.log(tempData);		
			setTimeout(updateData, 5000);
	}

	function updateData() {
		$.getJSON("http://iotserver.com/convertXMLtoJSON.php", addData);				
	}
	
	setTimeout(updateData, 1000);
}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; max-width: 1520px; margin: 0px auto;"></div>
<script src="../../canvasjs.min.js"></script>
</body>
</html>