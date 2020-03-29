<?php

require __DIR__.'/../includes/0-base.php';

// @@TODO: kick off those that aren't able_unpublish

if($_SESSION['logged_in']) {
  // add the userid from session
  $userid = $_SESSION['userid'];

  // check if all necessary fields set
  if(isset($_POST['user_id']) && isset($_POST['get_user_data'])) {
    $id = $_POST['user_id'];

    $cols = array(
      " email
      , firstname
      , lastname
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

  } 
  else if(isset($_POST['get_columns'])) {
    $sql = 'SHOW COLUMNS FROM users';
    $res = $db->query($sql);

    exit(json_encode(array($res)));
  } 
  else if(isset($_POST['user_id'])
  && isset($_POST['value'])
  && isset($_POST['field'])
  ) {
    $id = $_POST['user_id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    if($field === 'password') {
      $value = password_hash($value, PASSWORD_DEFAULT);
    }

    $data = array($field => $value);

    $db->where('id', $id);
    $res = $db->update('users', $data);

    // response should not send back the hashed $value when password is being set
    if($res) {
      echo json_encode(array(
        'tagsuccess' => true
        , 'message' => 'User '.$field.' editted to be ``'.$_POST['value'].'``'
      ));
    }
    else {
      echo json_encode(array(
        'tagsuccess' => false
        , 'message' => 'Failed to edit user '.$field.' to be ``'.$_POST['value'].'``.'
      ));
    }
  } 
  else {
    // postfields not set correctly
    exit(json_encode(array(
      'tagsuccess' => false
      , 'message' => 'Incorrect postfields set.'
    )));
  }  
} 
else {
  // not logged in
  exit(json_encode(array(
    'tagsuccess' => false
    , 'message' => 'Not logged in.'
  )));
}
