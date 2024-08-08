<?php 
	error_reporting(E_ALL);						//show any errors if there is any
	ini_set('display_errors', '1');


	if(
	isset($_GET['t']) && 
	isset($_GET['tt']) && 
	isset($_GET['h']) && 
	isset($_GET['ht']) && 
	isset($_GET['dt']) && 
	isset($_GET['w'])
	) { // Check if values are present
		
		$str = '';								// intialize empty string


		if(file_exists('tempData.xml')) {				// if the xml file already exists then read it
			$str = file_get_contents('tempData.xml');	// get all the current data
		}
		
		if(strlen($str) == 0) {
			// intialize the variable to the empty xml if there is no old content
			$str = "<?xml version='1.0' encoding='UTF-8'?> \n<records></records>";
		}

		// create a new text for appending to the file
		$newData = "\n<record>
		<temperature>". $_GET['t']. "</temperature>
		<tempThresh>". $_GET['tt']. "</tempThresh>
		<humidity>". $_GET['h']. "</humidity>
		<humiThresh>". $_GET['ht']. "</humiThresh>
		<wind>". $_GET['w']. "</wind>
		<deviceTime>". $_GET['dt']. "</deviceTime>
		<date>". date('d-m-Y H:i:s') . "</date>
		</record>\n</records>"; 	
		$str = str_replace("</records>", $newData, $str);	// put the data in the end of the xml document
		
		file_put_contents('tempData.xml', $str);			// write the file back to the server

		echo '1';							// return success
	}
	else
		echo '0';							// return failure
?>
