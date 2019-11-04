<?php 
require '/var/www/html/creative.lonelyleap.com/archive/includes/0-base.php';


if (isset($_GET['logout'])) {
	// logout procedure
	activitylog('logout', $_SESSION['nickname'] . ' logged out.');
	session_destroy();
	unset($_SESSION);
} else if (isset($_GET['login'])) {
	if (!(isset($_POST['email']) && isset($_POST['password']))) {
		echoError('This page has been modified.');
		activitylog('loginfail', 'Someone at '.$_SERVER['REMOTE_ADDR'].' has modified the login page to submit without having both fields set.');
	} else {
		// request the row: password, id, nickname, interfaceprefs
		$data = $db->rawQuery('SELECT password, id, nickname, interfaceprefs FROM users WHERE email=?', array($_POST['email']));
		// check if email address is in the system
		if ($db->count == 1){
			if (password_verify($_POST['password'], $data[0]['password'])) {
				$_SESSION = array(
					'logged_in' => true,
					'userid' => $data[0]['id'],
					'nickname' => $data[0]['nickname'],
					'recenttags' => array(),
					'interfaceprefs' => json_decode($data[0]['interfaceprefs'], true) // true returns an array instead of an object
				);
				activitylog('login', $_SESSION['nickname'] . ' logged in from ' . $_SERVER['REMOTE_ADDR']);
				if (isset($_GET['iframe'])) {
					$ajaxresponse = array(
						'success' => true
					);
					exit(json_encode($ajaxresponse));
				} else {
					echoSuccess('Welcome.');	
				}
			} else {
				activitylog('loginfail', 'Someone at '.$_SERVER['REMOTE_ADDR'].', using email '.$_POST['email'].' has used the wrong password');
				if (isset($_GET['iframe'])) {
					$ajaxresponse = array(
						'success' => false,
						'message' => 'Wrong Password'
					);
					exit(json_encode($ajaxresponse));
				} else {				
					echoError('Wrong password. Contact us if you need to reset');
				}
			}
		} else {
			activitylog('loginfail', 'Someone at '.$_SERVER['REMOTE_ADDR'].' has tried the email: '.$_POST['email']);			
			if (isset($_GET['iframe'])) {
				$ajaxresponse = array(
					'success' => false,
					'message' => 'Wrong Email'
				);
				exit(json_encode($ajaxresponse));
			} else {
				echoError('Wrong email address.');
			}
		}	
	}
} else if (isset($_GET['register'])) {
	// registration procedure
	// make sure all variables are set
	if (!(isset($_POST['email']) && isset($_POST['code']) && isset($_POST['password']) && isset($_POST['nickname']))) {
		echoError('This page has been modified.');
	} else {
		// get the variables i need 
		$data = $db->rawQuery('SELECT registration_open, registration_code FROM users WHERE email=?', array($_POST['email']));
		// check that email address is in the system
		if ($db->count == 1) {
			// check that the email is available to be registered, and get the code at the same time
			if ($data[0]['registration_open'] == 1) {
				// check that the code is two digits
				if (strlen($_POST['code']) == 2) {
					// check that the code matches the one in the database, case insensitive
					if (strtolower($data[0]['registration_code']) == strtolower($_POST['code'])) {
						// check that the nickname is available
						$db->rawQuery('SELECT * FROM users WHERE nickname=?',array($_POST['nickname']));
						if ($db->count == 0) {
							// hash the password
							$passwordhash = password_hash($_POST['password'], PASSWORD_DEFAULT);
							// store the hashed password
							// set the access code column to NULL
							// set the available to be registered to FALSE
							$db->rawQuery('UPDATE users SET password = ?, nickname = ?, registration_code = ?, registration_open = ? WHERE email=?', array($passwordhash, $_POST['nickname'], null, 0, $_POST['email']));
							echoSuccess('Thanks for creating an account. Go ahead and log in with it.');							
						} else {
							// get the email address from the database and echo it here.
							$conflictinguser = $db->rawQuery('SELECT email FROM users WHERE nickname=?', array($_POST['nickname']))[0]['email'];
							echoError('The name you picked as your first name is already being used by ' . $conflictinguser . '. This is the name used to identify you around the site. Please pick a new name, add your last initial, or maybe reach out to that person to change theirs if you really want that name. Thanks.');
							activitylog('loginfail', 'Nickname conflict between '.$conflictinguser.' and '.$_POST['email']);
						}
					} else {
						echoError('Wrong invite code. Double-check your email, then contact us with any issues.');
					}
				} else {
					echoError('Invite code must be two digits.');
				}
			} else {
				echoError('Already registered. Contact us if you would like to reset.');
			}
		} else {
			echoError('Email address not allowed. Check for typos.');
		}
	} 
}
?>
<!doctype html>
<?php require $includepath . '2-head.php'; ?>
<?php 
if (isset($_SESSION['logged_in'])) {
	require $includepath . '3-body.php'; 	
} else {
	require $includepath . '8-loginpage.php';
}
?>
</html>