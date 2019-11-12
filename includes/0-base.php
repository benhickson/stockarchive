<?php
// timeout settings, 172800 = 60 seconds * 60 minutes * 24 hours * 2 days
ini_set('session.gc_maxlifetime', 172800);
session_set_cookie_params(172800);
session_start();

require __DIR__.'/external/MysqliDb.php';
require __DIR__.'/settings.php';

$db = new MysqliDb('localhost','app-archive-ll', $dbpass, 'archive-ll');



function echoError($message){
	echo '<script type="text/javascript">alert("Error. ' . $message . '")</script>';
}
function echoSuccess($message){
	// echo '<script type="text/javascript">alert("Success! ' . $message . '")</script>';
}
function activitylog($page, $englishMessage){
	global $db;
	if (isset($_SESSION['logged_in'])){
		$user = $_SESSION['userid'];
	} else {
		$user = null;
	}
	$db->rawQuery('INSERT INTO activitylog (page, user, english) VALUES (?, ?, ?)', array($page, $user, $englishMessage));
}

function console_log($output, $with_script_tags = true) {
	$js_code = 'console.log('.json_encode($output, JSON_HEX_TAG).');';
	if ($with_script_tags) {
	    $js_code = '<script>' . $js_code . '</script>';
	}     

	echo $js_code;
}

function consoleEcho($content){
	console_log($content, true);
}
