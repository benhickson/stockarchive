<?php

require __DIR__.'/../includes/0-base.php';

if (isset($_SESSION['logged_in'])){
  $cols = array("id, name, jobnumber, datedelivered");
  $db->orderBy("datedelivered","desc");
  $db->orderBy("name","asc");

  $projects = $db->get("projects", null, $cols); // multilog('projects', $projects);

  if(!isset($_GET['html'])) {
    echo json_encode($projects);
  } else {
    $buildCard = function($id, $jobnumber, $name, $filterText = 'Search in project', $allText = 'Project clips') {
      $jobnumber = $jobnumber ? $jobnumber.' - ' : '';

      $projectUrl = $id > 0 ? "?project=$id" : '../';

      ?>
        <div class="col" id="project-card-id">
          <div class="card blue-grey darken-1">
            <div class="card-content white-text" style="padding: 8px 0px 0px 0px;">
              <span class="card-title" style="text-align: center; padding: 0px 8px 0px 8px;">
                <?php echo $jobnumber.$name ?>
              </span>
              <div class="card-action">
                <a class="waves-effect waves-light btn-small" href="<?php echo $projectUrl; ?>"><?php echo $allText; ?></a>                
                <a class="waves-effect waves-light btn-small" onClick="newSearch(projectId=<?php echo $id; ?>)"><?php echo $filterText; ?></a>
              </div>
            </div>
          </div>
        </div>
      <?php
    };

    echo '<div class="row">'; 

    $buildCard(-1, '', 'All Projects', 'Search through all projects', 'All clips');

    $rowYear = '';
    foreach($projects as $p) {
      $cardYear = substr($p['datedelivered'], 0, 4);
      if($rowYear !== $cardYear) { // multilog('rowYear', $rowYear);
        $rowYear = $cardYear;
  
        echo "</div> <div class=\"row\"> <p>$rowYear</p>";
      }

      $card = $buildCard($p['id'], $p['jobnumber'], $p['name']);
    }

    echo '</div>';
  }
} else {
	$success = false;
	$message = 'You\'re not logged in. This may be due to inactivity. Click any link to log in again.';
	$data = 'triggerLogin';
	echo json_encode(compact('success','message','data'));
}
