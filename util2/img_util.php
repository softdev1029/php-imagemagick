<?php

function resize_image(&$store) {
  echo "Starting resize..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    for ($i = 0; $i < count($img_item->dst); $i++) {
      $src = get_dst_file_path($img_item->dst[$i]);
      $dst = $src;
      $ratio = DST_INFO[$i]["scale"];
      $cmd = "convert " . $src . " -resize " . $ratio . "% " . $dst;
      exec($cmd);
      echo "\t" . $cmd . PHP_EOL;
    }
  }
  echo "Resized." . PHP_EOL . PHP_EOL;
}

function get_inches($filepath) {
  echo "\t\tCalculating inches..." . PHP_EOL;

  $cmd = "identify -format %w $filepath";
  $width = exec($cmd);
  echo "\t\t\t" . $cmd . PHP_EOL;
  echo "\t\t\tWidth = $width" . PHP_EOL;

  $cmd = "identify -format %x $filepath";
  $dpi = exec($cmd);
  echo "\t\t\t" . $cmd . PHP_EOL;
  echo "\t\t\tDPI = $dpi" . PHP_EOL;

  $inches = $width / $dpi;
  echo "\t\t\tinches = $inches" . PHP_EOL;

  echo "\t\tCalculated inches." . PHP_EOL . PHP_EOL;
  return $inches;
}

function make_12_inch(&$store) {
  echo "Making 12 inch images..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    for ($i = 0; $i < count($img_item->dst); $i++) {
      $src = get_dst_file_path($img_item->dst[$i]);
      echo "\tFile: $src" . PHP_EOL;
      $inches = get_inches($src);
      $repeat = ceil(12 / $inches);
      echo "\t\tRepeat Count = $repeat" . PHP_EOL;
    }
  }
  echo "Made 12 inch images." . PHP_EOL . PHP_EOL;
}