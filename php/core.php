<?php
require_once 'connect.php';

function adjust($escapestr) {
	global $con;
	return mysqli_real_escape_string($con, $escapestr);
}
function adjust2($queryfield){
	global $con;
	return mysqli_real_escape_string($con,htmlentities($escapestr));
}

function curPageURL() {
	// credit to Zuul
  $protocol = 'http';
  if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) {
    $protocol .= 's';
    $protocol_port = $_SERVER['SERVER_PORT'];
  } else {
    $protocol_port = 80;
  }

  $host = $_SERVER['HTTP_HOST'];
  $port = $_SERVER['SERVER_PORT'];
  $request = $_SERVER['PHP_SELF'];
  $query = isset($_SERVER['argv']) ? substr($_SERVER['argv'][0], strpos($_SERVER['argv'][0], ';') + 1) : '';

  $toret = $protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request . (empty($query) ? '' : '?' . $query);

  return $toret;
}

?>