<?php

require __DIR__.'/../includes/0-base.php';

if (isset($_SESSION['logged_in'])){
	if (isset($_GET['clip'])){

		$status = $db->rawQuery('SELECT status FROM transcodequeue WHERE clip=?', array($_GET['clip']))[0]['status'];

		if ($status == 2){
			$timer = 0;
		} else if ($status == 1){
			$timer = $db->rawQuery('SELECT SUM(rendertime_estimate) AS remaining FROM transcodequeue WHERE status=1')[0]['remaining'];
		} else if ($status == 0){
			$timer = $db->rawQuery('SELECT SUM(rendertime_estimate) AS remaining FROM transcodequeue WHERE status=1')[0]['remaining'];
		}

		$output = array(
			'status' => $status,
			'timer' => $timer
		);

		echo json_encode($output);

	}
}