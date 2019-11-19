<?php

require __DIR__.'/../includes/0-base.php';

if (isset($_SESSION['logged_in'])){
	$cols = array("id, name, jobnumber, datedelivered");
  $db->orderBy("datedelivered","desc");
  $db->orderBy("name","asc");

  $projects = $db->get("projects", null, $cols); // multilog('projects', $projects);

  if(!isset($_GET['html'])) {
    ?>
      <p>Hi</p>
    <?php

    // echo json_encode($projects);
  } else {
    $searchAll = array(id => -1, name => "All Projects", jobnumber => "", datedelivered => "");
    array_unshift($projects, $searchAll);
  
    $buildCard = function($id, $jobnumber, $name) {
      $jobnumber = $jobnumber ? $jobnumber.' - ' : ''; 
  
      $projectIdStr = 'projectId='.$id; // $id > 0 ? 'projectId='.$id : '';
  
      $str = "<div class=\"col\" id=\"project-card-id\">
          <div class=\"card blue-grey darken-1\">
            <div class=\"card-content white-text\" style=\"padding: 8px 0px 0px 0px;\">
              <span class=\"card-title\" style=\"text-align: center; padding: 0px 8px 0px 8px;\">
                $jobnumber $name
              </span>
              <div class=\"card-action\">
                <a class=\"waves-effect waves-light btn-small\" onClick=\"newSearch($projectIdStr)\">Filter by project</a>
                <a class=\"waves-effect waves-light btn-small\" href=\"?$projectIdStr\">See all</a>
              </div>
            </div>
          </div>
        </div>";
  
      return $str;
    };
  
    $overlayHtml = '<div class="row">';
    $rowYear = '';
    foreach($projects as $p) {
      $card = $buildCard($p['id'], $p['jobnumber'], $p['name']);
  
      $cardYear = substr($p['datedelivered'], 0, 4);
      if($rowYear !== $cardYear) { // multilog('rowYear', $rowYear);
        $rowYear = $cardYear;
  
        $overlayHtml = $overlayHtml .  
          "</div>
          <div class=\"row\">
            <p>
              $rowYear
            </p>";
      }
  
      $str = $card;
  
      $overlayHtml = $overlayHtml . $str;
    }

    echo $overlayHtml;
  }
} else {
	$success = false;
	$message = 'You\'re not logged in. This may be due to inactivity. Click any link to log in again.';
	$data = 'triggerLogin';
	echo json_encode(compact('success','message','data'));
}