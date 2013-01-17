#!/bin/bash
# Copyright (c) 2011-2012 DW Brand
# All Rights Reserved
# Licensed under the MIT license (see http://www.opensource.org/licenses/mit-license.php for details)

# src_folder must match $processing_folder in uploadify.php
src_folder="/tmp/admin/processing"

# media_folder must match $media_folder in uploadify.php
media_folder="/tmp/media"

lock_file="$src_folder/pid.lock"
log_file="$src_folder/log.json"
active_file="$src_folder/active.json"
job_file="$src_folder/currentjob.txt"
input_folder="$src_folder/in"
output_folder="$src_folder/out"
failed_folder="$src_folder/failed"

FILES="$input_folder/*"
for f in $FILES
do
  xbase="${f##*/}"
  filename="${xbase%.*}"
  active_file="$src_folder/active.json.$filename"
  if [ ! -e "$active_file" ]
  then
    echo "{\"file\":\"$f\",\"oog\":\"Queued\",\"mp3\":\"Queued\",\"started\":\"\",\"ended\":\"\"}" > "$active_file"
  fi
done

if [ -e "$lock_file" ]
then
  exit
fi

touch "$lock_file"

input_file=`ls -1 $input_folder | head -1`
input_file="$input_folder/$input_file"
while [ -f "$input_file" ]
do
  ogg_moved=0
  mp3_moved=0

  FILES="$input_folder/*"
  for f in $FILES
  do
    xbase="${f##*/}"
    filename="${xbase%.*}"
    active_file="$src_folder/active.json.$filename"
    echo "{\"file\":\"$f\",\"oog\":\"Queued\",\"mp3\":\"Queued\",\"started\":\"\",\"ended\":\"\"}" > "$active_file"
  done

  start=$(date)
  ogg_status="Queued"
  mp3_status="Queued"
  ended=""
  #echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"-\"}" >> "$log_file"

  xbase="${input_file##*/}"
  filename="${xbase%.*}"
  active_file="$src_folder/active.json.$filename"
  output_file="$output_folder/$filename"
  
  ogg_status="Processing"
  echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"$ended\"}" > "$active_file"
  /usr/local/bin/ffmpeg -v info -y -i "$input_file" -acodec libvorbis -ab 96k "$output_file.ogg" &> "$job_file"
  if [ -e "$output_file.ogg" ]
  then
    ogg_status="Encoded"
  else
    ogg_status="Failed"
  fi

  if [ -e "$output_file.ogg" ]
  then
    mv "$output_file.ogg" "$media_folder"
    
    if [ ! -e "$output_file.ogg" ]
    then
      ogg_status="Complete"
      ogg_moved=1
    else
      ogg_status="Error Moving"
    fi
  fi
  
  echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"$ended\"}" > "$active_file"
  
  mp3_status="Processing"
  
  echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"$ended\"}" > "$active_file"
  /usr/local/bin/ffmpeg -v info -y -i "$input_file" -acodec libmp3lame -ab 96k "$output_file.mp3" &>> "$job_file"
  if [ -e "$output_file.mp3" ]
  then
    mp3_status="Encoded"
  else
    mp3_status="Failed"
  fi

  echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"$ended\"}" > "$active_file"

  if [ -e "$output_file.mp3" ]
  then
    mv "$output_file.mp3" "$media_folder"
    
    if [ ! -e "$output_file.mp3" ]
    then
      mp3_status="Complete"
      mp3_moved=1
    else
      mp3_status="Error Moving"
    fi
  fi
  
  echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"$ended\"}" > "$active_file"
  
  if [ $ogg_moved -eq 1 ] && [ $mp3_moved -eq 1 ]
  then
    rm "$input_file"
  else
    mv "$input_file" "$failed_folder"
  fi

  ended=$(date)
  echo "{\"file\":\"$input_file\",\"oog\":\"$ogg_status\",\"mp3\":\"$mp3_status\",\"started\":\"$start\",\"ended\":\"$ended\"}" >> "$log_file"
  rm -f "$active_file"

  input_file=`ls -1 $input_folder | head -1`
  input_file="$input_folder/$input_file"
done

rm -f "$lock_file"
