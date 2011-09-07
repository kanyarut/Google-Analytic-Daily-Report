<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<title>Google Analytics Daily Report</title>
<style type="text/css">
body{
	font-family: sans-serif;
	font-size: .9em;
}
th,td{
	padding: 3px;
	border: 1px solid #CCC;
	border-collapse: collapse;
}
th{
	background: #EEE;
}
</style>
</head>
<body>
<div id="container">
<?
require_once('gaapi.class.php');
$ga = new gaApi('username@gmail.com','password');

$now = date("Y-m-d", strtotime('-1 day'));
$yesterday = date("Y-m-d", strtotime('-2 day'));
$lastmonth = date('Y-m-d', strtotime('-30 days'));
$monthbefore1 = date('Y-m-d', strtotime('-31 days'));
$monthbefore2 = date('Y-m-d', strtotime('-60 days'));

$accounts = $ga->listAccounts();

?>
<h1>Google Analytic Report (<?=date("l, m-d-Y", strtotime('-1 day'))?>)</h1>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th rowspan="2" width="20%">Website</th>
			<th colspan="2">Daily Stat</th>
			<th colspan="2">Monthly Stat</th>
			<th colspan="3">Daily Source</th>
		</tr>
		<tr>
			<th width="10%"><?=date("l", strtotime('-1 day'))?></th>
			<th width="10%"><?=date("l", strtotime('-2 day'))?></th>
			<th width="10%">Last 30 Days</th>
			<th width="10%">Last Month</th>
			<th width="10%">Search</th>
			<th width="10%">Direct</th>
			<th width="10%">Referrer</th>
		</tr>
	</thead>
	<tbody>
		<? foreach($accounts as $a){ 
				$ga->setSiteID($a['tableId']);
			
				$params = array (
					'metrics' 		=> 'ga:visits',
					'start-date' 	=> $now,
					'end-date' 		=> $now,
					'max-results' 	=> 1,
					'sort'			=> '-ga:visits');
	  			$res = $ga->genReport($params);
	  			$todayr = $res[0]['ga:visits'];
	  			
				$params = array (
					'metrics' 		=> 'ga:visits',
					'start-date' 	=> $yesterday,
					'end-date' 		=> $yesterday,
					'max-results' 	=> 1,
					'sort'			=> '-ga:visits');
	  			$res = $ga->genReport($params);
	  			$yesterdayr = $res[0]['ga:visits'];
	  			
	  			$params = array (
					'metrics' 		=> 'ga:visits',
					'start-date' 	=> $lastmonth,
					'end-date' 		=> $now,
					'max-results' 	=> 1,
					'sort'			=> '-ga:visits');
	  			$res = $ga->genReport($params);
	  			$lastmor = $res[0]['ga:visits'];
	
				$params = array (
					'metrics' 		=> 'ga:visits',
					'start-date' 	=> $monthbefore2,
					'end-date' 		=> $monthbefore1,
					'max-results' 	=> 1,
					'sort'			=> '-ga:visits');
	  			$res = $ga->genReport($params);
	  			$mobeforer = $res[0]['ga:visits'];
     		?>	
			<tr>
				<th align="left"><?=$a['title']?></th>
				<td align="right" style="font-weight: bold;<? if($todayr>$yesterdayr)echo 'color:green;'; ?>"><?= number_format($todayr); ?></td>
				<td align="right"><?= number_format($yesterdayr); ?></td>
				<td align="right" style="font-weight: bold;<? if($lastmor>$mobeforer)echo 'color:green;'; ?>"><?= number_format($lastmor); ?></td>
				<td align="right"><?= number_format($mobeforer); ?></td>
				<td align="right" style="font-weight: bold;">
				<?
					$params = array (
						'metrics' 		=> 'ga:visits',
						'start-date' 	=> $now,
						'end-date' 		=> $now,
						'segment'		=> 'gaid::-6',
						'max-results' 	=> 1,
						'sort'			=> '-ga:visits');
        			$res = $ga->genReport($params);
        			echo number_format($res[0]['ga:visits']);
        		?>
				</td>
				<td align="right" style="font-weight: bold;">
				<?
					$params = array (
						'metrics' 		=> 'ga:visits',
						'start-date' 	=> $now,
						'end-date' 		=> $now,
						'segment'		=> 'gaid::-7',
						'max-results' 	=> 1,
						'sort'			=> '-ga:visits');
        			$res = $ga->genReport($params);
        			echo number_format($res[0]['ga:visits']);
        		?>
        		</td>
				<td align="right" style="font-weight: bold;">
				<?
					$params = array (
						'metrics' 		=> 'ga:visits',
						'start-date' 	=> $now,
						'end-date' 		=> $now,
						'segment'		=> 'gaid::-8',
						'max-results' 	=> 1,
						'sort'			=> '-ga:visits');
        			$res = $ga->genReport($params);
        			echo number_format($res[0]['ga:visits']);
        		?>
				</td>
			</tr>
		<?  } ?>
	</tbody>
</table>
</div><!--end container-->
</body>
</html>