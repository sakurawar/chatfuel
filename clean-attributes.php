<?php
// clean attributes function for ChatFuel
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

$goToBlock = getRequest('goToBlock');
$help      = getRequest('help',0);

$data   = array();
$atts   = array();
$ignore = array(
	'help',
	'goToBlock'
);

foreach($_request as $key => $val){
	if(!in_array($key, $ignore)){
		$atts[$key] = getRequest($key);
		if(count($atts) == 40){
			break;
		}
	}
}

if(count($atts) > 0){
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
<title>Attribute Cleaner Script for ChatFuel</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Attribute Cleaner script for ChatFuel</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows bot builders to pass user attributes to the script and have them returned trimmed of extra spaces, and NULL (not set) if they are an empty string ("");
				</p>
				<p>
					<strong>Why?</strong>
				</p>
				<p>
					Because when you use a service like Dialogflow, non matching entities will be returned as an empty string (""), which is determined to <b>not</b> be "not set" in ChatFuel. This can cause issues when checking user attributes to see if they're set or not. The most common use case would be to call this script after sending back a custom payload from Dialogflow that sets user attributes.
				</p>
				<p>
					Also, users can enter in text and have leading or trailing spaces, which can end up in odd results too. This script takes care of that.
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
		"val1": null,
		"val2": "woot"
	},
	"redirect_to_blocks": [
		"Welcome message",
		"Default answer"
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/clean-attribute.php</strong>
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
							<th scope="row">{{attribute}}</th>
							<td>Any User Attribute</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>Any and all User Attributes may be passed, and each will be scrubbed and passed back to ChatFuel. Note: a maximum of 40 attributes may be passed</td>
						</tr>
						<tr>
							<th scope="row">goToBlock</th>
							<td>Text</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>
								Name of any valid content block(s) in ChatFuel. This can be a single block or comma delimited list of blocks.
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