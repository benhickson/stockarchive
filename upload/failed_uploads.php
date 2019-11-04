<?php

require '/var/www/html/creative.lonelyleap.com/archive/includes/0-base.php';

if ($_SESSION['logged_in']){
	if (isset($_GET['fail'])){
		// check if all necessary fields set
		if (isset($_POST['uploadfilename'])){
			$data = array(
				'uploadfilename' => $_POST['uploadfilename'],
				'user' => $_SESSION['userid']
			);
			$db->insert('failed_uploads',$data);
		}
	} else if (isset($_GET['check'])) {
		// check if all necessary fields set
		if (isset($_POST['failid'])){
			$db->rawQuery('UPDATE failed_uploads SET checked=1 WHERE id=? AND user=?',array($_POST['failid']),$_SESSION['userid']);
		}
	}
}