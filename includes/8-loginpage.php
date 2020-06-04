<?php
function addParams($name, $value)
{
    $params = $_GET;
    unset($params['logout']);
    unset($params['login']);
    unset($params['register']);
    $params[$name] = $value;
    return '?'.http_build_query($params);
}
?>
<style>
	.loginrow{
		text-align: center;
	}

	#logincard{
		margin-top: 20vh;
		opacity: 0;
		width: 480px;
		display: inline-block;
		text-align: left;		
	}
	#logincard .card-action a{
		cursor: pointer;
	}
	#regFieldEmail, #regFieldCode{
		display: inline-block;
	}
	#regFieldEmail{
		width: 78%;
    	margin-right: 1%;
	}
	#regFieldCode{
		width: 20%;
	}


	
	/* Hacks to fix gamma shift issues in chrome */
	.card-image{
		background: #000;
		height: 270px;
	}
	#loginVideo{
		opacity: 0.999;
	}
<?php
if (isset($_GET['iframe'])){
	?>

	body{
		background: rgba(238, 238, 238, 0.95);
	}

	/*imitating materialize class of .z-depth-4*/
	#logincard.card{
		-webkit-box-shadow: 0 8px 10px 1px rgba(0,0,0,0.14), 0 3px 14px 2px rgba(0,0,0,0.12), 0 5px 5px -3px rgba(0,0,0,0.3);
    	box-shadow: 0 8px 10px 1px rgba(0,0,0,0.14), 0 3px 14px 2px rgba(0,0,0,0.12), 0 5px 5px -3px rgba(0,0,0,0.3);		
	}

	<?php
}
?>
</style>
<div class="row loginrow">
<div id="logincard" class="card">
	<div class="card-image">
		<video id="loginVideo" muted autoplay loop src="//<?= $baseURL ?>/media/?clip=32&q=h"> </video>
        <span class="card-title">Archive</span>
	</div>
	<div class="card-content">
		<span class="card-title grey-text text-darken-4">Log in to continue</span>
		<form id="loginform" action="?" method="post">
			<input type="hidden" name="action" value="login" />
			<div class="input-field">
				<input id="logemail" type="email" name="email" class="validate" required>
				<label for="logemail">Email</label>
			</div>		
			<div class="input-field">
				<input id="logpassword" type="password" name="password" class="validate" required>
				<label for="logpassword">Password</label>
			</div>
			<div class="row align-right">
				<input id="loginButton" type="submit" value="Log in" class="btn right">				
			</div>
		</form>
	</div>
	<div class="card-action">
		<a id="registerButton" class="activator">Register</a>
	</div>	
	<div class="card-reveal">
		<span class="card-title grey-text text-darken-4">Register as a New User<i class="material-icons right">close</i></span>
		<form id="registerform" action="<?php echo addParams('register','true'); ?>" method="post">
			<p>Welcome!</p>
			<p>This is a private system for the Lonelyleap team. If you've been invited to join, please enter your email and your 2-character invite code below.</p>
			<div class="input-field" id="regFieldEmail">
				<input id="regemail" type="email" name="email" class="validate" required>
				<label for="regemail">Email</label>
			</div>
			<div class="input-field" id="regFieldCode">
				<input id="code" type="text" name="code" class="validate" maxlength="2" data-length="2" required>
				<label for="code">Code</label>
			</div>			
			<p>Please create a password.</p>
			<div class="input-field">
				<input id="regpassword" type="password" name="password" class="validate" required>
				<label for="regpassword">Password</label>
			</div>
			<p>Please tell us how you'd like to be called (first name, or whatever you want, and yes, you can change this later).</p>
			<div class="input-field">
				<input id="nickname" type="text" name="nickname" class="validate" required>
				<label for="nickname">First Name</label>
			</div>			
			<div class="row align-right">
				<input type="submit" value="Register" class="btn right">				
			</div>
		</form>		
	</div>
</div>
</div>
<!-- <div id="notChromeLoginDiv" style="display: none;"><h3>This site is only built to run on Chrome.</h3></div> -->
<script type="text/javascript">
	// hide for browsers other than chrome
	// function isNotChrome(){
	// 	$('#notChromeLoginDiv').show();
	// 	$('.loginrow').hide();
	// 	console.log('is not chrome.');
	// }
	// var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
	// if (!isChrome) isNotChrome();

	var iframe = false;
	<?php if (isset($_GET['iframe'])) { echo 'iframe = true;'; } ?>
	$(document).ready(function(){
		document.getElementById('loginVideo').onloadedmetadata = function(){
			$('#logincard').fadeTo(600, 1);
		};
		if (iframe) {
			var loginButton = $('#loginButton');
			$('#loginform').on('submit',function(event){
				// preventing it from using its traditional submission
				event.preventDefault();
				// message the user
				loginButton.attr('value','Checking...');
				// do the login
				var data = new Object();
				data.email = $('#logemail').val();
				data.password = $('#logpassword').val();
				data.action = 'login';
				$.ajax('?',{ 
				  type: 'POST',
				  data: data,
				  success: function(response){
				  	var responseObject = JSON.parse(response);
				    if (responseObject.success){
				    	// message the user
				    	loginButton.attr('value','Thanks!');
				    	// fade it out
				    	parent.$('#loginFrame').fadeOut();
				    } else {
				    	// message the user
				    	loginButton.attr('value',responseObject.message);
				    }
				  }
				});      

			});
		} else {
			
		}
	});
</script>
