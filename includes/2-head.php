<head>
	<meta charset="UTF-8">
	<title>Archive</title>
	<script type="text/javascript">
	// load the php session prefs
	var interfaceprefs = <?php 
	if (isset($_SESSION['interfaceprefs'])){
		echo json_encode($_SESSION['interfaceprefs']); 	
	} else {
		echo 0;
	}
	?>
	</script>
	<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/css/materialize.min.css"> -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js"></script> -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/css/selectize.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/js/standalone/selectize.min.js"></script>
	<style type="text/css">

		input:-webkit-autofill {
		    /*-webkit-box-shadow: 0 0 0 30px white inset !important;*/
		    background-color: rgb(255,255,255) !important;
		    background-image: url('/archive/cssjs/white.png') !important;
		}
		body{
			background-color: #eee;
		}
		.thumb.active{
			z-index: 999;
		}
		.input-field label a{
			pointer-events: all;
		}
		.switch label{
			white-space: nowrap;
		}
		.brand-logo.left{
			padding-left: 3px !important;
		}
		p.truncate{
			width: 90%;
		}
/* Hack bullshit for preventing the autocomplete from pushing page down */
/*		ul.autocomplete-content.dropdown-content {
			position: absolute;
		    margin-left: 2px;
		    margin-top: -18px;
		}*/
		#leftbar{
			position: fixed;
			width: inherit;
			border-top: white;
			border-top-width: 65px;
			border-top-style: outset;
			top: 0;
			left: 0;
			padding-left: 14px;    
		}
		#mainContent{
			background-color: white;
			border-top: white;
			border-top-width: 65px;
			border-top-style: outset;
			margin-top: -65px;
			padding: 20px;
		}
		.btn:not(#loginButton):not(#searchButton):focus{
			background-color: #0099e4;
			/*transform: scale(1.2);*/
		}
		video{
		  width: 100%;
		  opacity: 0.999;
		  display: block;
		}
		nav{
			/*box-shadow bleeds through*/ 
			/*background-color: rgba(255,255,255,0.9); */
			background-color: #fff;
			opacity: 0.9;
		}
		.toolbarText{
			color: rgba(0,0,0,0.87) !important;
		}
		.sliderForm{
		  display: inline-block;
		  width: 150px;
		}

		/* materialize overrides */ 
		.row{
		  margin-bottom: 0;
		}

		.responsive{
		  display: none;
		  height: 70vh;
		  width: 70vw;
		  position: fixed;
		  top: 15vh;
		  left: 15vw;
		}
		@media screen and (max-width:700px) { 
		  #lessthan700{
		    display: inline-block;
		  }
		  header, main, footer{
		    display: none;
		  }
		}

/*		@media screen and (-webkit-min-device-pixel-ratio:0) { 
		  #notChrome{
		    display: inline-block;
		  }
		  header, main, footer{
		    display: none;
		  }
		}*/
		/*stick footer to bottom of page*/
		body {
		  display: flex;
		  min-height: 100vh;
		  flex-direction: column;
		}

		main {
		  flex: 1 0 auto;
		}
		.pagination li.active {
			background-color: #26a69a;
		}	

	</style>	
</head>