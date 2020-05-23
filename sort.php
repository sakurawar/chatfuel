<?php
// sort - parameter sort function for ChatFuel
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

$goToBlock = getRequest('goToBlock','');
$help      = getRequest('help',0);

$data = array();
$sortArray = array();

foreach($_request as $key => $value){
	if($key != 'help'){
		if($key != 'goToBlock'){
			$sortArray[$key] = $value;
		}
	}
}

if(!empty($sortArray)){

	$attributes = array();

	asort($sortArray);

	reset($sortArray);
	$key = key($sortArray);
	$attributes['minVariable'] = $key;
	$attributes['minValue'] = $sortArray[$key];

	end($sortArray);
	$key = key($sortArray);
	$attributes['maxVariable'] = $key;
	$attributes['maxValue'] = $sortArray[$key];

	$data['set_attributes'] = $attributes;

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
<title>Sort script for ChatFuel or ManyChat</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Sort script for ChatFuel or ManyChat</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows bot builders to post name/value pairs via JSON API Plugin (ChatFuel) or post to a webhook (Zapier &amp; ManyChat) and retrieve maximum and minimum namne/value pairs.
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
		"minVariable": "val2",
		"minValue": "cat",
		"maxVariable": "val1",
		"maxValue": "zebra"
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/sort.php</strong>
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
							<th scope="row">*</th>
							<td>any value</td>
							<td>required</td>
							<td></td>
							<td>
								You can send any number of key/value pairs with any value. Examples:<br>
								<em>
									?val1=zebra&val2=cat&val3=dog&val4=elephant<br>
									?quizOpt1=9&quizOpt2=16&quizOpt3=2<br>
								</em>
								The submitted values will be sorted and the minimum and maximum values will be returned along with their cooresponding key names.
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