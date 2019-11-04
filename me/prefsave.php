<?php
require '/var/www/html/creative.lonelyleap.com/archive/includes/0-base.php';

if (isset($_SESSION['logged_in'])) {
	if (isset($_POST['interfaceprefs'])) {
		$db->rawQuery('UPDATE users SET interfaceprefs=? WHERE id=?',array(json_encode($_POST['interfaceprefs']), $_SESSION['userid']));
		$_SESSION['interfaceprefs'] = $_POST['interfaceprefs'];
		activitylog('prefsave',$_SESSION['nickname'].' updated their interface preferences.');	
		echo json_encode($_POST['interfaceprefs']);
	} else {
		activitylog('prefsave',$_SESSION['nickname'].' requested this page manually, or at least without POSTing a variable. They might be exploring. Watch them.');
	}
} else {
	activitylog('prefsave','Someone at '.$_SERVER['REMOTE_ADDR'].' is trying to save preferences without being logged in.');
}