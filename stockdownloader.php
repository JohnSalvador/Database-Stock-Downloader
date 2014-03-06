<?php
require_once 'php/connect.php';
//http://ichart.finance.yahoo.com/table.csv?s=GOOG&d=3&e=13&f=2013&g=d&a=7&b=19&c=2004&ignore=.csv
/*
d=month-1
e=3
f=current year
g=d leave it
a=beginning month you want to use -1
b=beginning day
c=beginning year
*/
function createURL($ticker) {
	$thismonth = date("n")-1;
	$thisday = date("j");
	$thisyear = date("Y");
	return "http://ichart.finance.yahoo.com/table.csv?s=$ticker&d=$thismonth&e=$thisday&f=$thisyear&g=d&a=7&b=19&c=2012&ignore=.csv";
	//change this for change of date
}

function getCSVFile($url, $outputfile) {
	$stringcontent = file_get_contents($url);
	$stringcontent = str_replace("Date,Open,High,Low,Close,Volume,Adj Close","", $stringcontent);
	$stringcontent = trim($stringcontent);
	//to directory
	file_put_contents($outputfile, $stringcontent);
}

function insertCSVDatabase($plaintextfile, $tablename) {
	global $con;
	global $ip_address;
	if($ip_address=='::1'){
		$tablename = strtolower($tablename);
	}
	
	$filehandle = fopen($plaintextfile, 'r');
	while(!feof($filehandle)) {
		$line = fgets($filehandle);
		$filedata = explode(',', $line);
		$date = $filedata[0];
		$open = $filedata[1];
		$high = $filedata[2];
		$low = $filedata[3];
		$close = $filedata[4];
		$volume = $filedata[5];
		$amountchange = $close-$open;
		$percentchange = ($amountchange/$open)*100; //should be stored as decimal(9,8)
		
		$checkquery = "SELECT * FROM $tablename";
		
		if(!$resultinsert = $con->query($checkquery)) {
			$createtablequery = "CREATE TABLE $tablename (Date DATE, PRIMARY KEY(date), Open DECIMAL(8,2), High DECIMAL(8,2), Low DECIMAL(8,2), Close DECIMAL(8,2), Volume INT, AmountChange DECIMAL(8,2), PercentChange FLOAT) ENGINE=InnoDB;";
			$con->query($createtablequery);
		}
		
		echo $insertquery = "INSERT INTO $tablename (Date, Open, High, Low, Close, Volume, AmountChange, PercentChange) VALUES('$date', '$open', '$high', '$low', '$close', '$volume', '$amountchange', '$percentchange');";
		echo '<br />';
		if(!($con->query($insertquery))) {
			echo 'Error inserting data into '.$tablename. ' ' . $con->error;
		}
	}
	fclose($filehandle);
}

function main() {
	$tickerfilehandle = fopen('ticker.txt','r');
	while(!feof($tickerfilehandle)) {
		$company = trim(fgets($tickerfilehandle));
		$url = createURL($company);
		
		//download text
		$stockfile = 'files/'.$company.'.txt';
		getCSVFile($url, $stockfile);
		insertCSVDatabase($stockfile, $company);
	}
}

main();
?>
Stock Data Uploaded!