<?php

require __DIR__.'/../includes/0-base.php';

function ableToPublish(){
	// bring in the variables needed
	// $userid must be set before calling!
	global $db, $clipid;

	$db->rawQuery('SELECT id FROM clips WHERE id=? AND project IS NOT NULL AND description IS NOT NULL AND description !=""',
				   array($clipid));

	if ($db->count == 1){
		return true;
	} else {
		return false;
	}
}

function isOwner(){
	// bring in the variables needed
	// $userid must be set before calling!
	global $db, $clipid, $userid;

	$db->rawQuery('SELECT id FROM clips WHERE id=? AND uploader=?',array($clipid, $userid));

	if ($db->count == 1){
		return true;
	} else {
		return false;
	}
} 

if ($_SESSION['logged_in']){
	// check if all necessary fields set
	if (isset($_POST['attemptPublish']) && isset($_POST['clipid'])) {
		$userid = $_SESSION['userid'];		
		$clipid = $_POST['clipid'];
		// verify that this is my clip
		if (isOwner()) {
			if (ableToPublish($clipid)){
				$db->rawQuery('UPDATE clips SET published=1, editor=NULL WHERE id=?', array($clipid));
				$published = true;
				$message = 'clip '.$clipid.' published!';
				echo json_encode(array($published,$message));
			} else {
				$published = false;
				$message = 'unable to publish, fields incomplete';
				exit(json_encode(array($published,$message)));
			}
		} else {
			$published = false;
			$message = 'not the clip owner';
			exit(json_encode(array($published,$message)));
		}
	} else if (isset($_POST['name']) && isset($_POST['value']) && isset($_POST['clipid'])){
		$field = $_POST['name'];
		$value = $_POST['value'];
		$clipid = $_POST['clipid'];
		$userid = $_SESSION['userid'];
		// verify that current user owns the clip
		if (isOwner()) {
			$datetest = false;
			$specsfield = false;
			// verify that the field is valid
			switch ($field) {
				case 'year':
				case 'month':
				case 'day':
					$datetest = true;				
				case 'framerate':
				case 'rawresolution':
				case 'country':
				case 'project':
				case 'camera':	
					$specsfield = true;				
					$column = $field;
					$datatype = 'integer';
					break;

				case 'region':
				case 'city':
					$specsfield = true;			
				case 'description':
					$column = $field;
					$datatype = 'string';
					break;					
				
				default:
					// fail
					exit('invalid field/column name');
					break;
			}

			// verify that the datatype can be accepted
			if ($datatype == 'string'){
				// turn just blank spaces into a nothing string
				if (strlen(trim($value)) == 0){
					$value = '';
				}
			} else if ($datatype == 'integer'){
				// make sure its an integer
				if ($value == (int) $value){
					// nothing, continue
					// if it's a date, make sure it's a correct date
					if ($datetest) {
						switch ($column) {
							case 'year':
								$min = 1800;
								$max = 2030;
								break;
							
							case 'month':
								$min = 1;
								$max = 12;
								break;
							
							case 'day':
								$min = 1;
								$max = 31;
								break;

							default:
								exit('somehow got past the datetest, code error');
								break;
						}
						// test it
						if (($min <= $value) && ($value <= $max)) {
							// valid, continue thusly.
						} else {
							if ($value == ''){
								$value = null;
							} else {
								if (isset($_SESSION['lastSpecs'])) {
									$thisquickoutput = $_SESSION['lastSpecs'];
								} else {
									$thisquickoutput = '';
								}
								exit(json_encode(array('invalid date',$thisquickoutput,false)));
							}
						}
					}
				} else {
					exit('value needs to be an integer. invalid.');
				}
			}

			$data = array(
				$column => $value,
			);

			// save for session stuff
			if ($specsfield) {
				$_SESSION['lastSpecs'][$column] = $value;	
			}			

			$db->where('id', $clipid);
			$db->update('clips',$data);

			$status = $db->count.' row(s) updated';

			if ($column == 'project'){
				$locationlookup = $db->rawQuery('SELECT rawlocation FROM projects WHERE id=?',array($value));
				if ($db->count == 1){
					$status = array(
						'status' => $status,
						'rawlocation' => $locationlookup[0]['rawlocation']
					);
				}
			}

			if (isset($_SESSION['lastSpecs'])) {
				$lastSpecs = $_SESSION['lastSpecs'];
			} else {
				$lastSpecs = '';
			}

			echo json_encode(array($status,$lastSpecs,ableToPublish()));

		} else {
			// trying to modify another user's clip
		}
	} else if (isset($_POST['matchSpecs'])) {
		$_SESSION['matchSpecs'] = true;
		if ($_POST['matchSpecs'] == 'false'){
			unset($_SESSION['matchSpecs']);
			echo 'unset';
		} else {
			echo 'set';
		}
	}
} else {
	echo 'Not logged in';
	activitylog('upload-details',$_SERVER['REMOTE_ADDR'].' attempted to modify a clip, not logged in.');
}