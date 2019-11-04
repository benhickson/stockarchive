<?php
echo '<h5>Logged in as ';
echo $_SESSION['nickname'].'</h5>';
echo '.<br><a href="?logout">Click to log out.</a>';
// print_r($_SESSION);
?>

<style>
	/* if #leftbar and #mainContent don't exist, use this */
	nav{
		background-color: #fff;
		opacity: 1;
	}
	nav *{
		opacity: 0.9;
	}
	nav * *{
		opacity: 1;
	}
</style>