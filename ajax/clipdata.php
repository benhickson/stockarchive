<?php

require '/var/www/html/creative.lonelyleap.com/archive/includes/0-base.php';

if (isset($_SESSION['logged_in'])){

	// add the userid from session
	$userid = $_SESSION['userid'];

	// check that a clipid and a "fields" container is set
	if (isset($_POST['clipid']) && isset($_POST['fields'])) {

		// add those variables
		$clipid = $_POST['clipid'];
		$fields = $_POST['fields'];

		// check that the clipid is valid & published
		$db->rawQuery('SELECT id FROM clips WHERE id=? AND published=1 AND (todelete = 0 OR todelete IS NULL)',array($clipid));
		if ($db->count == 1){

			// set up the query building
			$tagquery = false;
			$cols = array('c.id');

			// parse the fields and check that they're all valid, and build request as requested
			$fields = json_decode($fields);

			foreach ($fields as $field) {
	
				switch ($field) {
					case 'description':
						$cols[] = 'c.description';
						break;					
					case 'project':
						$cols[] = "CONCAT_WS(' - ',IF(LENGTH(p.jobnumber),p.jobnumber,NULL),IF(LENGTH(p.name),p.name,NULL)) AS project";
						$cols[] = 'p.id AS project_id';
						$db->join('projects p','c.project=p.id','LEFT');
						# code...
						break;
					case 'camera':
						$cols[] = 'optcam.model AS camera';
						$cols[] = 'optcam.id AS camera_id';
						$db->join('opt_cameras optcam','c.camera=optcam.id','LEFT');
						# code...
						break;
					case 'resolution':
						$cols[] = "CONCAT_WS('x',optr.width,optr.height) AS resolution";
						$cols[] = 'optr.id AS resolution_id';
						$db->join('opt_resolutions optr','c.rawresolution=optr.id','LEFT');
						# code...
						break;											
					case 'date':
						$cols[] = 'c.year';
						$cols[] = 'c.month';
						$cols[] = 'c.day';
						break;
					case 'tags':
						$tagquery = true;
						break;
					case 'location':
						$cols[] = 'country.countryname AS country';
						$cols[] = 'country.id AS country_id';
						$db->join('opt_countries country','c.country=country.id','LEFT');
						$cols[] = 'c.region';
						$cols[] = 'c.city';
						break;
					case 'originalfilename':
						$cols[] = 'c.uploadfilename AS originalfilename';
						break;
					case 'rawfootageurl':
						$cols[] = 'prj.rawfootageurl';
						$db->join('projects prj','c.project=prj.id','LEFT');
						break;
					default:
						$success = false;
						$message = 'Invalid fields requested.';
						$data = null;
						exit(json_encode(compact('success','message','data')));
						# code...
						break;
				}

			}

			// run the query
			$db->where('c.id', $clipid);
			$result = $db->getOne('clips c', $cols);

			// set up the data output array
			$data = array();

			// add the result of the query with their column names
			foreach ($result as $key => $value) {
				$data[$key] = $value;
			}

			if ($tagquery) {
				$result = $db->rawQuery('SELECT t.id, t.tagname FROM clips_x_tags cxt LEFT JOIN tags t ON cxt.tagid=t.id WHERE cxt.clipid=?',array($clipid));
				$data['tags'] = json_encode($result);
			}

			$success = true;
			$message = 'All good!';
			// $data = $data; // redundant, isn't it?
			echo json_encode(compact('success','message','data'));			

		} else {
			$success = false;
			$message = 'File not available. Either not published, deleted, or never existed.';
			$data = null;
			echo json_encode(compact('success','message','data'));
		}
	} else {
		$success = false;
		$message = 'Fields not set correctly.';
		$data = null;
		echo json_encode(compact('success','message','data'));
	}
} else {
	$success = false;
	$message = 'You\'re not logged in. This may be due to inactivity. Click any link to log in again.';
	$data = 'triggerLogin';
	echo json_encode(compact('success','message','data'));
}