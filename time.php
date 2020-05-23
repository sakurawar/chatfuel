<?php
// time - basic time/date function for ChatFuel
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
// either post or get: time.php?pffset={{timezone}}&format=l M jS, Y h:i A
// all fields are optional
$offset     = (!empty($_request['timezone']))? $_request['timezone'] : 0;
$format     = (!empty($_request['format']))? $_request['format'] : 'Y-m-d H:i:s e';

// locale isn't working for month/day names
$locale     = (!empty($_request['locale']))? $_request['locale'] : 'en_US';

// go to block after getting date/time
$goToBlock  = (!empty($_request['goToBlock']))? $_request['goToBlock'] : '';

// add time to the date? Allows you to get a future/past date from right now.
$addDays    = (!empty($_request['addDays']))? $_request['addDays'] : 0;
$addHours   = (!empty($_request['addHours']))? $_request['addHours'] : 0;
$addMinutes = (!empty($_request['addMinutes']))? $_request['addMinutes'] : 0;
$addSeconds = (!empty($_request['addSeconds']))? $_request['addSeconds'] : 0;

$help = (!empty($_request['help']))? $_request['help'] : 0;


/*
// time formats can be found here:
// formats: https://www.w3schools.com/php/func_date_date.asp
// UK
$format = 'Y-m-d'; // 2017-11-11
$format = 'Y/m/d'; // 2017/11/11
$format = 'j M, Y'; // 11 Nov, 2017

$format = 'Y-m-d h:i'; // 2017-11-11 06:14
$format = 'Y/m/d h:i'; // 2017/11/11 06:14
$format = 'j M, Y h:i'; // 11 Nov, 2017 06:14
$format = 'Y-m-d h:i A'; // 2017-11-11 06:14 PM
$format = 'Y/m/d h:i A'; // 2017/11/11 06:14 PM
$format = 'j M, Y h:i A'; // 11 Nov, 2017 06:14 PM
$format = 'Y-m-d H:i'; // 2017-11-11 18:14
$format = 'Y/m/d H:i'; // 2017/11/11 18:14
$format = 'j M, Y H:i'; // 11 Nov, 2017 18:14

// US
$format = 'm-d-Y'; // 11-11-2017
$format = 'm/d/Y'; // 11/11/2017
$format = 'D M jS, Y'; // Sat Nov 11th, 2017
$format = 'l M jS, Y'; // Saturday Nov 11th, 2017

$format = 'm-d-Y h:i'; // 11-11-2017 06:14
$format = 'm/d/Y h:i'; // 11/11/2017 06:14
$format = 'D M jS, Y h:i'; // Sat Nov 11th, 2017 06:14
$format = 'l M jS, Y h:i'; // Saturday Nov 11th, 2017 06:14
$format = 'm-d-Y h:i A'; // 11-11-2017 06:14 PM
$format = 'm/d/Y h:i A'; // 11/11/2017 06:14 PM
$format = 'D M jS, Y h:i A'; // Sat Nov 11th, 2017 06:14 PM
$format = 'l M jS, Y h:i A'; // Saturday Nov 11th, 2017 06:14 PM
$format = 'm-d-Y H:i'; // 11-11-2017 18:14
$format = 'm/d/Y H:i'; // 11/11/2017 18:14
$format = 'D M jS, Y H:i'; // Sat Nov 11th, 2017 18:14
$format = 'l M jS, Y H:i'; // Saturday Nov 11th, 2017 18:14

// just time
$format = 'H:i'; // 18:14
$format = 'H:i:s'; // 18:14:32
$format = 'h:i'; // 06:14
$format = 'h:i:s'; // 06:14:32
$format = 'h:i A'; // 06:14 PM
$format = 'h:i:s A'; // 06:14:32 PM
*/

// set the locale
setlocale(LC_ALL, $locale);

$offset = str_replace('+', '', $offset);

// if a numeric timezone is sent, parse it
if(is_numeric($offset)){

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

} else {

	$tz = $offset;

}

// set timezone
date_default_timezone_set($tz);

// get date
$d = date($format);

// get number of seconds to add
$addTime = ($addDays * 86400) + ($addHours * 3600) + ($addMinutes * 60) + $addSeconds;

