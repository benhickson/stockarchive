<?php

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

		// get info about the clip
		$clipInfo = $db->rawQuery(
			'SELECT id, published, editor FROM clips WHERE id=?'
			, array($clipid)
		)[0];

		// if the requested clip exists
		if ($db->count == 1) {

			// remove whitespace from beginning/end of tagtext
			$tagtext = trim($tagtext);

			// get the tag id if it already exists
			$taglookup = $db->rawQuery(
				'SELECT id FROM tags WHERE tagname=? AND deleted!=1'
				, array($tagtext)
			);

			$countOfTagsMatchingTagtext = $db->count;

			// check if action is add or remove
			if($action == 'add') {

				// check if the tag is in the recent tags
				$i = array_search($tagtext, $_SESSION['recenttags']);

				// if the tag is not in the array, add it, else, move the tag to the back
				if($i === false) {
					array_push($_SESSION['recenttags'], $tagtext);
					if(count($_SESSION['recenttags']) > 50) {
						array_shift($_SESSION['recenttags']);
					}
				}
				else {
					$tag = $_SESSION['recenttags'][$i];
					unset($_SESSION['recenttags'][$i]);
					array_push($_SESSION['recenttags'], $tag);
				}

				// if it's not published and the person is not the editor, reject the tag addition
				if(!$clipInfo['published'] && $userid != $clipInfo['editor']) {
					exit(json_encode(array(
						'tagsuccess' => false
						, 'message' => $clipid.' is not published and you are not the editor.'
					)));
				}

				// if the tag already exists
				// TODO: this should possibly be "==" because there should only ever be one instance of a tag
				if ($countOfTagsMatchingTagtext >= 1) {

					$tagid = $taglookup[0]['id'];

					// check if the clip is already tagged with this tag
					$db->rawQuery(
						'SELECT id FROM clips_x_tags 
						WHERE clipid=? AND tagid=?'
						, array($clipid, $tagid)
					);

					// if the tag is already tagged, then exit
					if ($db->count == 1) {
						exit(json_encode(array(
							'tagsuccess' => false
							, 'message' => 'Tag "'.$tagtext.'" is already on clip '.$clipid.'.'
						)));
					}

					// else, simply continue

				} else {
					// tag not created, so create the tag
					$data = array(
						'tagname' => $tagtext,
						'addedby' => $userid
					);

					$tagid = $db->insert('tags', $data);

					// continue with this new tag id

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
						, 'message' => 'Tag "'.$tagtext.'" could not be added to clip '.$clipid.'. Unexpected error.'
					)));
				}

			// if the request is "remove" it must also be unpublished and the current user must be the editor.
			} elseif ($action == 'remove' && !$clipInfo['published'] && $userid == $clipInfo['editor']) {
				
				// if the tag definitely exists
				// TODO: this should possibly be "==" because there should only ever be one instance of a tag
				if ($countOfTagsMatchingTagtext >= 1) {

					// grab the tagid
					$tagid = $taglookup[0]['id'];

					// remove the (all) links between the clip and tag
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
				// it was either not "add" or "remove", or a "remove" was attempted on a clip that wasn't unpublished and/or the user wasn't the editor.
				exit(json_encode(array(
					'tagsuccess' => false
					, 'message' => 'Denied action, this clip cannot be modified in this way.'
					, 'clip is unpublished' => $clipInfo['published'] === 0
					, 'you are clip editor' => $userid === $clipInfo['editor']
				)));
			}
		} else {
			// clip does not exist
			exit(json_encode(array(
				'tagsuccess' => false
				, 'message' => 'Clip does not exist.'
			)));
		}
	} else {
		// postfields not set correctly
		exit(json_encode(array(
			'tagsuccess' => false
			, 'message' => 'Incorrect postfields set.'
		)));
	}
} else {
	// not logged in
	exit(json_encode(array(
		'tagsuccess' => false
		, 'message' => 'Not logged in.'
	)));
}
