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

$callBackUrl = 'https://github.com/sakurawar/chatfuel/blob/master/appointment.php';

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
