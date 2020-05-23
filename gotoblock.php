<?php
// gotoblock - basic redirect for ChatFuel
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

$help = (!empty($_request['help']))? $_request['help'] : 0;

$data = array();

if(empty($help)){
	foreach($_request as $key => $val){
		if(!empty($val)){
			$redirect = $val;
			break;
		}
	}
}

if(!empty($redirect)){
	if(strpos($redirect,',') !== false){
		$redirect = explode (',',$redirect);
		foreach($redirect as $redir){
			$data['redirect_to_blocks'][] = trim($redir);
		}
	} else {
		$data['redirect_to_blocks'][] = $redirect;
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
<title>Redirect script for ChatFuel or ManyChat</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Redirect script for ChatFuel</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					ChatFuel Go To Blocks don't allow you to use attributes to redirect to a block, so you can't redirect to {{last visited block name}} or other attribute.

					This script allows bot builders to create a post via JSON API Plugin and redirect to a block from any posted value instead of using a Go To Block.
				</p>
				<p>
					The service accepts a number of parameters and returns JSON code formatted for use with ChatFuel's JSON API.
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>Sample data:</strong>
				</p>
<code><pre>{
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/gotoblock.php</strong>
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
							<td>Any string</td>
							<td>required</td>
							<td>&nbsp;</td>
							<td>
								<p>
									You can pass any user attribute with any name from ChatFuel and it will be processed as a redirect to blocks. It's assumed that the value of the parameter you pass in is the name of a block in your bot. Note that only the first non-empty parameter will be processed.
								</p>
								<p>
									If the value of the attribute you pass contains commas, then it will be parsed as redirecting to blocks in a series (bot init,welcome message,default answer).
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