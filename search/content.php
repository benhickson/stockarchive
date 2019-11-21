<style>
  .flex-parent{
    display: flex;
    flex-wrap: wrap;
    margin: -4px;  
  }
  .flex-child{
    flex-grow: 1;
    flex-shrink: 0; 
    margin: 1px;
  }
  /* Manually calculated by resizing browser window */
  .searchResult{
    position: relative;
    cursor: pointer;
  }
  .size2 .searchResult{
    flex-basis: 34%;
  }  
  .size3 .searchResult{
    flex-basis: 25%;
  }
  .size4 .searchResult{
    flex-basis: 20%;
  }
  .size5 .searchResult{
    flex-basis: 17%;
  }
  .size6 .searchResult{
    flex-basis: 15%;
  }
  .endSpacer{
    background: #a00;
    opacity: 0;
    pointer-events: none;
    cursor: none;
  }

  #clipExpand{
    flex-basis: 99%;
    overflow: hidden;
    transition: height 250ms ease;
    height: 0;
    text-align: center;
  }
  #clipExpandContent{
    display: inline-block;
    text-align: left;
    width: 100%;
  }
  #clipExpandContent video{
    width: 100%;
    position: relative;
    top: 50%;
    transform: translateY(-50%);
  }
  #clipExpandContent .panes{
    display: inline-block;
    vertical-align: top;
    height: 400px;
  }
  #clipExpandContent #leftpane{
    max-width: 700px;
    width: 60%;
  }
  #clipExpandContent #rightpane{
    width: calc(99% - 60%);
    padding-left: 10px;
    padding-top: 5px;
    min-width: calc(99% - 700px);    
  }  
  .hoverContent{
    background: white;
    font-size: 0.9rem;
    position: absolute;
    z-index: 1;
    top: 100%;
    bottom: 0;
    left: 0;
    right: 0;
    transition: bottom 250ms ease;
    overflow: hidden;
  }
  .hoverContent.hovered{
    bottom: -35px;
  }
  .searchResult.expanded .hoverContent.hovered{
    bottom: 0;
  }
  .hoverContent *{
    margin: 8px;
  }
  .hoverContent .description{
    width: 92%;
  }

  .searchResult.expanded{
    opacity: 0.8;
  }
  .expandedCover{
    background-image: url(../cssjs/close1.svg);
    background-size: 30%;
    /*background-color: white;*/
    /*opacity: 0.7;*/
    height: 0;
    width: 0;
    background-repeat: no-repeat;
    background-position: center;
    position: absolute;
    z-index: 1;
  }
  .searchResult.expanded .expandedCover + video.hoverToPlay{
    opacity: 0.3;
  }
  .searchResult.expanded .expandedCover{
    height: 100%;
    width: 100%;
  }
  #search{
    margin-top: 40px;
  }
  #search:not(.focus) input {
    width: 0px !important;
  }
  .searchstuff{
    opacity: 0;
    width: calc(100% - 14px);
  }
  #clipExpandFullQualityReveal{
    padding: 19px;
    white-space: nowrap;
    cursor: pointer;
  }
  #clipExpandFullQuality, #clipExpandFullQualityReveal{
    font-size: 80%;
  }
  .pagination li.leftbarhidden{
    display: none;
  }
  #afterResultContainer .pagination li.leftbarhidden{
    display: inline-block;
  }

  /*
  FOR PROJECT POPUP
  */
  .overlay {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    background-color: rgb(238, 238, 238);
    background-color: rgba(238, 238, 238, 0.9);
    overflow-x: hidden;
    transition: 0.35s;
  }

  .overlay-content {
    position: relative;
    width: 100%;
    text-align: left;
    margin-top: 120px;
    margin-left: 380px;
    margin-bottom: 250px;
    padding-right: 500px;
    overflow: scroll;
  }

  .overlay a:hover, .overlay a:focus {
    color: #f1f1f1;
  }

  .overlay .closebtn {
    position: absolute;
    top: 20px;
    right: 45px;
    font-size: 60px;
  }

  @media screen and (max-height: 450px) {
    .overlay a {font-size: 20px}
    .overlay .closebtn {
      font-size: 40px;
      top: 15px;
      right: 35px;
    }
  }

