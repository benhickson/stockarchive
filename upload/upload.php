<?php

require __DIR__.'/../includes/0-base.php';

if ($_SESSION['logged_in']){
	if (isset($_FILES['file'])){
		$fileName = $_FILES['file']['name']; // the filename
		$fileTmpLoc = $_FILES['file']['tmp_name']; // File in the PHP tmp folder
		$fileType = $_FILES['file']['type']; // The type of file it is
		$fileSize = $_FILES['file']['size']; // File size in bytes
		$fileErrorMsg = $_FILES['file']['error']; // 0 for false and 1 for true
		$duration = $_POST['duration']; // in seconds, from javascript (client side)
		echo 'duration '.$duration.', ';

		if (!$fileTmpLoc) { // if file is not chosen
			echo 'Error: Please browse for a file before clicking the upload button.';
			echo '<pre>';
			print_r($fileTmpLoc);
			echo '</pre>';
			exit();
		}
		
		// figure out a destination folder path
		$basepath = __DIR__.'/../media/';
		$volumeid = 1; // 1 is arc01
		$volume = $db->rawQuery('SELECT mountname FROM volumes WHERE id=?',array($volumeid))[0]['mountname'];
		$datestring = date('Y-md'); // today's date (UTC) - the FOLDER name
		$directorypath = $basepath.$volume.'/'.$datestring;

		// create the folder if it doesn't exit
		if (!file_exists($directorypath)) {
			mkdir($directorypath, 0777, true);
		} else {
			// echo "\n folder already exists."; 
		}

		// translate mime type into extension and id
		$data = $db->rawQuery('SELECT id, extension FROM opt_filetypes WHERE mime=?', array($fileType));
		$fileExtension = $data[0]['extension'];
		$fileTypeid = $data[0]['id'];

		// save the info to the database
		$data = array(
			'volume' => $volumeid,
			'folder' => $datestring,
			'filetype' => $fileTypeid,
			'bytesize' => $fileSize,
			'uploaddate' => $db->now()
		);
		if ($fileid = $db->insert('files',$data)) {
			// move the file to that folder
			if (move_uploaded_file($fileTmpLoc, $directorypath.'/'.$fileid.'.mp4')){
				// generate a clip 
				$data = array(
					'uploadfilename' => $fileName,
					'linkfull' => $fileid,
					'uploader' => $_SESSION['userid'],
					'editor' => $_SESSION['userid']
				);
				if ($clipid = $db->insert('clips',$data)) {
					// transcode speed, from manual testing / sql audits
					$transcode_speed = 0.3; // 0.3x real-time (duration)
					$rendertime_estimate = round($duration / $transcode_speed); 
					echo 'estimate '.$rendertime_estimate.', ';
					// add clip to queue
					$data = array(
						'clip' => $clipid,
						'rendertime_estimate' => $rendertime_estimate
					);
					if ($db->insert('transcodequeue',$data)) {
						// log it
						activitylog('upload', $_SESSION['nickname'].' uploaded a clip. Clip id='.$clipid);
						// respond to the ajax
						echo $fileName . ' upload is complete: clip ' . $clipid;
					} else {
						// fail. enable this row for debugging
						// echo 'Transcode Queue insert failed. Error: '. $db->getLastError();						
					}
				} else {
					// fail. enable this row for debugging
					// echo 'Clip insert failed. Error: '. $db->getLastError();
				}
			} else {
				echo 'move_uploaded_file function failed. Probably a permissions issue. Check php log.';
			}
		} else{
			// fail. enable this row for debugging
			// echo 'File insert failed. Error: '. $db->getLastError();
		}
	} else {
		// no $_FILES... dunno
	}
} else {
	echo 'Not logged in';
	activitylog('upload',$_SERVER['REMOTE_ADDR'].' attempted upload, not logged in.');
}
