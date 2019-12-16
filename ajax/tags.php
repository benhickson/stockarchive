<?php

// require '/var/www/html/creative.lonelyleap.com/archive/includes/0-base.php';
require __DIR__.'/../includes/0-base.php';

if ($_SESSION['logged_in']) {
	// add the userid from session
	$userid = $_SESSION['userid'];

	// check if all necessary fields set
	if (isset($_POST['action']) && isset($_POST['clipid']) && isset($_POST['tagtext'])) {
		// add the data from POST
		$action = $_POST['action'];
		$clipid = $_POST['clipid'];
		$tagtext = $_POST['tagtext'];

		$clipInfo = $db->rawQuery(
			'SELECT id, published, editor FROM clips WHERE id=?'
			, array($clipid)
		)[0];

		if ($db->count == 1) {
			// remove whitespace from beginning/end of tagtext
			$tagtext = trim($tagtext);

			// check if action is add or remove
			if ($action == 'add') {
				// add to recenttags variable
				if (!in_array($tagtext, $_SESSION['recenttags'])){
					array_push($_SESSION['recenttags'], $tagtext);
					if (count($_SESSION['recenttags']) > 20){ 
						array_shift($_SESSION['recenttags']);
					}
				}

				// check if tag exists, if not, create it
				$taglookup = $db->rawQuery(
					'SELECT id FROM tags WHERE tagname=? AND deleted!=1'
					, array($tagtext)
				);

				if ($db->count == 1) {
					$tagid = $taglookup[0]['id'];
				} else {
					$data = array(
						'tagname' => $tagtext,
						'addedby' => $userid
					);

					$tagid = $db->insert('tags', $data);
				}

				// tag the clip
				$data = array(
					'clipid' => $clipid,
					'tagid' => $tagid,
					'addedby' => $userid
				);
				$clips_x_tags_id = $db->insert('clips_x_tags', $data);

				// check that it completed and return info to the user
				if ($clips_x_tags_id) {
					echo json_encode(array(
						'tagsuccess' => true
						, 'message' => 'Tag "'.$tagtext.'" added to clip '.$clipid.'.'
					));
				} else {
					exit(json_encode(array(
						'tagsuccess' => false
						, 'message' => 'Tag "'.$tagtext.'" could not be added to clip '.$clipid.'.'
					)));
				}
			} elseif ($action == 'remove'
			&& !$clipInfo['published']
			&& $userid === $clipInfo['editor']) {
				// get the tag id, if not, error
				$taglookup = $db->rawQuery(
					'SELECT id FROM tags WHERE tagname=? AND deleted!=1'
					, array($tagtext)
				);

				if ($db->count == 1) {
					$tagid = $taglookup[0]['id'];

					// remove all links between the clip and tag
					$db->rawQuery(
						'DELETE FROM clips_x_tags WHERE clipid=? AND tagid=?'
						, array($clipid, $tagid)
					);

					// check that it completed and return info to the user
					if ($db->count > 0) {
						echo json_encode(array(
							'tagsuccess' => true
							, 'message' => 'Tag "'.$tagtext.'" removed from clip '.$clipid.' '.$db->count.' times.'
						));
					} else {
						exit(json_encode(array(
							'tagsuccess' => false
							, 'message' => 'Tag "'.$tagtext.'" could not be removed from '.$clipid.'. Possibly already removed by another user.'
						)));
					}
				} else {
					// trying to delete a tag that does not exist
					exit(json_encode(array(
						'tagsuccess' => false
						, 'message' => 'This tag does not exist, so it cannot be deleted.'
					)));
				}
			} else {
				// incorrect action set.
				exit(json_encode(array(
					'tagsuccess' => false
					, 'message' => 'This page has been modified to edit the postfields. Please reload normally.'
				)));
			}
		} else {
			// clip not taggable
			exit(json_encode(array(
				'tagsuccess' => false
				, 'message' => 'Current user is not allowed to tag this clip.'
			)));
		}
	} else {
		// postfields not set correctly
		exit(json_encode(array(
			'tagsuccess' => false
			, 'message' => 'This page has been modified to edit the postfields. Please reload normally.'
		)));
	}
} else {
	// not logged in
	exit(json_encode(array(
		'tagsuccess' => false
		, 'message' => 'Not logged in.'
	)));
}
