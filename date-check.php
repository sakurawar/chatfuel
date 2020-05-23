<?php
// date in range - date range check function for ChatFuel
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

// get the go to block
$goToBlock = getRequest('goToBlock','');
$date = (!empty($_request['date']))? new DateTime($_request['date']) : new DateTime();
$minDate = (!empty($_request['minDate']))? new DateTime($_request['minDate']) : new DateTime('01/01/1900');
$maxDate = (!empty($_request['maxDate']))? new DateTime($_request['maxDate']) : new DateTime('01/01/3000');
$gtet = getRequest('gtet',0);

if($gtet){
	$data = array(
		'set_attributes' => array(
			'userDateInRange' => ($date >= $minDate && $date <= $maxDate)
		)
	);
} else {
	$data = array(
		'set_attributes' => array(
			'userDateInRange' => ($date > $minDate && $date < $maxDate)
		)
	);
}

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

echo json_encode($data);
die();
?>