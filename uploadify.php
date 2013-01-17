<?php  /*
# Copyright (c) 2011-2012 DW Brand
# All Rights Reserved
# Licensed under the MIT license (see http://www.opensource.org/licenses/mit-license.php for details)
*/

function die_log($a) {
  error_log($a);
  echo $a;
  exit;
}

//if(!isset($_REQUEST['session_name'])) {
//  header("HTTP/1.0 404 Not Found");
//  exit;
//}
//if($_REQUEST['session_name'] != 'SomeRandomSessinNameGoesHereThatsUseless') {
//  header("HTTP/1.0 404 Not Found");
//  exit;
//}

$accepted_types = array(
  "audio/wav",
  "audio/ogg",
  "audio/aac",
  "audio/x-m4a",
  "audio/mp3",
  "audio/aiff",
  "audio/x-aiff",
  "mp3",
  "ogg",
  "m4a",
  "aif",
  "aiff",
  "aifc"
);

$base_folder = "/tmp";  /* SET THIS TO YOUR WEB SERVER BASE FOLDER */
$media_folder = $base_folder."/media"; /* Where the converted files will be stored */
$upload_folder = $base_folder."/admin"; /* Base folder for upload area */
$processing_folder = $upload_folder."/processing"; /* The folder that will be used to store various logs and such */
$processing_in_folder = $processing_folder."/in"; /* The folder that will be the target of the upload. MUST BE WRITABLE BY THE WEBSERVER PROCESS */
$processing_out_folder = $processing_folder."/out"; /* The folder that will be a temporary output folder for conversions. MUST BE WRITABLE BY THE WEBSERVER PROCESS */
$overwrite_allowed = 1;  /* Set to 0 to disallow overwrites of existing files */

if(empty($_FILES["Filedata"])) {
  die_log('No upload attempted');
}

$file = $_FILES["Filedata"];

if(!in_array($file["type"], $accepted_types)) {
  $fileParts  = pathinfo($file['name']);
  if (!in_array($fileParts['extension'],$accepted_types)) {
    die_log('Incorrect file type ('.$file["type"].')');
  }
}

if($file["error"] > 0) {
  die_log('Upload error '.$file["error"]);
}

// Reencode the filename because my client loves extended Mac characters which are bad here, m'kay?
$ofn = $file["name"];
$ofn = utf8_decode($ofn);
$ofn = preg_replace("/[^A-Za-z0-9_.]/","_",$ofn);
$fn = preg_replace("/^(.*)\.[^\/.]*$/","$1",$ofn);

if ($overwrite_allowed != 1) {
  if (file_exists($media_folder."/".$fn.".mp3") ||
      file_exists($media_folder."/".$fn.".ogg")) {
    echo 'File with that name already exists';
    exit;
  }
}

if(file_exists($processing_in_folder."/".$ofn)) {
  die_log('Encoding already in progress for this file');
}

move_uploaded_file($file["tmp_name"], $processing_in_folder."/".$ofn);
$cmd = '"'.$upload_folder.'/reencode.sh" "'.$processing_in_folder.'/'.$ofn.'" "'.$processing_out_folder.'/'.$fn.'" "'.$media_folder.'" &> /dev/null &';
error_log("Exec: ".$cmd);
$result = array();
exec($cmd,$result);

echo '1';
exit;
?>