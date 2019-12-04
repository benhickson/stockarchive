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
    $buildCard = function($id, $jobnumber, $name, $year, $filterText = 'Filter Search Results by Project', $allText = 'View All Clips in Project') {
      $jobnumber = $jobnumber ? $jobnumber.' - ' : '';

      $projectUrl = $id > 0 ? "?project=$id" : '../';
      
      ?>
        <li>
          <div class="col">
            <div class="projectcard card-panel">
              <div class="name"><?php echo $jobnumber.$name ?></div>
              <div hidden class="year"><?php echo $year ?></div>
              <div class="buttonbox">
                <a class="waves-effect waves-light btn-small tooltipped" data-position="bottom" data-tooltip="<?php echo $allText; ?>" href="<?php echo $projectUrl; ?>"><i class="material-icons">burst_mode</i></a>
                <a class="waves-effect waves-light btn-small tooltipped" data-position="bottom" data-tooltip="<?php echo $filterText; ?>" onClick="newSearch(projectId=<?php echo $id; ?>)"><i class="material-icons">image_search</i></a>
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

    $buildCard(-1, '', 'All Projects', '', 'Show Search Results from All Projects', 'Show All Clips');

    $rowYear = '';
    foreach($projects as $p) {
      $cardYear = substr($p['datedelivered'], 0, 4);
      if($rowYear !== $cardYear) {
        $rowYear = $cardYear;
        
        ?>
          <li>
            <div class="col s12">
              <div class="yearcard card-panel">
                <div hidden class="name">divider</div>
                <div class="year"><?php echo $rowYear ?></div>
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
      <script type="text/javascript">
        // Initialize the tooltips
        $(document).ready(function(){
          $('.tooltipped').tooltip({
            enterDelay: 250     // Delay after user hovers before tooltip shows
          });
        });
      </script>
    <?php
  }
} else {
	$success = false;
	$message = 'You\'re not logged in. This may be due to inactivity. Click any link to log in again.';
	$data = 'triggerLogin';
	echo json_encode(compact('success','message','data'));
}
