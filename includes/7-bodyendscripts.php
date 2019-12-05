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
    console.log(action, tag, 'to/from', tagclipid)
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

      $('.searchChips').chips({<?php
        if (isset($chipsexist) && $chipsexist) {
          $datastring = 'data: [';

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

            $datastring = $datastring.'{tag: "'.$keyword.'"},';
          }

          $datastring = substr($datastring, 0, -1); // trim that last comma
          $datastring = $datastring.'],';
          echo $datastring;
        }
      ?>
      // placeholder: 'Keywords',
      // secondaryPlaceholder: "+ Add'l Keywords"
      });

      // hack to add the label for Keywords search input.
      $('#search').append('<label for="searchInput">Keywords</label>');

      // when the class changes on the label, change it back if there are chips present
      function chipsPresentInSearch(){
        if ($('#search .chip').length > 0){
          return true;
        } else {
          return false;
        }
      }
      function updateLabel(){
        if (chipsPresentInSearch()){
          $('#search label').addClass('active');
        }
      }
      var targetNode = document.querySelector('#search label');
      var observerOptions = {
        childList: true,
        attributes: true,
        subtree: true //Omit or set to false to observe only changes to the parent node.
      }
      var observer = new MutationObserver(updateLabel);
      observer.observe(targetNode, observerOptions);  
      


      $('.searchstuff').animate({'opacity':1},300);

      // update the country dropdown if it was set
      <?php
        if (isset($_GET['country'])){
          echo 'document.getElementById("country").value = '.$_GET['country'].";\n";
        }
      ?>

      // initialize the dropdown boxes.
      $('select').formSelect();

    }

    // highlight the current page in the toolbar
    $('li#'+currentPage+"Link").addClass("active");

  });
</script>
