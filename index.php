<?php
$to      = 'name@domain.com';
$subject = 'Google Analytic Report ('.date("l, m-d-Y", strtotime('-1 day')).')';

$host = 'http://'. $_SERVER['HTTP_HOST'] .''. $_SERVER['REQUEST_URI'];

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 500); 
curl_setopt($ch, CURLOPT_URL, $host.'report.php');
$result=curl_exec($ch); 	
curl_close($ch); 

$message = $result;
$headers = 'MIME-Version: 1.0' . "\r\n".
   'Content-type: text/html; charset=iso-8859-1' . "\r\n".
	'From: '. $to . "\r\n" .
   'Reply-To: '. $to . "\r\n" .
   'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>