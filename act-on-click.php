<?php
/* *************************************************************** */
// Action on Click Script for ChatFuel
// Copyright 2018 Braintrust Interactive
// Pat Friedl pat@braintrustinteractive.com
/* *************************************************************** */

/* *************************************************************** */
// WHAT DOES IT DO?
/* *************************************************************** */
// This script allows you to link to ANY url and broadcast back to the bot
// when they click on the link. This lets you track clicks to anything
// and update user attributes, send content, etc only when they click!

/* *************************************************************** */
// USAGE
/* *************************************************************** */
// Set these attributes:
// {{go_to_block}} - the block the script trigger when it broadcasts back to ChatFuel
// {{target_link}} - the link where you want the user to end up when they click
// {{bot_id}}      - optional - the bot ID (from the bot URL in ChatFuel)
// {{bot_token}}   - optional - the broadcasting API token (configure tab in ChatFuel)
//
// then set this as your link in a button:
//
// https://your-site.com/path/to/act-on-click.php?go_to_block={{go_to_block}}&messenger_user_id={{messenger user id}}&target_link={{target_link}}
//
// OR pass bot ID and bot Token as well:
//
// https://your-site.com/path/to/act-on-click.php?go_to_block={{go_to_block}}&messenger_user_id={{messenger user id}}
// &bot_id={{bot_id}}&bot_token={{bot_token}}&target_link={{target_link}}
//
// passing bot ID and token will let you use this script with multiple bots
// the block being triggered from the broadcast should have all the logic - changing/updating attributes, etc.
//
// NOTE: If your domain is whitelisted in the bot, then this script will open the target link in
// a webview on desktop, so be sure your target link is https and also doesn't restrict content
// in iframes.

// cache control
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// general request grabber - either POST or GET
switch($_SERVER['REQUEST_METHOD']){
	case 'GET':
		$_req = &$_GET;
	break;
	case 'POST':
		$_req = &$_POST;
	break;
	default:
		$_req = &$_GET;
}
// querystring included with POST? grab the values and add to the $_req array
if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SERVER['QUERY_STRING'])){
	parse_str($_SERVER['QUERY_STRING'], $qs);
	foreach($qs as $key => $value){
		$_req[$key] = $value;
	}
}

/* *************************************************************** */
// BOT SETTINGS - set them here or send via querystring
/* *************************************************************** */
// these are just examples of a bot ID and token - be sure to use your own
$botId    = '597763bce4b06bc39cfcfc';
$botToken = 'h7CjAMU12VOxBNa2YQn1kl0F0oSfgkQnL1oBxbQw9AqWAsFbQtiJre1gWy7mp';

/* *************************************************************** */
// VARIABLES to pass from ChatFuel
/* *************************************************************** */
$botId       = (empty(getRequest('bot_id')))?    $botId    : getRequest('bot_id');
$botToken    = (empty(getRequest('bot_token')))? $botToken : getRequest('bot_token');
$msgrId      = getRequest('messenger_user_id');
$redirect    = getRequest('target_link');
$go_to_block = getRequest('go_to_block');

// function to get request fields, with fallback
function getRequest($req = '',$fallback = null){
	global $_req;
	$val = null;
	if(empty($_req[$req])){
		return $fallback;
	} else {
		$val = trim($_req[$req]);
		if(empty($val)){
			return $fallback;
		} else {
			return $val;
		}
	}
}

// broadcast back to ChatFuel so you can do all the things "on click"
if(!empty($botId) && !empty($botToken) && !empty($go_to_block) && !empty($msgrId)){

	$sendUrl  = 'https://api.chatfuel.com/bots/' . $botId;
	$sendUrl .= '/users/' . $msgrId . '/send?chatfuel_token=' . $botToken;
	$sendUrl .= '&chatfuel_block_name=' . urlencode($go_to_block);

	$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36';
	$ch = curl_init($sendUrl);
	curl_setopt($ch, CURLOPT_USERAGENT,$ua);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json'
	));
	$result = curl_exec($ch);

	if(curl_error($ch)){
		$status = curl_error($ch);
	} else {
		$status = $result;
	}
	curl_close($ch);
	if(empty($redirect)){
		echo $status;
	}

}
// redirect? You betcha!
if(!empty($redirect) && strpos($redirect,'http') !== false){
	header('Location: ' . $redirect);
}
?>