/*  .hoverFade{
    transition: opacity 300ms ease !important;
  }
  .searchResult:not(:hover) .hoverFade{
    opacity: 0.4;
  }*/
</style>
<script type="text/javascript">
  var lastClipExpandMove = false;
  var insertAfter = null;
  var clipExpandCurrentlyOpen = false;
  var monthNames = [null,'January','February','March','April','May','June','July','August','September','October','November','December']; // null for zero entry, as an offset.  
  function getElement(clipid){
    var theElement = $('.searchResult[data-clipid="'+clipid+'"]');
    return theElement;
  }
  function distanceToRightEdge(clipid){
    var theElement = getElement(clipid);
    return $(window).width() - (theElement.offset().left + theElement.width());
  }
  function clipAtEndOfRow(clipid){
    var thisClip = getElement(clipid);
      var currentDistance = distanceToRightEdge(clipid);
      var nextClipId = thisClip.next().data('clipid');
      var nextDistance = distanceToRightEdge(nextClipId);
      if (nextDistance > currentDistance){
        // then this is the correct clip 
        return thisClip;
      } else {
        // run again, with the next clip
        return clipAtEndOfRow(nextClipId);
      }
  }
  function moveClipExpand(clipid){
    lastClipExpandMove = clipid;
    // detach it and save in a variable
    var clipExpand = $('#clipExpand').detach();
    // re-attach it after the clip at end of row of the clipid
    clipAtEndOfRow(clipid).after(clipExpand);
  }
  function clipExpandHeight(height){
    window.setTimeout(function(){
      document.getElementById('clipExpand').style.height = height;
    }, 100);
  }
  function setActiveSearchResult(clipid){
    $('.searchResult').removeClass('expanded');
    getElement(clipid).addClass('expanded'); 
  }
  function clipExpandOpen(clipid){
    clipExpandHeight('400px');
    clipExpandCurrentlyOpen = true;
    setActiveSearchResult(clipid);
  }
  function clipExpandClose(){
    clipExpandHeight('0px');
    clipExpandCurrentlyOpen = false;
  }
  function continueUpdate(clipid, ajaxresponse){
    // organize response
    var responseObject = JSON.parse(ajaxresponse);
    // check if successful
    if (responseObject.success){
      // if successful,
      // drill in one level
      var responseData = responseObject.data;
      // log it
      console.log(responseData);

      // update the text fields
      $('#clipExpandId').text(responseData.id);
      $('#clipExpandDescription').text(responseData.description);
      var locationString = '';
      if (responseData.country) { locationString += responseData.country; }
      if (responseData.region) { locationString += ' > ' + responseData.region; }
      if (responseData.city) { locationString += ' > ' + responseData.city; }
      $('#clipExpandLocation').text(locationString);
      var dateString = '';
      if (responseData.month) { dateString += monthNames[responseData.month] + ' '; }
      if (responseData.day) { dateString += responseData.day + ', '; }
      if (responseData.year) { dateString += responseData.year; }
      $('#clipExpandDate').text(dateString);
      $('#clipExpandProject').text(responseData.project);
      $('#clipExpandCamera').text(responseData.camera);
      $('#clipExpandResolution').text(responseData.resolution);
      $('#clipExpandOriginalFilename').text(responseData.originalfilename.substring(0, responseData.originalfilename.length-4));
      
      // hide the section of full quality stuff
      $('#clipExpandFullQuality').hide();

      // from search result
      $('#clipScore').text($('.searchResult[data-clipid='+clipid+']').data('score'));

      // tags, temp. needs to be a proper chip field
      var tagstemp = '';
      $(JSON.parse(responseData.tags)).each(function(){
        tagstemp += this.tagname + ', ';
      });
      tagstemp = tagstemp.replace(/,\s*$/, "");
      $('#clipExpandTags').text(tagstemp);

      // update download link and full quality link
      $('#clipExpandDownloadUrl').attr('href','//creative.lonelyleap.com/archive/media/?clip='+clipid+'&q=f&download');
      $('#clipExpandRawFootageUrl').attr('href',responseData.rawfootageurl);

      <?php
      if ($_SESSION['userid'] == 1){
      ?>

      // updated retranscode id
      $('#clipExpandRetranscode').attr('href','../upload/transcode.php?retranscode='+clipid);

      <?php
      }
      ?>

      // update video src and button actions
      $('#clipExpandContent video').attr('src','//creative.lonelyleap.com/archive/media/?clip='+clipid+'&q=h').on('loadedmetadata', function(){
        $('.panes').animate({'opacity':1},300);
        $(this).off('loadedmetadata');
      });

    } else {
      console.log('responseObject is invalid');
      // if not successful
      // check if needs to login
      if (responseObject.data == 'triggerLogin'){
        triggerLogin();
      }
    }

  }
  function getClipData(clipid){
    var data = new Object();
    data.clipid = clipid;
    data.fields = '["description","project","tags","date","resolution","camera","location","originalfilename","rawfootageurl"]';
    $.ajax('../ajax/clipdata.php',{
      type: 'POST',
      data: data,
      success: function(response){
        continueUpdate(clipid, response);
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }      
    });
  }
  function updateClipExpandContent(clipid){
    $('.panes').css('opacity',0);
    getClipData(clipid);
  }
  function toggleClipExpand(clipid){
    if (!clipExpandCurrentlyOpen) {
      // clipExpand not open, user is opening
      console.log('user opening first clipExpand at', clipid);
      moveClipExpand(clipid);
      updateClipExpandContent(clipid);
      clipExpandOpen(clipid);

    } else if (lastClipExpandMove == clipid && clipExpandCurrentlyOpen) {
      // user is closing current clipExpand
      console.log('user closing clipExpand at', clipid);
      clipExpandClose();
      $('.searchResult').removeClass('expanded');

    } else {
      // user is moving clipExpand to new location
      console.log('moving from', lastClipExpandMove, 'to', clipid);

      clipExpandClose();
      moveClipExpand(clipid);
      updateClipExpandContent(clipid);
      clipExpandOpen(clipid);

    }
  }
  function newSearch(projectId = null){
    var windowLocationString = '';
    var clipIdSearchString = document.getElementById('clipIdSearch').value;
    if (clipIdSearchString == Number.parseInt(clipIdSearchString)) {
      windowLocationString = '?clip='+clipIdSearchString;
    } else {
      var searchString = '';  

      $('#search .chip').each(function(){
        searchString += encodeURIComponent($(this).clone().children().remove().end().text()) + '|';
      });

      // slice off the last "|"
      searchString = searchString.slice(0, -1);
      windowLocationString = '?s='+searchString;
      
      var countryvalue = document.getElementById("country").value;
      if (countryvalue > 0){
        windowLocationString = windowLocationString+'&country='+countryvalue;
      }
      
      var projectvalue = projectId || <?php echo isset($_GET['project']) && $_GET['project'].length > 0 ? $_GET['project'] : -1; ?>;
      if (projectvalue > 0) {
        windowLocationString = windowLocationString+'&project='+projectvalue;
      } 
    }

    window.location = windowLocationString;
  }

  function togglePopup() {
    if($('#projectPopup').style.width === "100%") {
      closeProjectPopup();
    }
    else {
      openPopup();
    }
  }

  function openPopup() {
    document.getElementById("projectPopup").style.width = "100%";

    if(document.getElementById("overlay-content-id").getAttribute('data-set') === 'false') {
      setProjectPopup();
    }
  }

  function closeProjectPopup() {
    document.getElementById("projectPopup").style.width = "0%";
  }

  function setProjectPopup() {
    $.ajax('../ajax/projects.php?html', {
      type: 'GET',
      success: function(res) {
        $('#overlay-content-id').prepend(res);
        $('#overlay-content-id').attr('data-set', 'true');

        $('body').on('keydown', function(e) {
          var key = e.key;
          if (key === "Escape") {
            closeProjectPopup();
          }
        });

        $('#bottomleftbar').click(function(e) {
          closeProjectPopup();
        });

        var options = {
            valueNames: ['name', 'year']
        };

        var projectList = new List('project-list', options);

        $('#project').on('input', function() {
          if (this.value.length >= 0) {
            var search = this.value;

            // the filter on the list is totaly cleared by returning true on all
            // items (you have to clear the later filter with another filter
            // search doesn't work), then an initial search is done that is used
            // to get all the projects that make the search so that can be the
            // basis of what years dividers to leave in the later filter
            projectList.filter(function(item) { return true });
            var nameList = projectList.search(this.value);
            projectList.search();

            // a list of years is generated so we can tell what dividers should stay
            // and to make the list only contain distinct years, the array is converted
            // to a Set (a data type that only holds distint values)
            let uniqueYears = new Set(nameList.map(item => {
              return item['_values']['year'];
            }));

            projectList.filter(function(item) {
              var name = item['_values']['name'];
              var year = item['_values']['year'];

              if(name === 'All Projects') {
                return true;
              }

              if(!uniqueYears.has(year)) {
                return false;
              }

              if(name === 'divider'
              || name.toLowerCase().search(search) >= 0
              || year === search) {
                return true;
              }

              return false;
            });
          }
        });
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }    
    });
  }

