<?php

if (isset($_POST['width']) && isset($_POST['height'])) {
  // add new resolution option
  $data = array(
    'width' => $_POST['width'],
    'height' => $_POST['height'],
    'addedby' => $_SESSION['userid']
  );
  $id = $db->insert('opt_resolutions', $data);
  // update the current row to that option
  $db->rawQuery('UPDATE clips SET rawresolution=? WHERE id=?',array($id, $clipid));
}

if (isset($_POST['projectname']) && isset($_POST['rawlocation'])) {
  // add new project option
  $data = array(
    'jobnumber' => $_POST['jobnumber'],
    'producer' => $_POST['producer'],
    'name' => $_POST['projectname'],
    'notes' => $_POST['notes'],
    'rawlocation' => $_POST['rawlocation'],
    'addedby' => $_SESSION['userid']
  );
  $id = $db->insert('projects', $data);
  // update the current row to that option
  $db->rawQuery('UPDATE clips SET project=? WHERE id=?',array($id, $clipid));
}

if (isset($_POST['client'])) {
  // add new client option
  $data = array(
    'name' => $_POST['client']
  );
  $id = $db->insert('clients', $data);
  // update the current row to that option
  $db->rawQuery('UPDATE clips SET restrictedtoclient=? WHERE id=?', array($id, $clipid));
}

if (isset($_POST['model'])) {
  // add new camera option
  $data = array(
    'model' => $_POST['model'],
    'addedby' => $_SESSION['userid']
  );
  $id = $db->insert('opt_cameras', $data);
  // update the current row to that option
  $db->rawQuery('UPDATE clips SET camera=? WHERE id=?',array($id, $clipid));
}

if (isset($_POST['countryname'])) {
  // add new country
  $data = array(
    'countryname' => $_POST['countryname'],
    'addedby' => $_SESSION['userid']
  );
  $id = $db->insert('opt_countries', $data);
  // update the current row to that option
  $db->rawQuery('UPDATE clips SET country=? WHERE id=?',array($id, $clipid));
}

$clip = $db->rawQuery(
  'SELECT c.description, c.project, c.rawresolution, c.camera, c.uploadfilename, c.transcodesready, 
  c.year, c.month, c.day, c.country, c.region, c.city, p.rawlocation, c.restrictedtoclient 
  FROM clips c 
  LEFT JOIN projects p 
  ON c.project=p.id 
  WHERE c.id=?'
  , array($clipid))[0];
?>

<style type="text/css">
/*  .select-wrapper input.select-dropdown{
    display: none;
  }*/
  input[type=number]::-webkit-inner-spin-button, 
  input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
  }
  #databox{
    font-size: 84%;
    opacity: 0.5;
    padding-top: 56px;
  }
  @media (max-width: 992px) {
    #publishButton, #deleteButton {
      max-width: 100%;
      padding: 0 15%;
    }
  }
  p.currentclip{
    background-color: #fff;
    padding: 1rem 7.8%;
    margin-left: -7%;
    width: 109%;    
  }
  p.currentclip a{
    color: rgba(0,0,0,0.87)
  }
  .chips .input{
    color: rgba(0,0,0,0.87);
  }
  #recenttags{
    font-size: 80%;
    opacity: 0.8;    
  }
  #recenttags a{
    cursor: pointer;
    margin-left: 9px;
  }
</style>

