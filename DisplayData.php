<?php 

	error_reporting(E_ALL);									//show any errors if there is any
	ini_set('display_errors', '1');
	
	$xml = '';										// intialize empty string


	if(file_exists('tempData.xml')) {					// if the xml file already exists then read it
		$xml = simplexml_load_file('tempData.xml');	// get all the current data

		// Report the total number of timestamps recorded
		$total_timestamps = count($xml->record);
		
		$last_record_index = $total_timestamps - 1;
		$last_record = $xml->record[$last_record_index];
		$current_temp_threshold = (float) $last_record->tempThresh;
		$current_humidity_threshold = (float) $last_record->humiThresh;


		echo "Total number of timestamps recorded: $total_timestamps<br>";
		echo "Current temperature threshold: $current_temp_threshold&deg;C<br>";
		echo "Current humidity threshold: $current_humidity_threshold%<br>";

		// Initialize counter for windy events
		$windy_count = 0;			
		// Count windy events
		foreach ($xml->record as $record) {
			if ((string) $record->wind === 'Windy') {
				$windy_count++; // Increment windy count
			}
		}

		// Display windy count
		echo "Total number of times it was windy: $windy_count<br><br>";

		// Display timestamps and relevant data in a table
		echo "<table border='1'>";
		echo "<tr><th>Date</th><th>Device Time</th><th>Temperature</th><th>Humidity</th><th>Temp Threshold</th><th>Humidity Threshold</th><th>Temp Above Threshold</th><th>Humidity Above Threshold</th><th>Wind State</th></tr>";

		foreach ($xml->record as $record) {
			$date = (string) $record->date;
			$device_date = (string) $record->deviceTime;
			$temperature = (float) $record->temperature;
			$humidity = (float) $record->humidity;
			$temp_threshold = (float) $record->tempThresh;
			$humidity_threshold = (float) $record->humiThresh;
			$wind_state = (string) $record->wind;

			// Determine if temperature and humidity values are above their respective thresholds
			$temp_above_threshold = ($temperature > $temp_threshold) ? 'Yes' : 'No';
			$humidity_above_threshold = ($humidity > $humidity_threshold) ? 'Yes' : 'No';

			echo "<tr><td>$date</td><td>$device_date</td><td>$temperature</td><td>$humidity</td><td>$temp_threshold</td><td>$humidity_threshold</td><td>$temp_above_threshold</td><td>$humidity_above_threshold</td><td>$wind_state</td></tr>";
		}

		echo "</table>";
	}
	else {
		echo "No file";
	}
?>