</script>

<?php
  function realUrlGet() {
    $s = array();

    // gets the raw query (with encoded special characters) and
    // replaces the % with a # to stop parse_str from decoding
    // the url; that way the unencoded pipes are not confounded
    // with pipes in the search terms
    $obscuredQuery = str_replace('%', '#', $_SERVER['QUERY_STRING']);

    // the query is parsed into an array, but not decoded
    $encodedParams = array();
    parse_str($obscuredQuery, $encodedParams); 

    // the # are replaced back to %, so that the query is
    // properly encoded except for the unencoded
    // pipe to seperate the keywords
    $encodedParams['s'] = str_replace('#', '%', $encodedParams['s']);
  
    return $encodedParams;
  }


  $page = 1;  // default
  $cols = array('c.id', 'c.description', 'c.project'); // columns/fields to request

  $db->where('published', 1); // published clips
  $db->where('(todelete = 0 OR todelete IS NULL)'); // not deleted clips

  if (isset($_GET['clip']) && $_GET['clip'].length > 0) {
    $table = 'clips c'; // table to search
    $db->where('c.id = '.$_GET['clip']);
    $cols[] = 'NULL AS score';
  } else {
    if (isset($_GET['country']) && $_GET['country'].length > 0) {
      $db->where('c.country = '.$_GET['country']);
    }

    if (isset($_GET['project']) && $_GET['project'].length > 0) {
      $db->where('c.project = '.$_GET['project']);
    }

    if (isset($_GET['page']) && $_GET['page'].length > 0) {
      $page = $_GET['page'];
    }

    $chipsexist = false; // default, if no chips set
    if (isset($_GET['s']) && $_GET['s'] != '') {
      $chipsexist = true; // setting a flag to use on the bodyendscripts.php page

      $requestedkeywords = explode('|', realUrlGet()['s']);

      $search = '';
      foreach($requestedkeywords as $rk) {
        // escape both regex and sql characters, do not use MysqliDb 
        // array replace functionality, since that will double escape 
        // and mess up the proper escapping done here
        $rk = urldecode($rk);
        $rk = preg_quote($rk);
        $rk = $db->escape($rk);

        $search = $search.'|'.$rk;
      }
      $search = substr($search, 1);

      $table = 'clips c, clips_x_tags cxt, tags t, projects p'; // table to search
      // $cols[] = "
      // SUM(
      //   CASE WHEN c.description REGEXP '$pipe' THEN 1 ELSE 0 END
      //   +
      //   CASE WHEN t.tagname REGEXP '$pipe' THEN 2 ELSE 0 END
      //   +
      //   CASE WHEN t.tagname = '$query' THEN 3 ELSE 0 END
      //   +
      //   CASE WHEN c.description = '$query' THEN 4 ELSE 0 END
      // ) AS score
      // ";

      // breaks when tags contain a double quote
      $cols[] = "
        SUM(
          CASE WHEN c.description REGEXP '$search' THEN 1 ELSE 0 END
          +
          CASE WHEN t.tagname REGEXP '$search' THEN 2 ELSE 0 END
        ) AS score
      ";

      $db->where('c.id = cxt.clipid');
      $db->where('cxt.tagid = t.id');
      $db->where('c.project = p.id');
      $db->where(
        "(
          c.description REGEXP '$search'
          OR t.tagname REGEXP '$search' 
          OR c.city REGEXP '^$search$' 
          OR c.region REGEXP '^$search$'
          OR p.name REGEXP '$search'
        )");
      $db->groupBy('c.id');
      $db->orderBy('score','desc');
    } else {
      $table = 'clips c'; // table to search
      $db->orderBy('project','desc');
      $db->orderBy('uploadfilename','asc');
      $cols[] = 'NULL AS score';
    }
  }

  $pageLimit = 60; // results per page
  $db->pageLimit = $pageLimit;
  $results = $db->withTotalCount()->paginate($table, $page, $cols);
  
  // console_log($db->getLastQuery());
  // console_log($results);
