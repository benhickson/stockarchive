<?php

require __DIR__.'/../includes/0-base.php';

if($_SESSION['logged_in']) {
	// add the userid from session
	$userid = $_SESSION['userid'];

	// check if all necessary fields set
	if(isset($_POST['userId'])) {
		$id = $_POST['userId'];

		$cols = array(
			" id
			, email
			, firstname
			, lastname
			, interfaceprefs
      , registration_open
      , registration_code
      , picture
      , temp
      , old
      , able_unpublish
    ");
    $db->where('id', $id);
    $userData = $db->get("users", null, $cols);
    $userData['tagsuccess'] = true;

    exit(json_encode($userData[0]));

  } else if(isset($_POST['get_columns'])) {
    $sql = 'SHOW COLUMNS FROM users';
    $res = $db->query($sql);

    exit(json_encode(array(
			$res
    )));
	} else {
		// postfields not set correctly
		exit(json_encode(array(
			'tagsuccess' => false
			, 'message' => 'Incorrect postfields set.'
		)));
  }  
} else {
	// not logged in
	exit(json_encode(array(
		'tagsuccess' => false
		, 'message' => 'Not logged in.'
	)));
}
