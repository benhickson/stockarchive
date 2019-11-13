
  <h3>New Upload(s)</h3>
  <p class="limit">Start by uploading a file. This should be a 1080p low-bitrate H.264. You can use the media encoder preset <a href="/archive/cssjs/epr/1080p-3_3.5-Archive_v2.epr.zip">here</a>. It can be any framerate, any frame width, but the frame height should be 1080. Do not letterbox, allow it to be wider. I recommend VBR 2-pass with a target of 2Mbps and a max of 2.5Mbps.</p>
  <p>Upload multiple files if you like.</p>
  <p>You'll add the details on the next page.</p>
  <form action="#">
    <div class="file-field input-field">
      <div id="targetToDisable" class="btn">
        <span id="buttonAndDropzone">Click Here</span>
        <input type="file" id="files" name="files[]" multiple onchange="handleFiles(this.files)" accept="video/mp4" />
      </div>
      <div class="file-path-wrapper">
        <input class="file-path validate" type="text" placeholder="Upload one or more files">
      </div>
    </div>
  </form>
  <table class=" bordered" id="fileList"></table>


<script type="text/javascript"> // uploading mechanisms
    var originalDropzoneText = $("#buttonAndDropzone").text();
    window.URL = window.URL || window.webkitURL;

    var errorClass = 'red lighten-3';

    var theUpload = new Array();

    var fileCount = 0;
    var uploadCount = 0;

    function countThenRedirect(){
      uploadCount++;
      console.log(fileCount,uploadCount);
      if (fileCount == uploadCount){
        setTimeout(function(){
          window.location = '?clip=first';
        }, 1000);
      }
    }

    function doTheUpload(file, item){
        file.name = item.originalFilename;
        // console.log(file, item);
        var formdata = new FormData();
        formdata.append('file',file);
        formdata.append('duration',item.seconds);
        var ajax = new XMLHttpRequest();
        // https://stackoverflow.com/questions/23389410/passing-parameter-to-xmlhttprequest-eventlistener-function
        // strange order of variables with this ".bind()" method.
        ajax.upload.addEventListener('progress', progressHandler.bind(null, item.progressBar), false);
        ajax.addEventListener('load', completeHandler, false);
        ajax.addEventListener('error', errorHandler, false);
        ajax.addEventListener('abort', abortHandler, false);
        ajax.open('POST', 'upload.php');
        ajax.send(formdata);
    }

    function progressHandler(bar, event){
        changeProgressBarValue(bar, (event.loaded / event.total))
    }
    function completeHandler(event){
        // after upload is complete, start the transcoding queue
        $.ajax({ url: 'transcode.php', success: countThenRedirect });

    }
    function errorHandler(event){
        console.log('Upload Failed');
    }
    function abortHandler(event){
        console.log('Upload Aborted');
    }

    function objectURLtoFileThenUpload(item){ // this is how you reconstruct the file object
        var myObjectURL = item.DOM.src;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', myObjectURL, true);
        xhr.responseType = 'blob';
        xhr.onload = function() {
            var myFile = new File([this.response], item.originalFilename, {
                type: item.fileType, 
                lastModified: item.lastModified
            });
            doTheUpload(myFile, item);
        };
        xhr.send();
    }

    function uploadLoop(){ // the main upload loop, parsing Blobs and uploading
        console.log(theUpload);
        fileCount = theUpload.length;
        theUpload.forEach(function(item, index){
            console.log("item # "+(index+1));
            objectURLtoFileThenUpload(item);
        });
    }

    function validateFiles(){
      // new upload set, clear the old stuff
      theUpload = new Array();
      // counter variables, so something can run at the end
      var countOfVids = $('video').length;
      var count = 0;
      // the loop
      $('video').each(function(){
        var thisVid = new Object();
        // preset it to be "true"
        thisVid.passed = true;
        thisVid.round = function(number, precision) {
            var factor = Math.pow(10, precision);
            var tempNumber = number * factor;
            var roundedTempNumber = Math.round(tempNumber);
            return roundedTempNumber / factor;
        };      
        thisVid.DOM = $(this).get(0);
        thisVid.bytesize = thisVid.DOM.dataset.bytesize;
        thisVid.seconds = thisVid.DOM.duration;
        thisVid.MB = thisVid.bytesize / 1000000;
        thisVid.Mbps = thisVid.MB * 8 / thisVid.seconds;
        thisVid.width = thisVid.DOM.videoWidth;
        thisVid.height = thisVid.DOM.videoHeight;
        thisVid.originalFilename = thisVid.DOM.dataset.originalFilename;
        // rounding for display
        thisVid.MB = thisVid.round(thisVid.MB, 0);
        thisVid.seconds = thisVid.round(thisVid.seconds, 0);      
        thisVid.Mbps = thisVid.round(thisVid.Mbps, 1);
        // duration splits
        thisVid.minutes = Math.floor(thisVid.seconds / 60);
        thisVid.seconds = thisVid.seconds - (thisVid.minutes * 60);
        if (thisVid.minutes > 0){
          thisVid.durationPrint = thisVid.minutes + "m " + thisVid.seconds + "s";
        } else {
          thisVid.durationPrint = thisVid.seconds + "s";
        }
        // entering text
        thisVid.metadataBox = $(this).parent().siblings(".metadataBox"); // collection
        thisVid.metadataBox.children(".originalFilename").text(thisVid.originalFilename);
        thisVid.metadataBox.children(".MB").text(thisVid.MB + " MB");
        thisVid.metadataBox.children(".duration").text(thisVid.durationPrint);
        thisVid.metadataBox.children(".Mbps").text(thisVid.Mbps + " Mbps");
        if (thisVid.Mbps > 4.5) {
          thisVid.metadataBox.children(".Mbps").addClass(errorClass);
          thisVid.passed = false;
        }
        thisVid.metadataBox.children(".framesize").text(thisVid.width + "x" + thisVid.height);
        if (thisVid.height < 1080 || thisVid.width < 1920) {
          thisVid.metadataBox.children(".framesize").addClass(errorClass);
          thisVid.passed = false;
        }
        // if it passed, add any extra data and add it to the upload array.
        if (thisVid.passed) {
            thisVid.lastModified = thisVid.DOM.dataset.lastModified;
            thisVid.fileType = thisVid.DOM.dataset.fileType;
            thisVid.progressBar = thisVid.DOM.dataset.progressBar;     
            theUpload.push(thisVid);
        } else {
            console.log(thisVid.originalFilename, 'failed');
            vidUploadFailed(thisVid.originalFilename);
        }
        count++;
        if (count == countOfVids) {
          uploadLoop();
        }
      }); 
    }

    function vidUploadFailed(uploadfilename){
      $.ajax('failed_uploads.php?fail',{
        type: 'POST',
        data: {
          'uploadfilename': uploadfilename
        }
      });
    }

    function handleFiles(files) {
      if (!files.length) {
        fileList.innerHTML = "<p>No files selected!</p>";
      } else {
        fileList.innerHTML = "";
        var list = document.createElement("tbody");
        fileList.appendChild(list);
        var loadcount = 0;
        for (var i = 0; i < files.length; i++) {

          var tr = document.createElement("tr");
          list.appendChild(tr);
          
          var statustd = document.createElement("td");
          statustd.className += " statustd";
          tr.appendChild(statustd);

          var progressBarContainer = document.createElement('div');
          progressBarContainer.id = 'bar' + i;
          progressBarContainer.className += " progressContainer";
          statustd.appendChild(progressBarContainer);
          createNewProgressBar('bar' + i);

          var videotd = document.createElement("td");
          var video = document.createElement("video");
          video.dataset.bytesize = files[i].size;
          video.dataset.originalFilename = files[i].name;
          video.dataset.lastModified = files[i].lastModified;
          video.dataset.fileType = files[i].type;
          video.dataset.progressBar = 'bar' + i;
          video.src = window.URL.createObjectURL(files[i]);
          video.id = 'vid' + i;
          video.addEventListener('loadedmetadata', function() {
            loadcount++;
            console.log("load count: "+loadcount);
            if (loadcount == files.length) {
              // only validate after all are loaded.
              console.log("trigger validate");
              $("#buttonAndDropzone").text("Upload Pending");
              $('#targetToDisable').addClass('disabled').siblings().children().prop('disabled',true);          
              validateFiles();
            }
          }, false);         
          video.onload = function() {
            window.URL.revokeObjectURL(this.src);    
          }
          tr.appendChild(videotd);
          videotd.appendChild(video);   
          var info = document.createElement("td");
          info.className += ' metadataBox';
          info.innerHTML = '<span class="originalFilename"></span>'
                         + '<span class="framesize"></span>'      
                         + '<span class="MB"></span>'
                         + '<span class="duration"></span>'
                         + '<span class="Mbps"></span>';
          tr.appendChild(info);
        }
      }
    }  
</script>