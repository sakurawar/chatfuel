<?php
// time-track - basic time tracking function for ChatFuel
// by Pat Friedl
// https://braintrustinteractive.com/
// https://clkths.us/why-bots

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

switch($_SERVER['REQUEST_METHOD']){
	case 'GET':
		$_request = &$_GET;
	break;
	case 'POST':
		$_request = &$_POST;
	break;
	default:
		$_request = &$_GET;
}
if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SERVER['QUERY_STRING'])){
	parse_str($_SERVER['QUERY_STRING'], $qs);
	foreach($qs as $key => $value){
		$_request[$key] = $value;
	}
}

function getRequest($req = '',$fallback = null){
	global $_request;
	$val = null;
	if(!empty($_request[$req])){
		$val = trim($_request[$req]);
	}
	if(empty($val)){
		return $fallback;
	}
	return $val;
}

// get the user input
$start     = (!empty($_request['timeStart']))? intval($_request['timeStart']) : 0;
$goToBlock = (!empty($_request['goToBlock']))? $_request['goToBlock'] : '';
$help      = (!empty($_request['help']))? $_request['help'] : 0;

$end           = 0;
$diff          = 0;
$monthsDiff    = 0;
$daysDiff      = 0;
$hoursDiff     = 0;
$minutesDiff   = 0;
$secondsDiff   = 0;
$formattedTime = 0;

if(empty($start)){

	// new timestamp
	$start = time();

} else {

	// get the end time and calc time diff
	$end  = time();
	$diff = $end - $start;

	$startDate = new DateTime(date('m/d/Y H:i:s', $start));
	$endDate   = new DateTime(date('m/d/Y H:i:s', $end));

	$timeDiff    = $startDate->diff($endDate);

	$monthsDiff  = $timeDiff->m;
	$daysDiff    = $timeDiff->d;
	$hoursDiff   = $timeDiff->h;
	$minutesDiff = $timeDiff->i;
	$secondsDiff = $timeDiff->s;

}

// set up attributes
$data = array(
	'set_attributes' => array(
		'timeStart' => $start,
		'timeEnd' => $end,
		'timeDiff' => $diff,
		'timeDiffMonths' => $monthsDiff,
		'timeDiffDays' => $daysDiff,
		'timeDiffHours' => $hoursDiff,
		'timeDiffMinutes' => $minutesDiff,
		'timeDiffSeconds' => $secondsDiff
	)
);

if(!empty($goToBlock)){
	if(strpos($goToBlock,',') !== false){
		$goToBlock = explode (',',$goToBlock);
		foreach($goToBlock as $block){
			$data['redirect_to_blocks'][] = trim($block);
		}
	} else {
		$data['redirect_to_blocks'][] = $goToBlock;
	}
}

if(!$help){
	echo json_encode($data);
} else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Time Tracking script for ChatFuel or ManyChat</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Time Tracking script for ChatFuel or ManyChat</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows bot builders to track how long a user takes to complete any given route, path or task.
				</p>
				<p>
					The service accepts a number of parameters and returns JSON code formatted for use with ChatFuel's JSON API, but it can also be used in Zapier with ManyChat.
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>Sample data:</strong>
				</p>
<code><pre>{
	"set_attributes": {
		"timeStart": 1511894283,
		"timeEnd": 1511898338,
		"timeDiff": 4055,
		"timeDiffMonths": 0,
		"timeDiffDays": 0,
		"timeDiffHours": 1,
		"timeDiffMinutes": 7,
		"timeDiffSeconds": 35
	},
	"redirect_to_blocks": [
		"Welcome Message"
	]
}</pre></code>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>Posting to the service:</strong>
				</p>
				<p>
					Values may be sent to the service via POST or GET. The endpoint of the script is:<br>
					<strong>https://braintrustinteractive.com/chatfuel/scripts/time-track.php</strong>
				</p>

				<table class="table">
					<thead>
						<tr>
							<th scope="col">Field</th>
							<th scope="col">Value</th>
							<th scope="col">Required/Optional</th>
							<th scope="col">Default</th>
							<th scope="col">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th scope="row">timeStart</th>
							<td>timestamp</td>
							<td>optional</td>
							<td>current timestamp</td>
							<td>If not passed to the service, the script will return the current timestamp as the "timeStart" attribute. If the value is passed, the script will calculate the time that has elapsed between the timeStart value and a current timestamp.</td>
						</tr>
						<tr>
							<th scope="row">goToBlock</th>
							<td>Text</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>
								Name of a valid content block in ChatFuel. This can be a single block or comma delimited list of blocks.
							</td>
						</tr>
						<tr>
							<th scope="row">help</th>
							<td>0/1</td>
							<td>optional</td>
							<td>0</td>
							<td>When true, dispays this help file</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<h6>
					Need more bot integration or bot development? Check out:<br>
					<a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a>
				</h6>
			</div>
		</div>
	</div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php } ?>