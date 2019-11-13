<?php

// important for this long-running script
set_time_limit(0);

require __DIR__.'/../includes/0-base.php';





// responding to the AJAX
// Buffer all upcoming output...
ob_start();
// check if this is a re-transcode request
if(isset($_GET['retranscode'])){
	if (isset($_SESSION['logged_in'])) {
		// check that there's only one of this clipid
		$db->rawQuery('SELECT id FROM transcodequeue WHERE clip=?',array($_GET['retranscode']));
		if ($db->count == 1){
			// check that it's not already queued or transcoding
			$db->rawQuery('SELECT id FROM transcodequeue WHERE clip=? AND status=2',array($_GET['retranscode']));
			if ($db->count == 0) {
				echo 'clip #'.$_GET['retranscode'].' is already queued to be transcoded/re-transcoded. give it a few mins.';
			} else {
				// set the status in the transcode queue
				$db->rawQuery('UPDATE transcodequeue SET status=0 WHERE clip=?',array($_GET['retranscode']));
				// set the status in the clip
				$db->rawQuery('UPDATE clips SET transcodesready=0 WHERE id=?',array($_GET['retranscode']));
				// delete the old linked files, the 3 transcodes:
				// get the file ids to delete
                $linkstodelete = $db->rawQuery('SELECT linkhalf, linkquarter, linkthumb FROM clips WHERE id=?',array($_GET['retranscode']));
                // mark the three underlying files for deletion
                $db->rawQuery('UPDATE files SET todelete=1 WHERE id IN ('.$linkstodelete[0]['linkhalf'].','.$linkstodelete[0]['linkquarter'].','.$linkstodelete[0]['linkthumb'].');');
                // status echo			
				echo 'clip #'.$_GET['retranscode'].' is successfully queued to be re-transcoded.';
			}
		} else if ($db->count == 0){
			echo 'error: no items in the transcode queue with this clip id ('.$_GET['retranscode'].'). message ben.';
		} else if ($db->count > 1){
			echo 'error: multiple items in the transcode queue with this clip id ('.$_GET['retranscode'].'). message ben.';
		} else {
			echo 'unknown error. message ben.';
		}
	} else {
		echo 'user not loggen in.';
	}
} else {
	// Send a basic response.
	echo "Here be response - ".time();	
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




// Start your background work here.

// check the first time
$db->rawQuery('SELECT id FROM transcodequeue WHERE status=1');
// while there isn't anything transcoding
while ($db->count == 0){
	// get the earliest untranscoded clip id
	$data = $db->rawQuery('SELECT id, clip FROM transcodequeue WHERE status=0 ORDER BY timestamp_added DESC LIMIT 1');
	// if there's something to transcode
	if ($db->count == 0) {
		exit('all transcodes done, or nothing to transcode.'); // nothing more to transcode
	} else {
		// assign some variables
		$clipid = $data[0]['clip'];
		$transcodeid = $data[0]['id'];
		// start the timer
		$time_start = time();
		// mark the clip as transcoding
		$db->rawQuery('UPDATE transcodequeue SET status=1 WHERE id=?',array($transcodeid));

		// base path of the files, before the volume (mountname) links
		$basepath = '/var/www/html/creative.lonelyleap.com/archive/media/';

		$fileids = array();

		// find the file, get the details
		$data = $db->rawQuery('SELECT c.linkfull, v.mountname, f.folder, oft.extension FROM clips c 
								LEFT JOIN files f ON f.id=c.linkfull 
								LEFT JOIN volumes v ON v.id=f.volume
								LEFT JOIN opt_filetypes oft ON oft.id=f.filetype
								WHERE c.id=?',array($clipid));
		$fileids['full'] = $data[0]['linkfull'];
		$sourcemountname = $data[0]['mountname'];
		$sourcefolder = $data[0]['folder'];
		$sourceextension = $data[0]['extension'];
		$sourcedirectorypath = $basepath.$sourcemountname.'/'.$sourcefolder;
		$sourcefilepath = $sourcedirectorypath.'/'.$fileids['full'].'.'.$sourceextension;

		// destination volume for the transcodes
		$targetvolume = 1; // 1 is arc01
		// figure out a destination folder path
		$targetmountname = $db->rawQuery('SELECT mountname FROM volumes WHERE id=?',array($targetvolume))[0]['mountname'];
		$targetfolder = date('Y-md'); // today's date (UTC) - the `folder` name
		$targetdirectorypath = $basepath.$targetmountname.'/'.$targetfolder;

		// create the folder if it doesn't exit
		if (!file_exists($targetdirectorypath)) {
			mkdir($targetdirectorypath, 0777, true);
		} else {
			// echo "\n folder already exists."; 
		}

		$targetfiletype = 4; // 4 is h264, 6 is VP9 (safari doesnt do vp9)
		$targetextension = $db->rawQuery('SELECT extension FROM opt_filetypes WHERE id=?',array($targetfiletype))[0]['extension'];

		// assign some new file id's
		$data = array(
			'volume' => $targetvolume,
			'folder' => $targetfolder,
			'filetype' => $targetfiletype
		);
		$data['resolution'] = 2;
		$fileids['half'] = $db->insert('files', $data);
		$data['resolution'] = 3;
		$fileids['quarter'] = $db->insert('files', $data);

		// start the ffmpeg command
		// nice -19 is deprioritization
		// -l 50 is 50% CPU usage
		$processorprefix = 'nice -19 cpulimit -l 50 -- ';
		$command = $processorprefix . 'ffmpeg -hide_banner -i ' . $sourcefilepath;

		// set the output resolutions
		// TODO: refactor to pull this info from the database, maybe
		// TODO: I'm not using the width and height variables anymore, it's hard coded. Refactor and cleanup.
		$outputs = array(
			array('width' => '960', 'height' => '540', 'outputid' => $fileids['half'], 'scalepadandcrop' => 'scale=iw*sar*min(960/(iw*sar)\,540/ih):ih*min(960/(iw*sar)\,540/ih),pad=960:540:(ow-iw)/2:(oh-ih)/2:white'),
			array('width' => '480', 'height' => '270', 'outputid' => $fileids['quarter'], 'scalepadandcrop' => 'scale=-1:270,crop=480:270')
		);

		// open a variable to append to
		$outputstring = '';

		foreach ($outputs as $output) {
			$outputstring .= ' -vf "'.$output['scalepadandcrop'].'"';
			// $outputstring .= ' -s';
			// $outputstring .= ' '.$output['width'].'x'.$output['height'];
			$outputstring .= ' -threads 1'; // limit processor threads. important on multi-cpu servers.
			$outputstring .= ' '.$targetdirectorypath.'/'.$output['outputid'].'.'.$targetextension;
		}

		// append the output string
		$command .= $outputstring;

		// add this ending thing to quiet the output (or something, shell scripts are so confusing.)
		$command .= ' 2>&1';
		$fullpathin = '';

		// make the transcodes
		$error = shell_exec($command);
		// error_log($command."\n\n".$error);

		// make the thumbnail
		// get a thumbnail file ID
		$data = array(
			'volume' => $targetvolume,
			'folder' => $targetfolder,
			'filetype' => 2, // jpg
			'resolution' => 2 // 960x540
		);
		$fileids['thumbnail'] = $db->insert('files', $data);

		// dont need this i think
		// // get the thumbnail dimensions
		// $thumbsize = $db->rawQuery('SELECT width, height FROM opt_resolutions WHERE id=?',array($data['resolution']))[0];


		// make a path of the source video and output jpg
		// need the thumbnail file extension
		$thumbextension = $db->rawQuery('SELECT extension FROM opt_filetypes WHERE id=?',array($data['filetype']))[0]['extension'];
		
		// build the input and output paths
		$thumbnailsourcepath = $sourcedirectorypath.'/'.$fileids['full'].'.mp4';
		$thumbnailoutputpath = $targetdirectorypath.'/'.$fileids['thumbnail'].'.'.$thumbextension;
		
		// open a variable to save errors to
		$exec_error = '';

		// $duration =  exec("ffmpeg -i $thumbnailsourcepath 2>&1 | grep Duration", $exec_error);
		// error_log('line 186: '.print_r($exec_error));

		// learning to parse response
		// error_log('$duration:');
		// error_log(print_r($duration));
		// $actualDuration = substr($duration, 11, 12);
		// error_log('$actualDuration:');
		// error_log(print_r($actualDuration));
		// $hourminsec = explode(":", $actualDuration);
		// error_log('$hourminsec:');
		// error_log(print_r($hourminsec));
		// $seconds = $hourminsec[2] + $hourminsec[1]*60 + $hourminsec[0]*3600;
		// error_log('$seconds:');
		// error_log(print_r($seconds));
		// $halfduration = round($seconds/2);
		// error_log('$halfduration:');
		// error_log(print_r($halfduration));
		// echo 'midpoint of this video: '. $halfduration;

		// disabled because of midpoint issue
		// exec($processorprefix."ffmpeg -i $thumbnailsourcepath -y -ss 00:00:$halfduration -vframes 1 $thumbnailoutputpath 2>&1", $exec_error);
		// still failing
		// exec($processorprefix."ffmpeg -i $thumbnailsourcepath -y -ss 00:00:01 -vframes 1 -vf \"scale=-1:540,crop=960:540\" $thumbnailoutputpath 2>&1", $exec_error);
		// new "thumbnail" filter
		exec($processorprefix."ffmpeg -i $thumbnailsourcepath -vf \"thumbnail,scale=-1:540,crop=960:540\" -frames:v 1 $thumbnailoutputpath", $exec_error);
		// error_log('command: '.$processorprefix."ffmpeg -i $thumbnailsourcepath -vf \"thumbnail,scale=-1:540,crop=960:540\" -frames:v 1 $thumbnailoutputpath");
		// error_log('line 223: '.print_r($exec_error));


		// update the bytesizes and creation dates
		// put them in an array for looping
		$newfiles = array(
			array('id' => $fileids['half'],'extension' => 'mp4'),
			array('id' => $fileids['quarter'],'extension' => 'mp4'),
			array('id' => $fileids['thumbnail'],'extension' => 'jpg'),
		);
		// loop
		foreach ($newfiles as $file) {

			// Disabled, ffprobe isn't working for some reason.

			// get the bytesize
			// ffmpeg stuff
			// $bytesize = 0;
			// $command = $processorprefix.'ffprobe -v 0 -select_streams 0 -show_entries format=size -print_format json ';
			// add the target file path
			// $command .= $targetdirectorypath.'/'.$file['id'].'.'.$file['extension'];
			// $json = exec($command, $exec_error);
			// error_log(print_r($exec_error));
			// error_log(print_r($json));
			// $bytesize = json_decode($json, true)['format']['size'];
			// save to the clip
			$data = array(
				// 'bytesize' => $bytesize,
				'uploaddate' => $db->now()
			);
			$db->where('id', $file['id']);
			$db->update('files', $data);
		}

		// save all ids to the clip
		$data = array(
			'linkhalf' => $fileids['half'],
			'linkquarter' => $fileids['quarter'],
			'linkthumb' => $fileids['thumbnail'],
			'transcodesready' => 1
		);
		$db->where('id', $clipid);
		$db->update('clips', $data);

		$time_spent = time() - $time_start;
		// mark the clip as complete, and add the time spent
		$db->rawQuery('UPDATE transcodequeue SET status=2, rendertime_actual=? WHERE id=?',array($time_spent, $transcodeid));
		// check the db again, to update the $db->count, checking if anything's running. this informs the while() loop.
		$db->rawQuery('SELECT id FROM transcodequeue WHERE status=1');	
	}
}

// only if the while() doesnt happen, cause the while() contains an exit()
echo 'another process is transcoding.';

