<?php

if (isset($_GET['clip'])){
    if ($_GET['clip'] == 'first'){
        $result = $db->rawQuery('SELECT id FROM clips WHERE published=0 AND uploader=? AND todelete=0 ORDER BY id ASC LIMIT 1', array($_SESSION['userid']));
        if ($db->count == 1){
            $clipid = $result[0]['id'];
            $clippage = true;      
        } else {
            $clippage = false;
        }
    } else {
        $clipid = $_GET['clip'];
        // check if access to this clip is correct.
        $db->rawQuery('SELECT id FROM clips WHERE published=0 AND todelete=0 AND uploader=? AND id=? LIMIT 1', array($_SESSION['userid'], $clipid));
        if ($db->count == 1){
            $clippage = true;
            if (isset($_GET['delete'])){
                // mark the clip for deletion
                $db->rawQuery('UPDATE clips SET todelete=1 WHERE id=? AND published=0 AND uploader=?',array($clipid, $_SESSION['userid']));
                $linkstodelete = $db->rawQuery('SELECT linkfull, linkhalf, linkquarter, linkthumb FROM clips WHERE id=? AND published=0 AND uploader=?',array($clipid, $_SESSION['userid']));
                // mark the four underlying files for deletion, after marking the clip
                $db->rawQuery('UPDATE files SET todelete=1 WHERE id IN ('.$linkstodelete[0]['linkfull'].','.$linkstodelete[0]['linkhalf'].','.$linkstodelete[0]['linkquarter'].','.$linkstodelete[0]['linkthumb'].');');
                // send back to ?clip=first after deleting
                echo '<script type="text/javascript">window.location = "?clip=first";</script>';
            }
        } else {            
            echoError('This is either not your clip, or the clip is already published, or the clip simply never existed.');
            $clippage = false;
        }
    }
} else {
    $clippage = false;
}
?>

<style>
  #fileList video{
    max-height: 135px;
    max-width: 240px;
  }
  #fileList span{
    display: block;
  }
  p.limit{
    max-width: 630px;
  }
  td.statustd div.progressContainer{
    height: 100px;
    width: 100px; 
  }
  #videoContainer{
    max-width: 720px;
  }
  #videoPlaceholder{
    background-color: rgba(101, 199, 190, 0.08);
  }
  #videoPlaceholder .flip{
    transform: rotate(180deg);
  }
  #transcodeStatus{
    margin: 25.1%;
  }
  #leftbar{
    height: 80vh;
    overflow: auto;
  }
</style>
<div class="col m4 l3 xl2">
  <div id="leftbar">
    <p>Unpublished Uploads</p>
    <?php
    $currentuploads = $db->rawQuery('SELECT id, uploadfilename, description FROM clips WHERE published=0 AND uploader=? AND todelete=0 ORDER BY uploadfilename ASC', array($_SESSION['userid']));
    foreach ($currentuploads as $clip) {
      if ($clip['description'] == '') {
        $displayname = $clip['uploadfilename'];
      } else {
        $displayname = $clip['description'];
      }
      echo '<p class="truncate" data-clipid="'.$clip['id'].'"><a href="?clip='.$clip['id'].'">'.$displayname.'</a></p>';
    }
    ?>
  </div>
</div>
<div class="col m8 l9 xl10" id="mainContent">

<?php 

if ($clippage) { 
    require 'content-clippage.php';
} else { 
    require 'content-uploadpage.php';
} 

?>

</div>

<script src="/archive/cssjs/progressbar.min.js"></script>
<script type="text/javascript"> // progressbar stuff

    var progressBars = new Array(); // reference array

    var successColor = '#4CAF50';
    var baseColor = '#4c63af';

    // the name is the the name of the bar in the reference array, and the id of the destination container
    function createNewProgressBar(name) { 
        var bar = new ProgressBar.Circle('#'+name, {
            color: '#aaa',
            strokeWidth: 5, // unit is the percentage of SVG canvas's size (the container)
            trailColor: '#eee',
            trailWidth: 1,
            // duration: 1400,
            // easing: 'easeInOut',
            from: { color: baseColor, width: 1 },
            to: { color: successColor, width: 5 },
            step: function(state, circle) {
                circle.path.setAttribute('stroke', state.color);
                circle.path.setAttribute('stroke-width', state.width);
                
                var value = Math.round(circle.value() * 100);
                if (value === 0) {
                    circle.setText('');
                } else if (value === 100) {
                    circle.setText('Uploaded!');
                } else {
                    circle.setText(value);
                }

            },
            warnings: true // allows warnings in the console
        });

        // assign it to the reference array
        progressBars[name] = bar;        
    }

    function changeProgressBarValue(bar, targetValue){
        bar = progressBars[bar];
        bar.animate(targetValue, {
            duration: 400,
            easing: 'easeInOut'
        });
    }    
</script>