<?php
// time until function - basic countdown for ChatFuel
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

// get the user input
// user's offset from the bot
$offset     = (!empty($_request['timezone']))? $_request['timezone'] : 0;

// the desired bot timezone
$botOffset  = (!empty($_request['botTimezone']))? $_request['botTimezone'] : 0;

// date must be a valid format: Y-m-d H:i:s, m/d/Y h:i:s A, etc.
$date       = (!empty($_request['futureDate']))? $_request['futureDate'] : (new DateTime())->format('Y-m-d H:i:s');

// got to block - optional
$goToBlock  = (!empty($_request['goToBlock']))? $_request['goToBlock'] : '';

// singular/plural time notation - optional
$years      = (!empty($_request['years']))? $_request['years'] : 'year|years';
$months     = (!empty($_request['months']))? $_request['months'] : 'month|months';
$days       = (!empty($_request['days']))? $_request['days'] : 'day|days';
$hours      = (!empty($_request['hours']))? $_request['hours'] : 'hour|hours';
$minutes    = (!empty($_request['minutes']))? $_request['minutes'] : 'minute|minutes';
$seconds    = (!empty($_request['seconds']))? $_request['seconds'] : 'second|seconds';
$showZeros  = (!empty($_request['showZeros']))? $_request['showZeros'] : 1;
$showYears  = (!empty($_request['showYears']))? $_request['showYears'] : 0;
$showMonths = (!empty($_request['showMonths']))? $_request['showMonths'] : 0;
$help       = (!empty($_request['help']))? $_request['help'] : 0;

$yearsUntil   = 0;
$monthsUntil  = 0;
$daysUntil    = 0;
$hoursUntil   = 0;
$minutesUntil = 0;
$secondsUntil = 0;
$totalSecondsLeft = 0;

$years      = explode('|',$years);
$months     = explode('|',$months);
$days       = explode('|',$days);
$hours      = explode('|',$hours);
$minutes    = explode('|',$minutes);
$seconds    = explode('|',$seconds);

$offset = str_replace('+', '', $offset);
$botOffset = str_replace('+', '', $botOffset);

function getTimeZone($offset){
	// calculate seconds from offset
	if(strpos($offset,'.') !== false){
		list($hours, $minutes) = explode('.', $offset);
		$minutes = ($minutes == '5')? 30 : 45; // 30 or 45 minutes
	} else if(strpos($offset,':') !== false){
		list($hours, $minutes) = explode(':', $offset);
	} else {
		$hours = $offset;
		$minutes = 0;
	}
	$seconds = $hours * 60 * 60 + $minutes * 60;
	// get timezone name from seconds
	$tz = timezone_name_from_abbr('', $seconds, 1);
	if($tz === false) $tz = timezone_name_from_abbr('', $seconds, 0);
	return $tz;
}

// if we can convert the date string to a date...
if(strtotime($date) !== false){

	// get timezones
	$botZone  = new DateTimeZone(getTimeZone($botOffset));
	$userZone = new DateTimeZone(getTimeZone($offset));

	// set date to bot's timezone
	$endDate = new DateTime($date, $botZone);

	// set timezone to user's timezone
	date_default_timezone_set(getTimeZone($offset));

	// get the date in user's timezone
	$now = new DateTime();

	// convert to the bot's timezone
	$now->setTimeZone($botZone);

	// if the end date is farther out than right now,
	// get the time diff
	if($endDate > $now){

		// get date diff
		$timeDiff = $now->diff($endDate);

		$yearsUntil   = $timeDiff->y;
		$monthsUntil  = $timeDiff->m;
		$daysUntil    = $timeDiff->d;
		$hoursUntil   = $timeDiff->h;
		$minutesUntil = $timeDiff->i;
		$secondsUntil = $timeDiff->s;

		$totalSecondsLeft  = $yearsUntil * 31557600;
		$totalSecondsLeft += $monthsUntil * 2628000;
		$totalSecondsLeft += $hoursUntil * 3600;
		$totalSecondsLeft += $minutesUntil * 60;
		$totalSecondsLeft += $secondsUntil;
	}

}

