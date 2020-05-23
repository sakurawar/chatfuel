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

$file  = $cf->getRequest('file');
$type  = $cf->getRequest('fileType','file');
$help  = $cf->getRequest('help',0);

if(!empty($file)){

	switch ($type) {
		case "file":
			$cf->addFile($file);
			break;
		case "audio":
			$cf->addAudio($file);
			break;
		case "video":
			$cf->addVideo($file);
			break;
		default:
			$cf->addFile($file);
			break;
	}

} else {

	if(!$help){
		$msg  = 'ERROR - Missing "file" parameter';
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
<title>Easy File Delivery script for ChatFuel</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Easy File Delivery script for ChatFuel</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows ChatFuel bots to deliver a file (pdf, docx, etc), video or audio directly to users without redirecting to a URL.
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>Sample data (file):</strong>
				</p>
<code><pre>{
	"messages": [
		{
			"attachment": {
				"type": "file",
				"payload": {
					"url": "https://yoursite.com/some-lead-magnet.pdf"
				}
			}
		}
	]
}</pre></code>
				<p>
					<strong>Sample data (audio):</strong>
				</p>
<code><pre>{
	"messages": [
		{
			"attachment": {
				"type": "audio",
				"payload": {
					"url": "https://yoursite.com/podcast-episode.mp3"
				}
			}
		}
	]
}</pre></code>
				<p>
					<strong>Sample data (video):</strong>
				</p>
<code><pre>{
	"messages": [
		{
			"attachment": {
				"type": "video",
				"payload": {
					"url": "https://yoursite.com/sales-video.mp4"
				}
			}
		}
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/file.php</strong>
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
							<th scope="row">file</th>
							<td>
								Valid URL
							</td>
							<td>required</td>
							<td>&nbsp;</td>
							<td>
								The file you're wanting to delier to the user - file, audio, or video
							</td>
						</tr>
						<tr>
							<th scope="row">fileType</th>
							<td>
								file, audio, video
							</td>
							<td>optional</td>
							<td>file</td>
							<td>
								The file type you're wanting to delier to the user - file, audio, or video
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