?> 
<div class="col m4 l3 xl2 grey lighten-2">
  <div id="leftbar">
    <p>Found <?php echo $db->totalCount; ?> results.<br />Viewing results <?php echo (($page - 1) * $pageLimit) + 1; ?> through <?php echo min($db->totalCount, $pageLimit * $page); ?></p>
    <ul class="pagination">
    <?php
    $get = realUrlGet();
    if (isset($get['page'])) {
      unset($get['page']);
    }

    $searchterms = '';
    foreach ($get as $param => $value) {
      $searchterms = $searchterms.$param.'='.$value.'&';
    }

    $pagecount = ceil($db->totalCount / $pageLimit);
    $i = 1;
    while ($i <= $pagecount){
      $paginationstring .= '<li';

      if ($page == $i) {
        $paginationstring .= ' class="active"';
        // if statement to show only the five current pages
      } elseif (abs($page - $i) > 2) {
        $paginationstring .= ' class="leftbarhidden"';
      }

      $paginationstring .= '><a href="?';
      $paginationstring .= $searchterms;
      $paginationstring .= 'page='.$i;
      $paginationstring .= '">'.$i.'</a></li>';
      $paginationstring .= "\n";
      $i++;
    }
    echo $paginationstring;
    ?>
    </ul>
    <div class="input-field searchstuff">
      <div id="search" placeholder="Keywords" class="searchChips chips chips-placeholder"></div>
      <!-- <label for="search">Keywords</label> -->
    </div>
    <div class="input-field searchstuff">
      <input id="clipIdSearch" type="text" class="validate" pattern="\d+">
      <label for="clipIdSearch">Clip Number</label>
    </div>
    <div class="input-field searchstuff">
      <select id="country">
        <option value="0" selected>All Countries</option>
        <?php
        $cols = array("id, countryname");
        $db->orderBy("countryname","asc");
        $countries = $db->get("opt_countries", null, $cols);
        foreach ($countries as $country) {
          echo '<option value="'.$country['id'].'">'.$country['countryname'].'</option>'."\n";
        }
        ?>
      </select>
    </div>
    <div class="input-field searchstuff" onclick="openPopup();">
      <input id="project" type="text" class="" value="<?php
        if(isset($_GET['project']) && $_GET['project'].length > 0) {
          $cols = array("name");
          $db->where("id = ?", array($_GET['project']));
          echo $db->get("projects", null, $cols)[0]['name'];
        }
      ?>">
      <label for="clipIdSearch">Project</label>
    </div>
    <button id="searchButton" type="submit" class="btn searchstuff" onclick="newSearch();">Search</button>
    <!-- <p>Included Tags</p> -->
    <!-- <p>Suggested Tags</p> -->
    <!-- <p>Collections</p> -->
    <div id="bottomleftbar">
      <br />
    </div>      
  </div>