<script type="text/javascript">

  function playPause(e){
    e = e || window.event;
    var player = e.target || e.srcElement;
    return player.paused ? player.play() : player.pause();
  }

  var clipid = <?php echo $clipid; ?>;

  var videoElement = '<video onClick="playPause()" muted loop autoplay src="//creative.lonelyleap.com/archive/media/?clip='+clipid+'&q=h"></video>';

  function loadVideo(){
    $('#videoPlaceholder').remove();
    $('#videoContainer').append(videoElement);
  }

  var countdownneeded = true;

  function countdown(){
    countdownneeded = false;
    console.log('countdown');
    var target = $('#countdown');
    var currentCount = target.text();
    if (currentCount > 0){
      target.text(currentCount - 1);
      setTimeout(countdown, 1000);
    }
  }

  var currentstatus;

  function checkIfTranscodesReady(){
    console.log("checking...");
    $.ajax({
      url: "transcode_status.php?clip=<?php echo $clipid; ?>",
      success: function(response){
        response = JSON.parse(response);
        console.log(response);
        if (response.status == currentstatus){
          // do nothing, just loop
          setTimeout(checkIfTranscodesReady, 4000);
        } else {
          if (response.status == 2) {
            $('#transcodeStatus').html('Done!');
            setTimeout(loadVideo, 1000);
          } else {
            if (response.status == 1){
              $('#transcodeStatus').html('Transcoding now.<!-- <span id="countdown">'+response.timer+'</span> seconds remaining.-->');
            } else if (response.status == 0){
              $('#transcodeStatus').html('Queued for transcode.<!-- <span id="countdown">'+response.timer+'</span> seconds until transcode starts.-->');
            }
            currentstatus = response.status;                
            setTimeout(checkIfTranscodesReady, 4000);
            if (countdownneeded) {
              setTimeout(countdown, 1000);
            }
          } 
        } 
      }
    })
  }

  var lastSpecs = new Object();

  <?php 
  if (isset($_SESSION['lastSpecs'])){
    echo 'lastSpecs = JSON.parse(\''.json_encode($_SESSION['lastSpecs']).'\');'."\n";
  }
  ?>

  var nextclip;

  function publishClip(){
    var data = new Object();
    data.clipid = clipid;
    data.attemptPublish = true;
    $.ajax('details.php',{
      type: 'POST',
      data: data,
      success: function(response){
        console.log(response);
        var published = JSON.parse(response)[0];
        var message = JSON.parse(response)[1];
        if (published) {
          // redirect to next clip
          window.location = nextclip ? "?clip="+nextclip : "?clip=first";
        } else {
          alert(message);
        }
      }
    });
  }

  function publishReady(bool){
    if (bool) {
      $('#publishButton').removeClass('disabled');
    } else {
      $('#publishButton').addClass('disabled');      
    }
  }  

  function sendData(obj) {
    var data = new Object();
    data.clipid = clipid;
    data.name = obj.name;
    data.value = obj.value;
    $.ajax('details.php',{
      type: 'POST',
      data: data,
      success: function(response) {
        console.log(response);
        var parse = JSON.parse(response);
        lastSpecs = parse[1];
        var ableToPublish = parse[2];
        if (data.name == 'project'){
          var locationtext = parse[0]['rawlocation'];
          $('#rawlocationSpan').text(locationtext);
        }
        if (ableToPublish && $('.datecheck:invalid').length == 0) {
          publishReady(true);
        } else {
          publishReady(false);
        }
      }
    });
  }

  function updateFieldsWithLastSpecs(){
    Object.keys(lastSpecs).forEach(function(key,index) {
      var el = document.getElementById(key);
      if (el.value == '') {
        el.value = lastSpecs[key];        
      }
    });
    $('select').formSelect();
    $('.trigger').trigger('change');
  }

  function matchSpecs(obj){
    var data = new Object();
    data.matchSpecs = obj.checked;
    $.ajax('details.php',{
      type: 'POST',
      data: data,
      success: function(response){
        console.log(response);
      }
    });
    console.log(data);
    if (obj.checked) {
      updateFieldsWithLastSpecs();
    }
  }

  // set up a var to set in doc ready
  var tagField = null;
  // set up an event to trigger
  var myEvent = jQuery.Event("keypress");
  myEvent.which = 13; //choose the one you want
  myEvent.keyCode = 13;  

  function addTagToField(tagtext){
    console.log(tagField, tagtext);
    tagField.val(tagtext).focus().trigger(myEvent);
  }

  var currentclipParagraph;

  $(document).ready(function() {
    // enable any <select>s
    $('select').formSelect();

    <?php if (isset($_SESSION['matchSpecs'])) { echo 'updateFieldsWithLastSpecs();'; } else {
      echo '$("#description").trigger("change");';
    } ?>

    // highlight the selected clip
    currentclipParagraph = $('p[data-clipid='+clipid+']');
    currentclipParagraph.addClass('currentclip');
    // get the next clip
    nextclip = currentclipParagraph.next().data('clipid');


    // enable the tag field
    $('.chips').chips({
      data: [
        <?php
          $tags = $db->rawQuery('SELECT t.id, t.tagname FROM clips_x_tags cxt LEFT JOIN tags t ON cxt.tagid=t.id WHERE cxt.clipid=?',array($clipid));
          foreach ($tags as $tag) {
            echo '{tag: '.json_encode($tag['tagname']).',id: "'.$tag['id'].'"},';
          }
        ?>
      ],
      autocompleteOptions: {
        data: {
          <?php 
            $tags = $db->rawQuery('SELECT tagname FROM tags WHERE deleted!=1');
            foreach ($tags as $tag) {
              echo json_encode($tag['tagname']).': null,';
            }
          ?>
        }
      },
      onChipAdd: function(e, chip){ addTag(<?php echo $clipid; ?>, chip.firstChild.textContent); },
      onChipDelete: function(e, chip){ removeTag(<?php echo $clipid; ?>, chip.firstChild.textContent); },
      placeholder: 'Tags',
      secondaryPlaceholder: '+ Tag'
    });

    // set that tagField
    tagField = $('#tags input');
    
    // just setting them red, rather than using materialize's red and green "validate" class
    $(document).bind('change', function(e){
      if( $(e.target).is(':invalid') ){
          $(e.target).addClass('invalid');
      } else {
          $(e.target).removeClass('invalid');
      }
    });

  });

