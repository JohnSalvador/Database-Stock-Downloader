<?php
require_once '../php/connect.php';

function loopAnalyze() {
	global $ip_address;
	global $con;
	
	$tickerfilehandle = fopen('../ticker.txt','r');
	while(!feof($tickerfilehandle)) {
		$ticker = trim(fgets($tickerfilehandle));
		
		$stockincrease = 0;
		$stockdecrease = 0;
		$stocknochange = 0;
		$totaldays = 0;
		$Eincrease = 0;
		$Edecrease = 0;
		
		if($ip_address=='::1'){
			$ticker = strtolower($ticker);
		}
		
		$query1 = "SELECT Date, PercentChange FROM $ticker WHERE PercentChange < 0 ORDER BY Date ASC;";
		if($result1 = $con->query($query1)) {
			while($row1 = $result1->fetch_assoc()) {
				$date = $row1['Date'];
				
				$percentchange = $row1['PercentChange'];
				//echo '<br />';
				//wtf this will be slow
				$query2 = "SELECT Date, PercentChange FROM $ticker WHERE Date > '$date' ORDER BY Date ASC LIMIT 1;";
				
				//can use SELECT COUNT(*)
				if($result2 = $con->query($query2)) {
					$rowcount = $result2->num_rows;
					//echo "ROW COUNT IS $rowcount <br />";
					if($rowcount==1) {
						$row2 = $result2->fetch_assoc();
						print_r($row2);
						$nextdate = $row2['Date'];
						$nextpercentchange = $row2['PercentChange'];
						
						if($nextpercentchange>0) {
							$stockincrease++;
							$Eincrease += $nextpercentchange;
							echo $totaldays++;
						} elseif($nextpercentchange < 0) {
							$stockdecrease++;
							$Edecrease += $nextpercentchange;
							$totaldays++;
						} else {
							$stocknochange++;
							echo $totaldays++;
						} 
					} elseif($rowcount==0) {
						$err = true;
						$errmessage .= 'No data for next day.\n';
					} else {
						$err = true;
						$errmessage .= 'Error Fetching next day data.\n';
					}
				} 
				
			} 
		} else {
			$err = true;
			$errmessage .= 'Error Fetching Ticker Fields.\n';
		}
		
		$nextincreasepercent = ($stockincrease/$totaldays);
		$nextdecreasepercent = ($stockdecrease/$totaldays);
		$avgincreasepercent = $Eincrease/$stockincrease;
		$avgdecreasepercent = $Edecrease/$stockdecrease;
		
		insertAnalysisDatabase($ticker, $stockincrease, $nextincreasepercent, $avgincreasepercent, $stockdecrease, $nextdecreasepercent, $avgdecreasepercent);
	}
}

function insertAnalysisDatabase($companyTicker, $nextDayIncrease, $nextDayIncreasePercent, $averageIncreasePercentage, $nextDayDecrease, $nextDayDecreasePercent, $averageDecreasePercentage){
	global $ip_address;
	global $con;
	
	$BuyValue = $nextDayIncreasePercent * $averageIncreasePercentage;
	$SellValue = $nextDayDecreasePercent * $averageDecreasePercentage;
	
	$query = "SELECT * FROM AnalysisA WHERE ticker='$companyTicker' ";
	$result = $con->query($query);
	echo $numberOfRows = $result->num_rows;
	
	if($ip_address=='::1'){
		$table = "analysisa";
	}else{
		$table = "AnalysisA";
	}
	
	if($numberOfRows==1){
		$updatequery = "UPDATE $table SET Ticker='$companyTicker',DayIncrease='$nextDayIncrease',PctDayIncrease='$nextDayIncreasePercent',AvgIncreasePct='$averageIncreasePercentage',DayDecrease='$nextDayDecrease',PctDayDec='$nextDayDecreasePercent',AvgDecreasePct='$averageDecreasePercentage',BuyValue='$BuyValue',SellValue='$SellValue' WHERE ticker='$companyTicker' ";
		$con->query($updatequery);
	}else{
		$insertquery="INSERT INTO $table (Ticker,DayIncrease,PctDayIncrease,AvgIncreasePct,DayDecrease,PctDayDecrease,AvgDecreasePct,BuyValue,SellValue) VALUES ('$companyTicker', '$nextDayIncrease', '$nextDayIncreasePercent', '$averageIncreasePercentage', '$nextDayDecrease', '$nextDayDecreasePercent', '$averageDecreasePercentage', '$BuyValue', '$SellValue')";
		$con->query($insertquery);
	}
}

loopAnalyze();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
</body>
</html>