<?php
// geo - location function for ChatFuel
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

$key       = getRequest('key','');
$address   = getRequest('address','');
$city      = getRequest('city','');
$state     = getRequest('state','');
$zip       = getRequest('zip','');
$goToBlock = getRequest('goToBlock','');
$help      = getRequest('help',0);

$googleGeo = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
$googleReverseGeo = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=';

$geoUrl = '';

$locStatus       = null;
$locLat          = null;
$locLon          = null;
$locType         = null;
$locStreetNum    = null;
$locAddress      = null;
$locNeighborhood = null;
$locCity         = null;
$locCounty       = null;
$locState        = null;
$locZip          = null;
$locZipLong      = null;
$locStateLong    = null;
$locCountry      = null;
$locCountryLong  = null;

if(!empty($key)){

	//if(!empty($latitude) && !empty($longitude)){
		// reverse lookup
	//	$geoUrl = $googleReverseGeo . $latitude . ',' . $longitude . '&key='. $key;
	//} else {

		$srch  = str_replace(' ','+',$address);
		$srch .= (!empty($srch) && !empty($city))? ',+' : '';
		$srch .= (!empty($city))? str_replace(' ','+',$city) : '';
		$srch .= (!empty($srch) && !empty($state))? ',+' : '';
		$srch .= (!empty($state))? str_replace(' ','+',$state) : '';
		$srch .= (!empty($srch) && !empty($zip))? '+' : '';
		$srch .= (!empty($zip))? str_replace(' ','+',$zip) : '';

		$geoUrl = (!empty($srch))? $googleGeo . $srch . '&key='. $key : '';

	//}

	if(!empty($geoUrl)){
		$location = json_decode(file_get_contents($geoUrl));

		$locStatus = $location->status;

		if($location->status == 'OK'){

			$locLat    = $location->results[0]->geometry->location->lat;
			$locLon    = $location->results[0]->geometry->location->lng;
			$locType   = strtolower($location->results[0]->geometry->location_type);

			foreach($location->results[0]->address_components as $component){
				if(in_array('street_number',$component->types)){
					$locStreetNum = $component->long_name;
				}
				if(in_array('route',$component->types)){
					$locAddress = $component->long_name;
				}
				if(in_array('neighborhood',$component->types)){
					$locNeighborhood = $component->long_name;
				}
				if(in_array('locality',$component->types) || in_array('postal_town',$component->types)){
					$locCity = $component->long_name;
				}
				if(in_array('administrative_area_level_2',$component->types)){
					$locCounty = $component->long_name;
				}
				if(in_array('administrative_area_level_1',$component->types)){
					$locState = $component->short_name;
					$locStateLong = $component->long_name;
				}
				if(in_array('postal_code',$component->types)){
					$locZip = $component->short_name;
					$locZipLong = $component->long_name;
				}
				if(in_array('country',$component->types)){
					$locCountry = $component->short_name;
					$locCountryLong = $component->long_name;
				}
			}

		}
	}


}

$data = array(
	'set_attributes' => array(
		'locStatus' => $locStatus,
		'locLat' => $locLat,
		'locLon' => $locLon,
		'locType' => $locType,
		'locStreetNum' => $locStreetNum,
		'locAddress' => $locAddress,
		'locNeighborhood' => $locNeighborhood,
		'locCity' => $locCity,
		'locCounty' => $locCounty,
		'locState' => $locState,
		'locStateLong' => $locStateLong,
		'locZip' => $locZip,
		'locZipLong' => $locZipLong,
		'locCountry' => $locCountry,
		'locCountryLong' => $locCountryLong
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
<title>Geo script for ChatFuel or ManyChat</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h2>Geo script for ChatFuel or ManyChat</h2>
				<h6>&copy; 2018 Pat Friedl, <a href="https://braintrustinteractive.com" target="_blank">Braintrust Interactive</a></h6>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p>
					<strong>What does this script do?</strong>
				</p>
				<p>
					This script allows bot builders to create a post via JSON API Plugin (ChatFuel) or post to a webhook (Zapier &amp; ManyChat) and retrieve geolocation data for a location given any combination of address, city, state or zip code.
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
		"locStatus": "OK",
		"locLat": 38.8829439,
		"locLon": -94.778624,
		"locType": "rooftop",
		"locStreetNum": "13505",
		"locAddress": "South Mur-Len Road",
		"locNeighborhood": "Santa Fe Square Shopping Center",
		"locCity": "Olathe",
		"locCounty": "Johnson County",
		"locState": "KS",
		"locStateLong": "Kansas",
		"locZip": "66062",
		"locZipLong": "66062",
		"locCountry": "US",
		"locCountryLong": "United States"
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
					<strong>https://braintrustinteractive.com/chatfuel/scripts/geo.php</strong>
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
							<th scope="row">key</th>
							<td>Valid API Key</td>
							<td>required</td>
							<td>&nbsp;</td>
							<td>Your Google Maps API key. A key can be gotten for free <a href="https://developers.google.com/maps/documentation/geocoding/start" target="_blank">here</a></td>
						</tr>
						<tr>
							<th scope="row">address</th>
							<td>Address</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>
								While optional, the more detail that can be provided, the more accurate the results.
							</td>
						</tr>
						<tr>
							<th scope="row">city</th>
							<td>City</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>
								While optional, the more detail that can be provided, the more accurate the results.
							</td>
						</tr>
						<tr>
							<th scope="row">state</th>
							<td>State/Province</td>
							<td>optional</td>
							<td>&nbsp;</td>
							<td>
								While optional, the more detail that can be provided, the more accurate the results.
							</td>
						</tr>
						<tr>
							<th scope="row">zip</th>
							<td>Zip Code</td>
							<td>optional/required</td>
							<td>&nbsp;</td>
							<td>
								While optional, the more detail that can be provided, the more accurate the results.
								<b> Note: if no other values are provided, zip code is required.</b>
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