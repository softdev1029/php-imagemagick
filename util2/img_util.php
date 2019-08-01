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

function get_dpi($filepath) {
  echo "\t\tCalculating DPI..." . PHP_EOL;

  $cmd = "identify -format %x \"" . addslashes($filepath) . "\"";
  $dpi = exec($cmd);
  echo "\t\t\t" . $cmd . PHP_EOL;
  echo "\t\t\tDPI = $dpi" . PHP_EOL;

  echo "\t\tCalculated DPI." . PHP_EOL . PHP_EOL;
  return $dpi;
}

function get_width($filepath) {
  echo "\t\tCalculating width..." . PHP_EOL;

  $cmd = "identify -format %w \"" . addslashes($filepath) . "\"";
  $width = exec($cmd);
  echo "\t\t\t" . $cmd . PHP_EOL;
  echo "\t\t\tWidth = $width" . PHP_EOL;

  echo "\t\tCalculated width." . PHP_EOL . PHP_EOL;
  return $width;
}

function make_target_inch(&$store, $inch) {
  echo "Making $inch inch images..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    for ($i = 0; $i < count($img_item->dst); $i++) {
      $src = get_dst_file_path($img_item->dst[$i]);
      $dst = get_dst_file_path(rename_file_with_12_inch($img_item->dst[$i]));
      $tmp = get_dst_file_path("tmp.jpg");
      echo "\tFile: $src" . PHP_EOL;
      $width = get_width($src);
      $dpi = get_dpi($src);
      $inches = $width / $dpi;
      echo "\t\tinches = $inches" . PHP_EOL;
      $repeat = ceil($inch / $inches);
      echo "\t\tRepeat Count = $repeat" . PHP_EOL . PHP_EOL;

      echo "\t\tStacking vertically ..." . PHP_EOL;
      $cmd = "convert -append \"" . addslashes($src) . "\" \"" . addslashes($src) . "\" " . $tmp;
      exec($cmd);
      echo "\t\t\t" . $cmd . PHP_EOL;
      echo "\t\tStacked vertically" . PHP_EOL . PHP_EOL;

      echo "\t\tStacking horizontally ..." . PHP_EOL;
      $cmd = "convert +append \"" . addslashes($tmp) . "\" \"" . addslashes($tmp) . "\" \"" . $dst . "\"";
      exec($cmd);
      echo "\t\t\t" . $cmd . PHP_EOL;
      echo "\t\tStacked horizontally" . PHP_EOL . PHP_EOL;

      unlink($tmp);

      echo "\t\tCropping ..." . PHP_EOL;
      $target_width = $dpi * $inch;
      $cmd = "mogrify -crop $target_width" . "x" . "$target_width" . "+0+0 \"" . addslashes($dst) . "\"";
      exec($cmd);
      echo "\t\t\t$cmd" . PHP_EOL;
      echo "\t\tCropped" . PHP_EOL . PHP_EOL;
    }
  }
  echo "Made $inch inch images." . PHP_EOL . PHP_EOL;
}