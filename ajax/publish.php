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
        $published = $status['published'] === 0;

        $db->where('id', $_POST['clipid']);
        $status = $db->get('clips', null, 'clips.editor');
        $editor = $status[0]['editor'] == $userid;

        echo json_encode(array('success' => true, 'published' => $published, 'editor' => $editor));
      } else {
        echo json_encode(array('success' => true, 'published' => 'status get failed: '.json_encode($status)));
      }
    }
    else if(isset($_POST['unpublish'])) {

      $db->where('id', $_POST['clipid']);

      // $hist = $db->get('clips', null, 'clips.edithistory');
      // $hist = json_decode($hist);

      /* if(count($array) > 10) {
        $_ = array_shift($array);
      } */

      $data = array(
        'published' => 0,
        'editor' => $userid // ,
        // 'edithistory' => $userid.''.$db->now()
      );

      $db->where('id', $_POST['clipid']);

      if($db->update('clips', $data)) {
        echo json_encode(array('success' => true, 'message' => 'Clip in Upload Queue (click to see page)'));
      } else {
        echo 'update failed: '.$db->getLastError();  
      }
    } else {
      exit(json_encode(array('success' => false, 'message' => 'Only unpublishing is implemented.')));
    }
  } else {
    // postfields not set correctly
    exit(json_encode(array('success' => false, 'message' => 'This page has been modified to edit the postfields. Please reload normally.')));
  }
} else {
  // not logged in
  exit(json_encode(array('success' => false, 'message' => 'Not logged in.')));
}