</script>

<div class="row">
  <h3 class="col s12">Clip Details</h3>
</div>
<div class="row">
  <div class="col s9">
    <div id="videoContainer">
      <?php
      if ($clip['transcodesready'] == 1){
        echo '<script type="text/javascript">loadVideo();</script>';
      } else { ?>
        <div id="videoPlaceholder">
          <div class="progress flip">
            <div class="indeterminate"></div>
          </div>
          <p id="transcodeStatus">&nbsp;</p>
          <div class="progress ">
            <div class="indeterminate"></div>
          </div>
        </div>
        <script type="text/javascript">checkIfTranscodesReady();</script>
     <?php }
      ?>
    </div>            
  </div>
  <div class="col s3">
    <!-- Modal Trigger -->
    <a id="deleteButton" class="waves-effect waves-light btn modal-trigger truncate" href="#modal1">Delete Clip</a><br /><br />
    <!-- Retranscode button -->
    <a id="retranscodeButton" class="waves-effect waves-light btn truncate" target="_blank" href="/archive/upload/transcode.php?retranscode=<?php echo $clipid; ?>">Re-Transcode Clip</a>
    <div class="switch">
    <br />
    <p>Repeat specs of last clip.</p>
      <label>
        Off
        <input name="matchSpecs" type="checkbox" <?php if (isset($_SESSION['matchSpecs'])) { echo 'checked'; } ?> onchange="matchSpecs(this);">
        <span class="lever"></span>
        On
      </label>
    </div>

    <!-- Modal Structure -->
    <div id="modal1" class="modal">
      <div class="modal-content">
        <h4>Delete this clip</h4>
        <p>Are you sure? You'll have to upload the clip again if you change your mind.</p>
        <p>Please remember to remove the clip from the raw footage archive as well.</p>
        <h5><?php echo $clip['uploadfilename']; ?></h5>
      </div>
      <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-teal btn-flat ">Cancel</a>
        <a href="?clip=<?php echo $clipid; ?>&delete" class="modal-action btn-flat">Confirm Delete</a>
      </div>
    </div>
    <script type="text/javascript">
      $(document).ready(function(){
        $('.modal').modal();
      });
    </script>    
  </div>
