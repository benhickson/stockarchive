<?php

// overrides's php's default maximum script execution time
set_time_limit(0);

require __DIR__.'/../includes/0-base.php';

ob_start();

if (isset($_GET['start'])){
	// just start the queue
	
	// 1. check to see if it's already running
	//



} else if (isset($_SESSION['logged_in'])) {

	if (isset($_GET['add'])) {
		// add a new item the queue, start the queue
		if (isset($_GET['clip'])) {
			// clip specified. add it to the queue.

			// check that the clip isn't already queued
			$db->where('(status = 0 or status = 1)')->where('clipid = ?',array($_GET['clip']))->get('ai_queue');
			if ($db->count == 0) {
				$data = array(
					'added_by_user' => $_SESSION['userid'],
					'clipid' 		=> $_GET['clip']
				);
				$db->insert('ai_queue', $data);
				echo json_encode(['success' => true, 'message' => 'Clip '.$_GET['clip'].' queued to be tagged by ai.']);
			} else {
				echo json_encode(['success' => false, 'message' => 'Clip '.$_GET['clip'].' already queued.']);
			}

		} else {
			// user didn't specify what clip to add
			echo json_encode(['success' => false, 'message' => 'Please specify a clip. Add "&clip=[yourclipid]" to the URL.']);
		}

	} else if (isset($_GET['status'])) {

		// query database for status = 1
		$currentqueueitem = $db->where('status = 1')->get('ai_queue', null, array('id', 'clipid'));
		if ($db->count == 1){
			$currentqueueitem = $currentqueueitem[0];
		} else if ($db->count == 0){
			$currentqueueitem = 0;
		} else {
			$currentqueueitem = -1;
		}

		// query databsse for count(id) status = 0
		$db->where('status = 0')->get('ai_queue', null, array('id'));
		// this is how many clips are in the queue afterward
		$remainingclips = $db->count;

		if ($currentqueueitem > 0){
			echo json_encode(['success' => true, 'running' => true,'message' => 'Clip '.$currentqueueitem['clipid'].' is being tagged. There are '.$remainingclips.' clips to go after that.']);
		} else if ($currentqueueitem == 0){
			echo json_encode(['success' => true, 'running' => false,'message' => 'The queue is not running. There are '.$remainingclips.' clips queued to be tagged.']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Queue Error. Multiple rows with status = 1']);
		}

	} else {
		echo json_encode(['success' => false, 'message' => 'Please specify an action. Add either "status" or "add" as parameters to the url']);
	}

} else {
	echo json_encode(['success' => false, 'message' => 'User not logged in.']);
}


// Get the size of the output.
$size = ob_get_length();
// Disable compression (in case content length is compressed).
header("Content-Encoding: none");
// Set the content length of the response.
header("Content-Length: {$size}");
// Close the connection.
header("Connection: close");
// Flush all output.
ob_end_flush();
ob_flush();
flush();
// Close current session (if it exists).
if (session_id()) session_write_close();


// when you set status of the completed clip from 1 to 2, make sure that update happens at the same time as when you set the next item from 0 to 1.
// this is to prevent race conditions where the 'status' request could happen and indicate that the queue was paused when it was actually just moving to the next item.
// might can just do it in a single query, might have to do some chaining or locking or something.

// if clarafai responds with a failure, record their response (or the critical subset of it) in the `failuremessage` field and set status to 3