$timeUntil = '';
if($yearsUntil > 0 || ($showZeros && $showYears)){
	$timeUntil .= $yearsUntil . ' ';
	$timeUntil .= ($yearsUntil == 0 || $yearsUntil > 1)? $years[1] : $years[0];
	$timeUntil .= ' ';
}
if($monthsUntil > 0 || ($showZeros && $showMonths)){
	$timeUntil .= $monthsUntil . ' ';
	$timeUntil .= ($monthsUntil == 0 || $monthsUntil > 1)? $months[1] : $months[0];
	$timeUntil .= ' ';
}
if($daysUntil > 0 || $showZeros){
	$timeUntil .= $daysUntil . ' ';
	$timeUntil .= ($daysUntil == 0 || $daysUntil > 1)? $days[1] : $days[0];
	$timeUntil .= ' ';
}
if($hoursUntil > 0 || $showZeros){
	$timeUntil .= $hoursUntil . ' ';
	$timeUntil .= ($hoursUntil == 0 || $hoursUntil > 1)? $hours[1] : $hours[0];
	$timeUntil .= ' ';
}
if($minutesUntil > 0 || $showZeros){
	$timeUntil .= $minutesUntil . ' ';
	$timeUntil .= ($minutesUntil == 0 || $minutesUntil > 1)? $minutes[1] : $minutes[0];
	$timeUntil .= ' ';
}
if($secondsUntil > 0 || $showZeros){
	$timeUntil .= $secondsUntil . ' ';
	$timeUntil .= ($secondsUntil == 0 || $secondsUntil > 1)? $seconds[1] : $seconds[0];
	$timeUntil .= ' ';
}

$data = array(
	'set_attributes'   => array(
		'futureDate'   => $date,
		'timeUntil'    => $timeUntil,
		'yearsUntil'   => $yearsUntil,
		'monthsUntil'  => $monthsUntil,
		'daysUntil'    => $daysUntil,
		'hoursUntil'   => $hoursUntil,
		'minutesUntil' => $minutesUntil,
		'secondsUntil' => $secondsUntil,
		'totalSecLeft' => $totalSecondsLeft
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
<title>Time Until script for ChatFuel or ManyChat</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Time Until script for ChatFuel or ManyChat</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows bot builders to create a post via JSON API Plugin (ChatFuel) or post to a webhook (Zapier &amp; ManyChat) and retrieve the time until a future date, like a countdown.
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
		"futureDate": "2017/12/25 00:00:00",
		"timeUntil": "1 month 5 days 1 hour 56 minutes 31 seconds ",
		"yearsUntil": 0,
		"monthsUntil": 1,
		"daysUntil": 5,
		"hoursUntil": 1,
		"minutesUntil": 56,
		"secondsUntil": 31,
		"totalSecLeft": 2634991
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/time-until.php</strong>
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
							<th scope="row">timezone</th>
							<td>-12 - +14</td>
							<td>required</td>
							<td>0</td>
							<td>The user's timezone offset. In ChatFuel, this is the {{timezone}} variable</td>
						</tr>
						<tr>
							<th scope="row">botTimezone</th>
							<td>-12 - +14</td>
							<td>required</td>
							<td>0</td>
							<td>The timezone the bot is using for the countdown date.</td>
						</tr>
						<tr>
							<th scope="row">futureDate</th>
							<td>Date</td>
							<td>required</td>
							<td>Current server time</td>
							<td>Date must be a valid format: Y-m-d H:i:s, m/d/Y h:i:s A. Example: 11/01/2018 12:00:00</td>
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
							<th scope="row">showZeros</th>
							<td>0/1</td>
							<td>optional</td>
							<td>1</td>
							<td>When true, will show zero values as part of the response.</td>
						</tr>
						<tr>
							<th scope="row">showYears</th>
							<td>0/1</td>
							<td>optional</td>
							<td>0</td>
							<td>When true, will show years as part of the response, even if the value is 0.</td>
						</tr>
						<tr>
							<th scope="row">showMonths</th>
							<td>0/1</td>
							<td>optional</td>
							<td>0</td>
							<td>When true, will show months as part of the response, even if the value is 0.</td>
						</tr>
						<tr>
							<th scope="row">years</th>
							<td>pipe delimited text</td>
							<td>optional</td>
							<td>year|years</td>
							<td>singular &amp; plural values for the year value</td>
						</tr>
						<tr>
							<th scope="row">months</th>
							<td>pipe delimited text</td>
							<td>optional</td>
							<td>month|months</td>
							<td>singular &amp; plural values for the month value</td>
						</tr>
						<tr>
							<th scope="row">days</th>
							<td>pipe delimited text</td>
							<td>optional</td>
							<td>day|days</td>
							<td>singular &amp; plural values for the day value</td>
						</tr>
						<tr>
							<th scope="row">hours</th>
							<td>pipe delimited text</td>
							<td>optional</td>
							<td>hour|hours</td>
							<td>singular &amp; plural values for the hour value</td>
						</tr>
						<tr>
							<th scope="row">minutes</th>
							<td>pipe delimited text</td>
							<td>optional</td>
							<td>minute|minutes</td>
							<td>singular &amp; plural values for the minute value</td>
						</tr>
						<tr>
							<th scope="row">seconds</th>
							<td>pipe delimited text</td>
							<td>optional</td>
							<td>second|seconds</td>
							<td>singular &amp; plural values for the second value</td>
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