</div>
<p>&nbsp;</p>
<div class="row">
  <div class="input-field col s6 l3">
    <select id="rawresolution" name="rawresolution" onchange="sendData(this)" class="trigger">
      <?php 
      echo '<option value="" disabled';
      if ($clip['rawresolution'] == '') {
        echo ' selected';
      }
      echo '>Please Select</option>';
      $opt_resolutions = $db->rawQuery('SELECT id, width, height FROM opt_resolutions ORDER BY width DESC');
      foreach ($opt_resolutions as $resolution) {
        echo '<option value="'.$resolution['id'].'"';
        if ($resolution['id'] == $clip['rawresolution']){
          echo ' selected';
        }
        echo '>'.$resolution['width'].'x'.$resolution['height'].'</option>';
        echo "\n";
      }
      ?>
    </select>
    <label>Original Resolution | <a class="modal-trigger" href="#newResolution" tabindex="-1">Add New</a></label>
    <div id="newResolution" class="modal">
      <form autocomplete="off" method="post" action="?clip=<?php echo $clipid; ?>">
      <div class="modal-content row">
        <h4>Add a new resolution</h4>
        <div class="input-field col s5">
          <input id="width" name="width" type="text" class="validate" required pattern="^[1-9]\d*$">
          <label for="width">Width (pixels)</label>
        </div>
        <div class="input-field col s5">
          <input id="height" name="height" type="text" class="validate" required pattern="^[1-9]\d*$">
          <label for="height">Height (pixels)</label>
        </div>
      </div>
      <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-teal btn-flat ">Cancel</a>
        <input type="submit" class="modal-action btn-flat" value="Add Resolution">
      </div>
      </form>
    </div>
  </div>
  <div class="input-field col s6 l3">
    <select id="project" name="project" onchange="sendData(this)" class="trigger">
      <?php 
      echo '<option value="" disabled';
      if ($clip['project'] == '') {
        echo ' selected';
      }
      echo '>Please Select</option>';
      $projects = $db->rawQuery('SELECT id, jobnumber, name, notes, rawlocation FROM projects');
      foreach ($projects as $project) {
        echo '<option value="'.$project['id'].'"';
        if ($project['id'] == $clip['project']){
          echo ' selected';
        }
        echo '>'.implode(' - ',array_filter(array($project['jobnumber'],$project['name']))).'</option>'; //array filter removes the blank items
        echo "\n";
      }
      ?>
    </select>
    <label>Project (raw location) | <a class="modal-trigger" href="#newProject" tabindex="-1">Add New</a></label>
    <div id="newProject" class="modal">
      <form autocomplete="off" method="post" action="?clip=<?php echo $clipid; ?>">
      <div class="modal-content row">
        <h4>Add a new project</h4>
        <div class="input-field col s6">
          <input id="jobnumber" name="jobnumber" type="text" class="validate" pattern="^(UK|NY)\d*(\.|-|_)?\d*$"
           style="text-transform:uppercase;" onkeypress="javascript:this.value=this.value.toUpperCase();">
          <label for="jobnumber" data-error="Prefix the number with UK or NY">Job Number (if applicable)</label>
        </div>
        <div class="input-field col s6">
          <select name="producer">
            <option value="" disabled selected>Please Select</option>
            <?php
            $producers = $db->rawQuery('SELECT id, firstname, lastname FROM users ORDER BY firstname ASC');
            foreach ($producers as $producer) {
              echo '<option value="'.$producer['id'].'">';
              echo $producer['firstname'].' '.$producer['lastname'];
              echo '</option>';
            }
            ?>
          </select>
          <label>Producer</label>
        </div>        
        <div class="input-field col s12">
          <input id="projectname" name="projectname" type="text" class="validate" required>
          <label for="projectname">Project Name</label>
        </div>
        <div class="input-field col s12">
          <input id="notes" name="notes" type="text" class="validate">
          <label for="notes">Notes (anything)</label>
        </div>
        <div class="input-field col s12">
          <input id="rawlocation" name="rawlocation" type="text" class="validate" required>
          <label for="rawlocation">Location of Raw Footage</label>
        </div>
      </div>
      <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-teal btn-flat ">Cancel</a>
        <input type="submit" class="modal-action btn-flat" value="Add Project">
      </div>
      </form>
    </div>    
  </div> 
  <div class="input-field col s6 l3">
    <select id="restrictedtoclient" name="restrictedtoclient" onchange="sendData(this)" class="trigger">
      <?php
      echo '<option value=""';
      if ($clip['restrictedtoclient'] === null) {
        echo ' selected';
      }
      echo '>None</option>';

      $restrictions = $db->rawQuery('SELECT id, name FROM clients');
         
      foreach ($restrictions as $res) {
        echo '<option value="'.$res['id'].'"';
        if ($res['id'] == $clip['restrictedtoclient']) {
          echo ' selected';
        }
        echo '>'.$res['name'].'</option>';
        echo "\n";
      }
      ?>
    </select>
    <label>
      Client Restrictions | <a class="modal-trigger" href="#newRestriction" tabindex="-1">Add New</a>
    </label>
    <div id="newRestriction" class="modal">
      <form autocomplete="off" method="post" action="?clip=<?php echo $clipid; ?>">
      <div class="modal-content row">
        <h4>Add a new client restriction</h4>
        <p>
          This is for frequent clients like Google, where clips that are obviously from a project 
          with said client may still be archived for use in future projects with them. 
          Please only add recurring clients to this list.
        <p>
        <div class="input-field col s6">
          <input id="client" name="client" type="text" class="validate" required>
          <label for="client">Restircted to:</label>
        </div>
      </div>
      <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-teal btn-flat ">Cancel</a>
        <input type="submit" class="modal-action btn-flat" value="Add Restriction">
      </div>
      </form>
    </div>
  </div>
  <div class="input-field col s6 l3">
    <select id="camera" name="camera" onchange="sendData(this)" class="trigger">
      <?php 
      echo '<option value="" disabled';
      if ($clip['camera'] == '') {
        echo ' selected';
      }
      echo '>Please Select</option>';
      $cameras = $db->rawQuery('SELECT id, model FROM opt_cameras');
      foreach ($cameras as $camera) {
        echo '<option value="'.$camera['id'].'"';
        if ($camera['id'] == $clip['camera']){
          echo ' selected';
        }
        echo '>'.$camera['model'].'</option>';
        echo "\n";
      }
      ?>
    </select>
  <label>Camera | <a class="modal-trigger" href="#newCamera" tabindex="-1">Add New</a></label>
    <div id="newCamera" class="modal">
      <form autocomplete="off" method="post" action="?clip=<?php echo $clipid; ?>">
      <div class="modal-content row">
        <h4>Add a new camera</h4>
        <div class="input-field col s6">
          <input id="model" name="model" type="text" class="validate" required>
          <label for="model">Camera Make and Model</label>
        </div>
      </div>
      <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-teal btn-flat ">Cancel</a>
        <input type="submit" class="modal-action btn-flat" value="Add Camera">
      </div>
      </form>
    </div>
  </div>
  <div class="input-field col s2 l1">
    <input autocomplete="off" value="<?php echo $clip['year']; ?>" id="year" name="year" type="number" class="trigger datecheck" onchange="sendData(this)" data-length="4" step="1" min="1800" max="2030">
    <label for="year">Year</label>
  </div>
  <div class="input-field col s2 l1">
    <input autocomplete="off" value="<?php echo $clip['month']; ?>" id="month" name="month" type="number" class="trigger datecheck" onchange="sendData(this)" data-length="2" step="1" min="1" max="12">
    <label for="month">Month</label>
  </div>
  <div class="input-field col s2 l1">
    <input autocomplete="off" value="<?php echo $clip['day']; ?>" id="day" name="day" type="number" class="trigger datecheck" onchange="sendData(this)" data-length="2" step="1" min="1" max="31">
    <label for="day">Day</label>
  </div>

  <div class="input-field col s3">
    <select id="country" name="country" onchange="sendData(this)" class="trigger">
      <?php 
      echo '<option value="" disabled';
      if ($clip['country'] == '') {
        echo ' selected';
      }
      echo '>Please Select</option>';
      $countries = $db->rawQuery('SELECT id, countryname FROM opt_countries');
      foreach ($countries as $country) {
        echo '<option value="'.$country['id'].'"';
        if ($country['id'] == $clip['country']){
          echo ' selected';
        }
        echo '>'.$country['countryname'].'</option>';
        echo "\n";
      }
      ?>
    </select>
    <label>Country | <a class="modal-trigger" href="#newCountry" tabindex="-1">Add New</a></label>
    <div id="newCountry" class="modal">
      <form autocomplete="off" method="post" action="?clip=<?php echo $clipid; ?>">
      <div class="modal-content row">
        <h4>Add a new country</h4>
        <div class="input-field col s6">
          <input id="countryname" name="countryname" type="text" class="validate" required>
          <label for="countryname">Country Name</label>
        </div>
      </div>
      <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-teal btn-flat ">Cancel</a>
        <input type="submit" class="modal-action btn-flat" value="Add Country">
      </div>
      </form>
    </div>
  </div>
  <div class="input-field col s3">
    <input autocomplete="off" value="<?php echo $clip['region']; ?>" id="region" name="region" type="text" class="trigger" onchange="sendData(this)">
    <label for="region">Region / State</label>
  </div>
  <div class="input-field col s3">
    <input autocomplete="off" value="<?php echo $clip['city']; ?>" id="city" name="city" type="text" class="trigger" onchange="sendData(this)">
    <label for="city">City</label>
  </div>
  <div class="input-field col s12">
    <input autofocus autocomplete="off" value="<?php echo $clip['description']; ?>" id="description" name="description" type="text" class="" onchange="sendData(this)">
    <label for="description">Description</label>
  </div>  
  <div class="input-field col s12">
    <div id="tags" class="chips chips-placeholder" data-clipid="<?php echo $clipid; ?>"></div>
    <!-- <label for="tags">Tags</label> -->
  </div>
  <div class="col s12" id="recenttags">Recent tags:<?php foreach ($_SESSION['recenttags'] as $tag) {
    // the tag string passed to addTagToField has to be encoded for both JS and HTML 
    // this does the JS formatting
    $jsTag = str_replace('\\', '\\\\', $tag);
    $jsTag = str_replace("'", "\'", $jsTag);

    echo '<a onclick="addTagToField(\'';
    echo htmlspecialchars($jsTag);
    echo '\');">';
    echo htmlspecialchars($tag);
    echo '</a>';
  } ?></div>
</div>
<div class="row">
  <p>&nbsp;</p>
  <div class="col s9" id="databox">
    <p>Original Filename: <?php echo substr($clip['uploadfilename'],0,strlen($clip['uploadfilename']) - 4); ?><br />
       Raw File Location: <span id="rawlocationSpan"><?php echo $clip['rawlocation']; ?></span></p>
  </div>
  <div class="col s3">
    <a id="publishButton" href="#" class="btn disabled truncate" onclick="publishClip();">Publish Clip</a>
  </div>
  <p>&nbsp;</p>
</div>