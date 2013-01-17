function update_status(log_object) {
  var rendered = "";
  var table_header = '<tr class="header_row"><th class="column-source">Source File</th><th class="column-status column-oggstatus">OGG Status</th><th class="column-status column-mp3-status">MP3 Status</th><th class="column-time column-started">Started</td></tr>';
  var needUpdate = false;
  for(var i=0;i<log_object.length;i++) {
    //if(log_object[i]["ended"] == "") {
      if(rendered != "") { rendered += "</tr><tr>"; }
      var fn = log_object[i]["file"];
      var fn_parts = fn.split('/');
      fn = fn_parts[fn_parts.length-1];
      var ogg_status = log_object[i]["oog"];
      var mp3_status = log_object[i]["mp3"];
      rendered += '<td class="column-source">'+fn+'</td>';
      rendered += '<td class="column-status column-oggstatus '+class_for_status(ogg_status)+'">'+'</td>';
      rendered += '<td class="column-status column-mp3status '+class_for_status(mp3_status)+'">'+'</td>';
      rendered += '<td class="column-time column-started">'+log_object[i]["started"]+'</td>';
      if((ogg_status == 'Queued') || (ogg_status == 'Processing') ||
         (mp3_status == 'Queued') || (mp3_status == 'Processing')) {
          needUpdate = true;
         }
    //}
  }
  if(rendered == "") {
    table_header = "";
    rendered = '<td class="no_encoding" colspan=4></td>';
  }
  $("#status_table").html(table_header+"<tr>"+rendered+"</tr>");
  if(needUpdate) {
    setTimeout(function (){get_status();},5000);
  }
}

function get_status() {
  $.getJSON('/processing/log.php', function(data) {
    update_status(data);
  });
}

function class_for_status(status) {
  switch(status) {
    case 'Queued':
      return "status-requested";
    case 'Processing':
      return "status-encode-processing";
    case 'Failed':
      return "status-encode-failed";
    case 'Encoded':
      return "status-encode-success";
    case 'Error Moving':
      return "status-filemove-failed";
    case 'Complete':
      return "status-complete";
    default:
      return "status-unknown";
  }
  return "status-error";
}
