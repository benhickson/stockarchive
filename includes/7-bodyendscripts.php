<div class="responsive teal lighten-2" id="lessthan700">This page is too small. Minimum 700px wide, please.</div>
<div class="responsive teal lighten-2" id="notChrome">This runs on Chrome only.</div>
<iframe id="loginFrame" allowtransparency="true" style="position: fixed; z-index: 1000; width: 100%; height: 100%; border: none; display: none;">Error</iframe>
<script type="text/javascript">

  // hide for browsers other than chrome
  function isNotChrome(){
      // $('#notChrome').show();
      // $('header, main, footer').hide();
      console.log('is not chrome.');
    }
  var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
  if (!isChrome) isNotChrome();

  // login iframe
  function triggerLogin(){
    $('#loginFrame').attr('src','?iframe').fadeIn();
  }

  // change the class for thumbnail size
  function thumbnailSize(numOfCols){
    $("#mainContent").removeClass("size2 size3 size4 size5 size6").addClass("size" + numOfCols);
    if (lastClipExpandMove) {
      moveClipExpand(lastClipExpandMove);
    }
  }

  function hoverStart() {  
    this.play();
    this.nextElementSibling.classList.add('hovered');
  }

  function hoverEnd() {
    this.pause();
    this.nextElementSibling.classList.remove('hovered');
  }

  function savePrefs(){
    console.log(interfaceprefs);
    $.ajax({
      type: 'POST',
      data: {interfaceprefs: interfaceprefs},
      url: '/archive/me/prefsave.php',
      success: function(msg){
        console.log(msg);
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }  
    });
  }


  function tagUpdate(tagclipid, tag, action){
    console.log(action, tag, 'to/from', tagclipid);
    $.ajax({
      type: 'POST',
      url: '/archive/ajax/tags.php',
      data: {
        'action': action,
        'clipid': tagclipid,
        'tagtext': tag
      },
      success: function(msg){
        console.log(msg);
      }
    });
  }

  function addTag(tagclipid, tag){
    tagUpdate(tagclipid, tag, 'add');
  }

  function removeTag(tagclipid, tag){
    tagUpdate(tagclipid, tag, 'remove'); 
  }

  // add text as chip
  function addChip(chip){
    $('#chiptarget').append('<div class="chip">'+chip+'<i class="close material-icons">close</i></div>');
  }  

  // startup stuff
  $(document).ready(function(){
    // set some vars to be things on page
    var currentPage = window.location.pathname.split('/')[2];

    // page specific stuff
    if (currentPage == 'search'){

      // get the slider
      var slide = document.getElementById('thumbnailSizeSlider');

      // set the user's interfaceprefs
      thumbnailSize(interfaceprefs.thumbnailSize);
      slide.value = interfaceprefs.thumbnailSize;

      // assign an oninput to the thumbnail slider
      // .onchange will only fire after releasing
      slide.oninput = function() {
        thumbnailSize(this.value);
      };
      var timer1;
      slide.onchange = function() {
        clearTimeout(timer1);
        interfaceprefs.thumbnailSize = this.value;
        timer1 = setTimeout(savePrefs, 2000);
      }

      // trigger clipExpands
      $('.searchResult').click(function(){
        toggleClipExpand(this.dataset.clipid);
      });

      // videos play/pause on hover
      $(".hoverToPlay").hover( hoverStart, hoverEnd );



      <?php
      // load up the chips from the last search request

        if (isset($chipsexist) && $chipsexist) {
          $datastring = '[';

          $requestedkeywords = explode('|', realUrlGet()['s']);

          // TODO: do the str_replace before the explode
          foreach ($requestedkeywords as $keyword) {

            $keyword = urldecode($keyword);

            // replacing backslash with double backslash to double backslash to escape 
            // both php and javascript, can't just escape the once, have to escape both
            $keyword = str_replace('\\', '\\\\', $keyword);

            // replacing double quote with backslash double quote to escape 
            // both php and javascript
            $keyword = str_replace('"', '\"', $keyword);

            // echo it as a addChip() javascript command
            echo 'addChip("'.$keyword.'");'."\n";
          }
        }
      ?>

      // enter key adds text as chip
      $('#keywordEntry').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
          if (event.target.value == '') {
            if ($('#chiptarget .chip').length > 0){
              newSearch();  
            }
          } else {
            addChip(event.target.value);
            event.target.value = null;            
          }
        }
      });

      // fade in the search boxes
      $('.searchstuff').animate({'opacity':1},300);

      // update the country dropdown if it was set
      // disabled because country is disabled
      <?php
        // if (isset($_GET['country'])){
        //   echo 'document.getElementById("country").value = '.$_GET['country'].";\n";
        // }
      ?>

      // initialize the dropdown boxes.
      $('select').formSelect();

    }

    // highlight the current page in the toolbar
    $('li#'+currentPage+"Link").addClass("active");

  });
</script>
