<?php
// Appointment script for ChatFuel
// by Pat Friedl
// https://braintrustinteractive.com/
// https://clkths.us/why-bots

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// require config
require '../chatfuel.php';
require '../chatfuel-emoji-list.php';

// create the chatfuel client
$cf = new ChatFuelClient();

$offset     = $cf->getRequest('timezone',0);
$startTime  = $cf->getRequest('startTime',1000);
$endTime    = $cf->getRequest('endTime',1800);
$buffer     = $cf->getRequest('buffer',0);
$skip       = strtolower($cf->getRequest('skip'));
$goToBlock  = $cf->getRequest('goToBlock');
$pickedDate = $cf->getRequest('pickedDate');
$help       = $cf->getRequest('help',0);

$offset = str_replace('+', '', $offset);
$skipDays = (empty($skip))? array() : explode(',',$skip);
// keep dipshits from passing in too many days
while(count($skipDays) >= 7){
	array_shift($skipDays);
}

$callBackUrl = 'https://braintrustinteractive.com/chatfuel/scripts/appointment.php';

if(!empty($goToBlock)){

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
		$offset = $tz;

	} else {

		$tz = $offset;

	}

	// set timezone
	date_default_timezone_set($tz);

	// set date/time
	$hour = 0;
	$d = date('l m/d');

	// render the day picker
	if(empty($pickedDate)){

		// see if we're past 6PM, less a 2 hour buffer (4PM) - 1300 hours
		// if so, pick tomorrow as start date
		$hour = date('Hi');
		$startToday = true;

		// echo 'hour: ' . $hour . '<br>';
		// echo $endTime . '<br>';
		// echo ($endTime - ($buffer*100)) . '<br>';
		// echo ($endTime - ($buffer*100) - 100) . '<br>';
		// die();

		if($hour > ($endTime - ($buffer*100) - 100)){
			$d = date('l m/d', strtotime('+1 day', strtotime($d)));
			$startToday = false;
			$hour = 0;
		}

		$cf->addText('{{first name}}, just select a day you\'d prefer for your appointment ' . $cf->emoji('backhand index pointing down') . ' below:');
		$cf->createQuickReply();

		// render the quick replies for day picker
		$daySpread = 5;
		for($i = 0; $i < $daySpread; $i++){

			$date = date('l m/d', strtotime('+' . $i . ' day', strtotime($d)));
			$day  = strtolower(date('D', strtotime('+' . $i . ' day', strtotime($d))));

			if(!in_array($day,$skipDays)){

				// render quick replies for date
				$url  = $callBackUrl;
				$url .= '?timezone='   . $offset;
				$url .= '&startTime='  . $startTime;
				$url .= '&endTime='    . $endTime;
				$url .= '&buffer='     . $buffer;
				$url .= '&goToBlock='  . urlencode($goToBlock);
				$url .= '&pickedDate=' . urlencode($date);

				// if i = 0 (Today), i = 1 (Tomorrow)
				//echo $date . '<br>';
				if($i == 0 && $startToday){
					$cf->quickReply->addJSONBtn('Today',$url);
				} else if($i == 0 && !$startToday){
					$cf->quickReply->addJSONBtn('Tomorrow',$url);
				} else if($i == 1 && $startToday){
					$cf->quickReply->addJSONBtn('Tomorrow',$url);
				} else {
					$cf->quickReply->addJSONBtn($date,$url);
				}
			} else {

				++$daySpread;

			}
		}

		$cf->attachQuickReply();

	// render the time picker
	} else {

		// on hours leave 2 hour window
		$today = date('l m/d');
		if($today == date('l m/d', strtotime($pickedDate))){
			$hour = date('Hi');
		}

		$msg  = 'Great! Now just pick a time that best fits your schedule.';

		$cf->addText($msg);
		$cf->createQuickReply();

		// loop through from 10AM - 6PM (1000 - 1800)
		for($i = $startTime; $i < ($endTime+100); $i+=100){

			// are we within the 2 hour window?
			// 10:24 = 1024. 1200 - 200 = 1000 < 1024. Next slot!
			if(($i - ($buffer*100)) >= $hour) {

				$ampm = ($i < 1200)? ' AM' : ' PM';
				$timeslot  = ($i < 1300)? $i : $i - 1200;
				if(strlen($timeslot) == 3){
					$timeslot = substr($timeslot,0,1) . ':' . substr($timeslot,1);
				} else if(strlen($timeslot) == 4){
					$timeslot = substr($timeslot,0,2) . ':' . substr($timeslot,2);
				}
				$timeslot .= $ampm;

				$cf->createAttributes();
				$cf->addAttribute('appointmentTime',$pickedDate . ' ' . $timeslot);
				$cf->quickReply->addBlockBtn($timeslot,$goToBlock,$cf->attributes);

			}

		}

		$cf->attachQuickReply();

	}

} else {

	if(!$help){
		$msg  = 'ERROR - Missing one of these fields:' . "\n";
		$msg .= 'timezone, startTime, endTime, buffer, goToBlock' . "\n";
		$msg .= 'Instructions: https://braintrustinteractive.com/chatfuel/scripts/appointment.php?help=1';
		$cf->addText($msg);
	}

} // end field check
if(!$help){

	$cf->render();
	die();

} else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Easy Appointment Picker script for ChatFuel</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Easy Appointment Picker script for ChatFuel</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows ChatFuel bots to display a <b>very</b> simple appointment picker without leaving the bot. You can set the start and end times for availability, a time buffer before appointments, and days to skip.
				</p>
				<p>
					The script will output Quick Replies with the day and time options. The user will be able to pick a day - "Today", "Tomorrow" and 3 additional days for a total of 5 days. Once they pick a day, they'll have the option of picking a time between your start time and end time, less any slots that are already pasy or outside the buffer.
				</p>
				<p>
					Once they pick a time, the combined day an time will be returned as a user attribute {{appointmentTime}} in the format: Day mm/dd hh:mm am/pm, and then go to a defined block for handling by the bot. The bot can collect email, phone, notify the appointment receipient, etc - that's up to you.
				</p>
				<p>
					Whoever is receiving the appointment should then either take the appointment or contact the user to reschedule or confirm.
				</p>
				<p>
					<strong>
						Why doesn't this hook into a calendar app? Why doesn't this have more options? What if the time the user picks isn't available?
					</strong>
				</p>
				<p>
					<ol>
						<li>This script is free to use and requires no additional 3rd party platforms</li>
						<li>The whole idea is to get the user to make the commitment to book an appointment without leaving the bot</li>
						<li>If you have to reschedule, you'll have a great excuse to contact the user - that's what you wanted, right?!</li>
						<li>If you need a more complex solution, click the link below and contact us - we're for hire</li>
					</ol>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>Posting to the service:</strong>
				</p>
				<p>
					Values may be sent to the service via POST or GET. The endpoint of the script is:<br>
					<strong>https://braintrustinteractive.com/chatfuel/scripts/appointment.php</strong>
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
								or Timezone Name
							</td>
							<td>required</td>
							<td>0</td>
							<td>
								The user's (or bot's) timezone offset. In ChatFuel, the user timezone is the {{timezone}} variable. You may also pass a timezone name (like America/Chicago, Europe/Berlin, etc), as defined
								<a href="https://www.w3schools.com/php/php_ref_timezones.asp" target="_blank">here</a>.
							</td>
						</tr>
						<tr>
							<th scope="row">startTime</th>
							<td>Time in 24Hr format</td>
							<td>optional</td>
							<td>1000</td>
							<td>Define the earliest time when appointments can be booked using 24 hour format.</td>
						</tr>
						<tr>
							<th scope="row">endTime</th>
							<td>Time in 24Hr format</td>
							<td>optional</td>
							<td>1800</td>
							<td>Define the latest time when appointments can be booked using 24 hour format.</td>
						</tr>
						<tr>
							<th scope="row">buffer</th>
							<td># hours buffer for booking</td>
							<td>optional</td>
							<td>0</td>
							<td>This defines how closely to a timeslot a user can book. Example: with a 2 hour buffer, a person would not be able to book a 10AM appointment at 9AM, and the earliest time they could book would be 11AM.</td>
						</tr>
						<tr>
							<th scope="row">skip</th>
							<td>Days to black out</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>
								If you provide a comma delimited list of days that can not be booked, then those days will not be available for booking. The list <b>must</b> use the 3 letter abbreviation for days: (sun,mon,tue,wed,thu,fri,sat).
							</td>
						</tr>
						<tr>
							<th scope="row">goToBlock</th>
							<td>Block Name in ChatFuel</td>
							<td>required</td>
							<td>&nbsp;</td>
							<td>
								Once the user picks a day and time, they will be redirected to the defined block (or blocks) provided. This variable can be a single block name or a comma delimited list of block names.<br>
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

<?php
}
?>