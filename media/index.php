<?php 

require __DIR__.'../includes/0-base.php';


// check that variables are set
if (isset($_GET['clip']) && isset($_GET['q'])) {

	$clipid = $_GET['clip'];
	$quality = $_GET['q'];

	// check a valid quality level
	if ($quality == 't'){
		$field = 'linkthumb';
		$filenamequality = '';
	} else if ($quality == 'q'){
		$field = 'linkquarter';
		$filenamequality = '_quarterres';
	} else if ($quality == 'h'){
		$field = 'linkhalf';
		$filenamequality = '_halfres';
	} else if ($quality == 'f'){
		$field = 'linkfull';
		$filenamequality = '';
	} else {
		// invalid quality level. log it.
		exit('invalid quality level');
	}

	// get all the information we need to check the permission and serve the clip
	$query = '
		SELECT  c.id AS clipid, c.uploader, c.published, c.version, c.public,
				f.id AS fileid, f.folder,
				v.mountname,
				oft.extension, oft.mime

		FROM clips c

		LEFT JOIN files f ON c.'.$field.'=f.id
		LEFT JOIN volumes v ON f.volume=v.id
		LEFT JOIN opt_filetypes oft ON f.filetype=oft.id

		WHERE c.id=?
	';
	$clip = $db->rawQuery($query,array($clipid))[0];

	// check that user is logged in
	if (isset($_SESSION['logged_in'])){
		// continue
	} else {
		// check if public clip or if it's a thumbnail, otherwise do not serve
		if ($clip['public'] == 1 OR $field == 'linkthumb'){
			// continue
		} else {
			exit('not logged in and clip not public');
			// log the IP
		}
	}

	// check that the clip is published OR the user is the uploader
	if ($clip['published'] == 1 OR $clip['uploader'] == $_SESSION['userid']){

		// serve the file
		$file = $clip['mountname'].'/'.$clip['folder'].'/'.$clip['fileid'].'.'.$clip['extension'];

		// error_log($file);
		header('X-Sendfile: '.$file);

		// add version string
		if ($clip['version'] == 1){
			$version = '';
		} else {
			$version = '_v'.$clip['version'];
		}

		// build the output filename
		$filename = 'LLArchiveProxy_C'.$clip['clipid'].$version.$filenamequality.'.'.$clip['extension'];

		// set the output header depending on if download or not.
		if (isset($_GET['download'])){
			header('Content-type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
		} else {
			$contenttypeheader = $clip['mime'];
			header("Content-type: $contenttypeheader");
			header('Content-Disposition: inline; filename="'.$filename.'"');
		}

		// add a header so these files are cached
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 365 * 10))); // 10 years 
		// override the header that prevents these from being cached
		header ('Cache-Control: public');

	} else {
		// log it with the userid. the user is sneaking around.
	}
} else {
	// variables not set. log user with it.
}


// request this with ?sizes in the URL to get the list first, then add &save to save it to the database
// This updates the bytesizes in the database.
if (isset($_GET['sizes'])){
	echo '<pre>';

	$results = $db->rawQuery('
		SELECT f.id, f.volume, f.folder, f.filetype, v.mountname, oft.extension

		FROM files f

		LEFT JOIN volumes v ON f.volume=v.id
		LEFT JOIN opt_filetypes oft ON f.filetype=oft.id

		WHERE f.bytesize IS NULL

		ORDER BY f.uploaddate ASC
		;
	');
	// print_r($result);
	$i = 1;
	foreach ($results as $file) {
		$filepath = $file['mountname'].'/'.$file['folder'].'/'.$file['id'].'.'.$file['extension'];
		$filesize = filesize($filepath);
		echo "$i: $filepath: $filesize bytes\n";

		if (isset($_GET['save'])){
			$db->rawQuery('UPDATE files SET bytesize=? WHERE id=?',array($filesize,$file['id']));
		}

		$i++;
	}


}


// request this with ?trash in the URL to get the list first, then add &empty to save it to the database
// This removes files marked "todelete" from the disk and marks them "deleted" in the database.
if (isset($_GET['trash'])){
	echo '<pre>';

	$results = $db->rawQuery('
		SELECT f.id, f.volume, f.folder, f.filetype, v.mountname, oft.extension, f.bytesize

		FROM files f

		LEFT JOIN volumes v ON f.volume=v.id
		LEFT JOIN opt_filetypes oft ON f.filetype=oft.id

		WHERE f.todelete=1 AND f.deleted IS NULL

		ORDER BY f.uploaddate ASC
		;
	');

	// print_r($result);
	$totalsize = 0;
	$list = '';
	$i = 1;
	foreach ($results as $file) {
		$filepath = $file['mountname'].'/'.$file['folder'].'/'.$file['id'].'.'.$file['extension'];
		$filesize = $file['bytesize'];

		$totalsize = $totalsize + $filesize;
		$list .= "$i: $filepath: $filesize bytes\n";

		if (isset($_GET['empty'])){
			unlink($filepath);
			$db->rawQuery('UPDATE files SET deleted=1 WHERE id=?',array($file['id']));
		}

		$i++;
	}
	$totalsize = $totalsize / 1000000000;
	echo $totalsize." GB\n\n";
	echo $list;


}

// if (isset($_GET['filename'])){

// 	$share = "arc01";
// 	$filename = $_GET['filename'];
// 	$file = $share.'/'.$filename;
// 	header('X-Sendfile: '.$file);

// 	// get this from the database
// 	$contenttype = 1;

// 	if ($contenttype == 1) {
// 		$contenttypeheader = 'video/mp4';
// 	} else if ($contenttype == 2) {
// 		$contenttypeheader = 'image/jpeg';
// 	}

// 	if (isset($_GET['download'])){
// 		header('Content-type: application/octet-stream');
// 		header('Content-Disposition: attachment; filename="'.basename($file).'"');
// 	} else {
// 		header("Content-type: $contenttypeheader");
// 		header('Content-Disposition: inline; filename="'.basename($file).'"');
// 	}

// }


