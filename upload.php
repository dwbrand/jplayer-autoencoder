<?php  /*
# Copyright (c) 2011-2012 DW Brand
# All Rights Reserved
# Licensed under the MIT license (see http://www.opensource.org/licenses/mit-license.php for details)
*/?><html>
<head>
    <link type="text/css" rel="stylesheet" media="all" href="style.css">
    <link rel="stylesheet" type="text/css" href="upload.css" />
    <script type="text/javascript" src="/js/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="/js/jquery-ui-1.8.4.custom.min.js"></script>
    <script type="text/javascript" src="jquery.filedrop.js"></script>
    <script type="text/javascript" src="status_monitor.js"></script>
</head>
<body>
<div class="header">
<div class="logo-block">
</div>
Upload Center
</div>

<div class="main-block">

<div class="page_name">Upload File</div>

<div class="content">
  <div id="dropzone">Drop a music file here to upload</div>
  <ul id="files_uploading"></ul>
  <div id="fileQueue"></div>
  <table id="status_table">
  </table>
</div>

<script type="text/javascript">
function setup_dropzone() {
  $('#dropzone').filedrop({
    url: '/uploadify.php',
    paramname: 'Filedata',
    maxFiles: 5,
    maxfilesize: 750,
    dragOver: function() { $('#dropzone').addClass('hovering').html("Drop to upload"); },
    dragLeave: function() { $('#dropzone').removeClass('hovering').html("Drag a music file here to upload"); },
    drop: function() { $('#dropzone').removeClass('hovering').addClass('processing').html("Processing...  Please wait..."); },
    uploadStarted: function(i, file, len) {
      var fn = file.name.replace(/\.[^/.]+$/, "");
      var x = $('<li></li>').attr('id','fileid'+i).addClass('progressItem');
      var y = $('<div></div>').html('Uploading "'+fn+'"').addClass('progressName');
      x.append(y);
      y = $('<div></div>').addClass('progressBar').html($('<div></div>').addClass('progressBarProg').html('0%'));
      x.append(y);
      $('#files_uploading').append(x);
    },
    uploadFinished: function(i, file, response, time) {
      $('#fileid'+i+' .progressBarProg').html('100%').attr('style','width: 100%;');
      $('#fileid'+i).addClass('fileuploadcomplete');
      setTimeout(function(i){$('.fileuploadcomplete').remove();get_status();},1000);
    },
    progressUpdated: function(i, file, progress) {
      $('#fileid'+i+' .progressBarProg').html(progress+'%').attr('style','width: '+progress+'%;');
    },
    afterAll: function() {
        // runs after all files have been uploaded or otherwise dealt with
        $('#dropzone').removeClass('processing').html("Drag a music file here to upload");
    }

  });
}

    $(document).ready(function() {
        setup_dropzone();
        get_status();
      });
</script>
</body>
</html>