</div>
<div id="mainContent" class="col m8 l9 xl10 size<?php echo $_SESSION['interfaceprefs']['thumbnailSize']; ?>">
  <div class="row">
    <form class="sliderForm" action="#">
      <p class="range-field">
        <div>Thumbnail Size:</div>
        <input type="range" id="thumbnailSizeSlider" min="2" max="6" style="direction:rtl;" value="<?php echo $_SESSION['interfaceprefs']['thumbnailSize']; ?>" />
      </p>
    </form>
  </div>
  <div id="resultContainer" class="row hidden flex-parent">
    <div id="projectPopup" class="overlay">
      <a href="javascript:void(0)" class="closebtn" onclick="closeProjectPopup()">&times;</a>
      <div id="overlay-content-id" class="overlay-content" data-set="false">
      </div>
    </div>
    <?php
    foreach ($results as $clip) {
      ?>
      <div class="searchResult flex-child" data-clipid="<?php echo $clip['id']; ?>" data-score="<?php echo $clip['score']; ?>">
        <div class="expandedCover"></div>
        <video loading="eager" class="hoverToPlay" muted loop preload="none" 
        src="//creative.lonelyleap.com/archive/media/?clip=<?php echo $clip['id']; ?>&q=q"
        poster="//creative.lonelyleap.com/archive/media/?clip=<?php echo $clip['id']; ?>&q=t">
        </video>
        <div class="hoverContent">
          <span class="description truncate"><?php echo $clip['description']; ?></span>
        </div>
      </div>
      <?php
    }
    ?>
      <!-- 5 spacers to fill the last row up to 6, when necessary -->
      <div class="endSpacer searchResult flex-child" data-clipid="0a"></div>
      <div class="endSpacer searchResult flex-child" data-clipid="0b"></div>
      <div class="endSpacer searchResult flex-child" data-clipid="0c"></div>
      <div class="endSpacer searchResult flex-child" data-clipid="0d"></div>
      <div class="endSpacer searchResult flex-child" data-clipid="0e"></div>

      <script type="text/javascript">
        function playPause(){
          var player = document.getElementById('clipExpandVideo');
          if (player.paused) {
            player.play();
          } else {
            player.pause();
          }
        }
      </script>

      <!-- The div for the expanded content -->
      <div id="clipExpand" class="flex-child">
        <div id="clipExpandContent">
          <div class="panes" id="leftpane">
            <video id="clipExpandVideo" src="//creative.lonelyleap.com/archive/media/?clip=134&q=h" muted controls controlsList="nodownload nofullscreen" autoplay loop onclick="playPause();"></video>
          </div>
          <div class="panes" id="rightpane">
            <h5 id="clipExpandDescription">Description</h5>
            <p>Clip # <span id="clipExpandId">Clip Id</span> <?php 
              if($_SESSION['userid'] == 1){
                  echo '<a id="clipExpandRetranscode" href="../upload/transcode.php?retranscode=clipid" target="_blank">RT</a>';
              }
            ?><br />
               Tags: <span id="clipExpandTags">Tags</span></p>
            <p>Date: <span id="clipExpandDate">Date</span><br />
               Project: <span id="clipExpandProject">Project</span><br />
               Location: <span id="clipExpandLocation">Location</span></p>
            <p>Camera: <span id="clipExpandCamera">Camera</span><br />
               Resolution: <span id="clipExpandResolution">Resolution</span></p>
            <p style="display: none;">Search Relevancy Score: <span id="clipScore"></span></p>
            <a class="btn waves-effect waves-light" id="clipExpandDownloadUrl" href="#"><i class="material-icons left">cloud_download</i>Download Proxy Clip</a>
            <a id="clipExpandFullQualityReveal" onclick="console.log('clicked');$('#clipExpandFullQuality').hide(1,function(){$('#clipExpandFullQuality').show(400);console.log('shown');});">Download Full Quality</a>
            <p id="clipExpandFullQuality">Raw Footage Folder: <a id="clipExpandRawFootageUrl" target="_blank" href="#">link</a><br />
              Filename: <span id="clipExpandOriginalFilename"></span></p>
          </div>
        </div>
      </div>      
  </div>
  <div class="row" id="afterResultContainer">
    <ul class="pagination">
      <?php 
        echo $paginationstring; 
      ?>
    </ul>
  </div>
</div>    
