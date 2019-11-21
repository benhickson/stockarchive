<?php

require __DIR__.'/../includes/0-base.php';

if (isset($_SESSION['logged_in'])){
  $cols = array("id, name, jobnumber, datedelivered");
  $db->orderBy("datedelivered","desc");
  $db->orderBy("name","asc");

  $projects = $db->get("projects", null, $cols);

  if(!isset($_GET['html'])) {
    echo json_encode($projects);
  } else {
    $buildCard = function($id, $jobnumber, $name, $year, $filterText = 'Search in project', $allText = 'Project clips') {
      $jobnumber = $jobnumber ? $jobnumber.' - ' : '';

      $projectUrl = $id > 0 ? "?project=$id" : '../';
      
      ?>
        <li>
          <div class="col" id="project-card-id">
            <div class="card blue-grey darken-1">
              <div class="card-content white-text" style="padding: 8px 0px 0px 0px;">
                <span class="card-title" style="text-align: center; padding: 0px 8px 0px 8px;">
                  <div class="name"><?php echo $jobnumber.$name ?></div>
                  <div hidden class="year"><?php echo $year ?></div>
                </span>
                <div class="card-action">
                  <a class="waves-effect waves-light btn-small" href="<?php echo $projectUrl; ?>"><?php echo $allText; ?></a>                
                  <a class="waves-effect waves-light btn-small" onClick="newSearch(projectId=<?php echo $id; ?>)"><?php echo $filterText; ?></a>
                </div>
              </div>
            </div>
          </div>
        </li>
      <?php
    };

    ?>
      <div id="project-list">
        <ul class="row list">
    <?php

    $buildCard(-1, '', 'All Projects', '', 'Search through all projects', 'All clips');

    $rowYear = '';
    foreach($projects as $p) {
      $cardYear = substr($p['datedelivered'], 0, 4);
      if($rowYear !== $cardYear) {
        $rowYear = $cardYear;
        
        ?>
          <li>
            <div class="col m12" id="project-card-id">
              <div class="card blue-grey darken-1">
                <div class="card-content white-text" style="padding: 8px 0px 0px 0px;">
                  <span class="card-title" style="text-align: center; padding: 0px 8px 0px 8px;">
                    <div hidden class="name">divider</div>
                    <div class="year"><?php echo $rowYear ?></div>
                  </span>
                </div>
              </div>
            </div>
          </li>
        <?php
      }

      $card = $buildCard($p['id'], $p['jobnumber'], $p['name'], $cardYear);
    }

    ?>
        </ul> 
      </div>
    <?php
  }
} else {
	$success = false;
	$message = 'You\'re not logged in. This may be due to inactivity. Click any link to log in again.';
	$data = 'triggerLogin';
	echo json_encode(compact('success','message','data'));
}