// add time if need be
if($addTime != 0){
	$d =  date($format, strtotime($d. ' + ' . $addTime . ' seconds'));
}

$hr = intval(date('H', strtotime($d)));
if($hr < 12){
	$greeting = 'morning';
} else if($hr >= 12 && $hr < 17){
	$greeting = 'afternoon';
} else if($hr >= 17){
	$greeting = 'evening';
}

$data = array(
	'set_attributes' => array(
		'dateFormatted' => $d,

		'dayInt' => date('d', strtotime($d)),
		'dayShort' => date('D', strtotime($d)),
		'dayLong' => date('l', strtotime($d)),

		'monthInt' => date('m', strtotime($d)),
		'monthShort' => date('M', strtotime($d)),
		'monthLong' => date('F', strtotime($d)),

		'year' => date('Y', strtotime($d)),

		'time24h' => date('H', strtotime($d)),
		'time12h' => date('h', strtotime($d)),
		'timeMin' => date('i', strtotime($d)),
		'timeSec' => date('s', strtotime($d)),
		'timeAmPm' => date('A', strtotime($d)),

		'time12hms' => date('h:i:s A', strtotime($d)),
		'time24hms' => date('H:i:s', strtotime($d)),
		'time12hm' => date('h:i A', strtotime($d)),
		'time24hm' => date('H:i', strtotime($d)),
		'timezoneName' => $tz,

		'greeting' => $greeting,
		'greetingCap' => ucwords($greeting)
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
<title>Time script for ChatFuel or ManyChat</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>"Time" script for ChatFuel or ManyChat</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows bot builders to create a post via JSON API Plugin (ChatFuel) or post to a webhook (Zapier &amp; ManyChat) and retrieve the curent date/time with additional attributes broken out for year, month, day and times.
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
		"dateFormatted": "2018-01-01 10:26:53 America/Chicago",
		"dayInt": "01",
		"dayShort": "Mon",
		"dayLong": "Monday",
		"monthInt": "01",
		"monthShort": "Jan",
		"monthLong": "January",
		"year": "2018",
		"time24h": "10",
		"time12h": "10",
		"timeMin": "26",
		"timeSec": "53",
		"timeAmPm": "AM",
		"time12hms": "10:26:53 AM",
		"time24hms": "10:26:53",
		"time12hm": "10:26 AM",
		"time24hm": "10:26",
		"timezoneName": "America/Chicago",
		"greeting": "morning",
		"greetingCap": "Morning"
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/time.php</strong>
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
							<td>
								-12 - +14<br>
								or <a href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones" target="_blank">Timezone Name</a>
							</td>
							<td>required</td>
							<td>0</td>
							<td>
								The user's (or bot's) timezone offset. In ChatFuel, the user timezone is the {{timezone}} variable. You may also pass a timezone name (like America/Chicago, Europe/Berlin, etc), as defined
								<a href="https://www.w3schools.com/php/php_ref_timezones.asp" target="_blank">here</a>.
							</td>
						</tr>
						<tr>
							<th scope="row">format</th>
							<td>
								<a href="formats: https://www.w3schools.com/php/func_date_date.asp" target="_blank">
									Any valid format string
								</a>
							</td>
							<td>required</td>
							<td>Y-m-d H:i:s e</td>
							<td>
								The formatting you want to display for your date. Formatting options can be found
								<a href="formats: https://www.w3schools.com/php/func_date_date.asp" target="_blank">here</a>.
							</td>
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
							<th scope="row">addDays</th>
							<td>Any valid integer</td>
							<td>optional</td>
							<td>0</td>
							<td>Used to add/subract days to a date to obtain a future/past date</td>
						</tr>
						<tr>
							<th scope="row">addHours</th>
							<td>Any valid integer</td>
							<td>optional</td>
							<td>0</td>
							<td>Used to add/subract hours to a date to obtain a future/past date</td>
						</tr>
						<tr>
							<th scope="row">addMinutes</th>
							<td>Any valid integer</td>
							<td>optional</td>
							<td>0</td>
							<td>Used to add/subract minutes to a date to obtain a future/past date</td>
						</tr>
						<tr>
							<th scope="row">addSeconds</th>
							<td>Any valid integer</td>
							<td>optional</td>
							<td>0</td>
							<td>Used to add/subract seconds to a date to obtain a future/past date</td>
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