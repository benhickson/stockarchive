<?php

// require '/var/www/html/creative.lonelyleap.com/archive/includes/0-base.php';
require __DIR__.'/../includes/0-base.php';

if ($_SESSION['logged_in']){
  // add the userid from session
  $userid = $_SESSION['userid'];

  // check if all necessary fields set
  if (isset($_POST['clipid']) && $_POST['clipid'].length > 0) {

  	if(isset($_POST['status'])) {
	  $db->where('id', $_POST['clipid']);
	  $status = $db->get('clips', null, 'clips.published');

      if(count($status) === 1) {
        echo json_encode(array('tagsuccess' => true, 'published' => $status['published'] === 0));
      }
      else {
      	echo json_encode(array('tagsuccess' => true, 'published' => 'status get failed: '.json_encode($status)));
      }
  	}
    else if(isset($_POST['unpublish'])) {
      $data = array(
        'published' => 0,
        'editor' => $userid,
      );

      $db->where('id', $_POST['clipid']);

      if($db->update('clips', $data)) {
        echo json_encode(array('tagsuccess' => true, 'message' => 'Clip put in your edit queue'));
      }
      else {
        echo 'update failed: '.$db->getLastError();  
      }
    }
    else {
      exit(json_encode(array('tagsuccess' => false, 'message' => 'Only unpublishing is implemented.')));
    }
  } else {
    // postfields not set correctly
    exit(json_encode(array('tagsuccess' => false, 'message' => 'This page has been modified to edit the postfields. Please reload normally.')));
  }
} else {
  // not logged in
  exit(json_encode(array('tagsuccess' => false, 'message' => 'Not logged in.')));
}
