<?php
// Dynamic Ref Parser
// by Pat Friedl
// https://braintrustinteractive.com/
// https://clkths.us/why-bots

// ?ref=dynRef^email~pfriedl@gmail.com|phone~913243618
npm install chatfuel-api
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
	if(empty($_request[$req])){
		return $fallback;
	} else {
		$val = trim($_request[$req]);
		if(empty($val)){
			return $fallback;
		} else {
			return $val;
		}
	}
}

$data = array();
$atts = array();
$goToBlock  = getRequest('goToBlock');
$ref        = getRequest('ref');

if(strpos($ref, 'dynRef^') !== false){
	$ref = explode('^',$ref)[1];
	$ref = explode('|',$ref);

	foreach($ref as $param){
		$vals = explode('~',$param);
		$atts[$vals[0]] = $vals[1];
	}
	$data['set_attributes'] = $atts;
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
?>
