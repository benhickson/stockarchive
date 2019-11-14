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

			echo json_encode(['success' => true, 'message' => 'Clip '.$_GET['clip'].' queued to be tagged by ai.']);
		} else {
			// user didn't specify what clip to add
			echo json_encode(['success' => false, 'message' => 'Please specify a clip. Add "&clip=[yourclipid]" to the URL.']);
		}

	} else if (isset($_GET['status'])) {


	} else {
		echo json_encode(['success' => false, 'message' => 'Please specify a clip. Add "&clip=[yourclipid]" to the URL.']);
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
if(session_id()) session_write_close();