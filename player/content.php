<?php

$cols = array('c.id', 'c.project'
			// , 'oc.countryname AS countryName'		// disabled for now, redundant
		); 

$db->where('published', 1); // published clips
$db->where('(todelete = 0 OR todelete IS NULL)'); // not deleted clips

$table = 'clips c'; // table

// $db->join('opt_countries oc','c.country=oc.id','LEFT');

$db->orderBy("RAND ()");

$limit = 1500;

$results = $db->get($table, $limit, $cols);

// echo '<pre>';
// print_r($results);
// echo '</pre>';

$cols = array('p.id', 'p.name AS projectName');
$table = 'projects p';
$results_projects = $db->get($table, NULL, $cols);

?>
<script type="text/javascript">
	var playlist = <?php echo json_encode($results); ?>;
	var projectsRaw = <?php echo json_encode($results_projects); ?>;
	var projects = [];
	projectsRaw.forEach(function(project){
		projects[project.id] = project.projectName;
	});
</script>
<link href="https://fonts.googleapis.com/css?family=Anonymous+Pro|Source+Sans+Pro:300" rel="stylesheet">
<style type="text/css">
	/* fonts for above	*/
	/*font-family: 'Anonymous Pro', monospace;*/
	/*font-family: 'Source Sans Pro', sans-serif;*/
</style>
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
<style type="text/css">
	main .row{
		padding: 2.5vw;
	}
	#playerContainer{
		height: calc(95vw * 0.5625);		/* 95vw is 100 - 2x the padding (currently 2.5vw), 0.5625 is 9/16 */
		position: relative;
		overflow: hidden;				/* necessary for the +2 and -1 hack below */
	}
	#playerContainer video{
		width: 100%;
		height: 100%;
	}
	#playerTextOverlay{
		position: absolute;
	    bottom: 0px;
	    display: inline-block;
	    padding: 2.5vw;
	    padding-bottom: 1.3vw;
	    width: calc(100% + 2px);		/* +2px and -1px are hack bullshit to make the textoverlay */
	    								/* background completely cover the video and still be centered. */
	    margin-left: -1px;
	    background: linear-gradient(0deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.15) 60%, rgba(0,0,0,0) 100%);
	    color: rgba(255,255,255,0.9);
	    font-size: 2.2vw;
	    font-weight: 300;
	    font-family: 'Source Sans Pro', sans-serif;
	}
	#playerTextClipId{
		font-size: 70%;
	    background-color: rgba(0,0,0,0.5);
	    border-radius: 0.5vw;
	    padding: 0.3vw 0.9vw;
	}
	#buttonRow{
		text-align: center;
	}
/*	#playerLogoOverlay{
		background-image: url(../cssjs/logo-1-20190131.png);
	    background-size: contain;
	    background-repeat: no-repeat;
	    position: absolute;
	    top: 0;
	    z-index: 2;
	    height: 44vw;
	    width: 16vw;
	    margin: 2vw 2.3vw;
	    opacity: 0.5;
	    pointer-events: none;
	}*/

	/*ALT SIZING*/
	
	/*#playerLogoOverlay{
		width: 36vw;
	}
	#playerTextOverlay{
		font-size: 1.7vw;
	}*/
</style>

<div id="playerContainer" allowfullscreen>
	<video id="myVideo" autoplay muted>
	    <source src="" id="mp4Source" type="video/mp4">
	    Your browser does not support the video tag.
	</video>
	<!-- <div id="playerLogoOverlay"></div> -->
	<div id="playerTextOverlay">
		<span class="left" id="playerTextProjectName"></span>
		<span class="right" id="playerTextClipId"></span>
	</div>
</div>
<div id="buttonRow" class="row">
	<a class="waves-effect waves-light btn-large" onclick="playerContainer.requestFullscreen();"><i class="material-icons right">fullscreen</i>Go Fullscreen</a>
</div>

<script type='text/javascript'>
	var playlistPosition = 0;
	var clipId = playlist[playlistPosition].id;
	var player = document.getElementById('myVideo');
	var mp4Vid = document.getElementById('mp4Source');
	var playerContainer = document.getElementById('playerContainer');
	var playerTextClipId = document.getElementById('playerTextClipId');

	// load initial clip
	loadClip();

	// event listeners
	player.addEventListener('ended', videoEnded, false);
	player.addEventListener('durationchange', videoDurationReady);

	// stuff for looping decision tree inside fn videoEnded()
	var timesClipPlayed = 0;
	var clipDuration = 0;
	function videoDurationReady() {
   		clipDuration = player.duration;
   	}

	function updatePlayerTextOverlay() {
		$(playerTextClipId).text('Clip #'+clipId
			// +" - "+playlist[playlistPosition].countryName 		// Country Name redundant for now.
			);
		$(playerTextProjectName).text('Project: '+projects[playlist[playlistPosition].project]);
	}

	function loadClip() {
      	$(mp4Vid).attr('src', "../media/?clip="+clipId+"&q=f");			
      	player.load();
      	// player.play(); // not necessary, because of autoplay attribute?
      	updatePlayerTextOverlay();
	}

	function videoEnded(e) {
		if (!e) {
			e = window.event; 
		}

		timesClipPlayed++;

		// the decision tree
		var repeatClip = false;									// by default, don't repeat
		if (clipDuration < 4 && timesClipPlayed < 3) {			// repeat if less than 4 seconds and not yet played 3 times.
			repeatClip = true;
		} else if (clipDuration < 8 && timesClipPlayed < 2) {	// repeat if less than 8 seconds and not yet played 2 times.
			repeatClip = true;
		}

		// the action
		if (repeatClip) {
			player.play();
		} else {
	      	incrementPosition();
	      	clipId = playlist[playlistPosition].id;
	      	timesClipPlayed = 0;
			loadClip();
		}
   	}

   	function incrementPosition() {
   		playlistPosition++;
   		if (playlistPosition >= playlist.length) {
   			playlistPosition = 0;
   		}
   	}

</script>