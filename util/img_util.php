<?php

function change_density($src, $level) {
  $dpi = get_dpi($src, $level);
  //if ($dpi < DST_DPI) {
    echo indent($level) . "Starting change density..." . PHP_EOL;
    $dst = $src;
    $cmd = "convert \"" . $src . "\" -density " . DST_DPI . "% \"" . $dst . "\"";
    exec($cmd);
    echo indent($level) . $cmd . PHP_EOL;
    echo indent($level) . "Changed density." . PHP_EOL . PHP_EOL;
  //}
}

function resize_image($file, $scale, $level) {
  echo indent($level) . "Starting resize: $file ..." . PHP_EOL;
  $src = get_dst_file_path($file);
  $dst = $src;
  $ratio = $scale;
  $cmd = "convert \"" . $src . "\" -resize " . $ratio . "% \"" . $dst . "\"";
  exec($cmd);
  echo indent($level+1) . $cmd . PHP_EOL;
  echo indent($level) . "Resized." . PHP_EOL . PHP_EOL;
}

function get_dpi($filepath, $level) {
  echo indent($level) . "Calculating DPI..." . PHP_EOL;

  $cmd = "identify -format %x \"" . addslashes($filepath) . "\"";
  $dpi = exec($cmd);
  echo indent($level+1) . $cmd . PHP_EOL;
  echo indent($level+1) . "DPI = $dpi" . PHP_EOL;

  echo indent($level) . "Calculated DPI." . PHP_EOL . PHP_EOL;
  return $dpi;
}

function get_width($filepath, $level) {
  echo indent($level) . "Calculating width..." . PHP_EOL;

  $cmd = "identify -format %w \"" . addslashes($filepath) . "\"";
  $width = exec($cmd);
  echo indent($level+1) . $cmd . PHP_EOL;
  echo indent($level+1) . "Width = $width" . PHP_EOL;

  echo indent($level) . "Calculated width." . PHP_EOL . PHP_EOL;
  return $width;
}

function get_height($filepath, $level) {
  echo indent($level) . "Calculating height..." . PHP_EOL;

  $cmd = "identify -format %h \"" . addslashes($filepath) . "\"";
  $height = exec($cmd);
  echo indent($level+1) . $cmd . PHP_EOL;
  echo indent($level+1) . "Height = $height" . PHP_EOL;

  echo indent($level) . "Calculated height." . PHP_EOL . PHP_EOL;
  return $height;
}

function make_target_inch($file, $w_inch, $h_inch, $level) {
  echo indent($level) . "Making $w_inch x $h_inch images: $file ..." . PHP_EOL;
  $src = get_dst_file_path($file);
  $tmp = get_dst_file_path("tmp.jpg");
  echo indent($level+1) . "File: $src" . PHP_EOL;
  $width = get_width($src, $level+2);
  $height = get_height($src, $level+2);
  $dpi = get_dpi($src, $level+2);
  $org_w_inches = $width / $dpi;
  $org_h_inches = $height / $dpi;
  echo indent($level+2) . "origin inches = $org_w_inches x $org_h_inches" . PHP_EOL;
  $w_repeat = ceil($w_inch / $org_w_inches);
  $h_repeat = ceil($h_inch / $org_h_inches);
  echo indent($level+2) . "Repeat Count = $w_repeat, $h_repeat" . PHP_EOL . PHP_EOL;

  if ($h_repeat > 1) {
    $cnt = 1;
    copy($src, $tmp);
    while ($cnt != $h_repeat) {
      echo indent($level+2) . "Stacking vertically $cnt th ..." . PHP_EOL;
      $cmd = "convert -append \"" . addslashes($tmp) . "\" \"" . addslashes($src) . "\" " . $tmp;
      exec($cmd);
      echo indent($level+3) . "" . $cmd . PHP_EOL;
      echo indent($level+2) . "Stacked vertically $cnt th" . PHP_EOL . PHP_EOL;

      $cnt++;
    }
  }
  if ($w_repeat > 1) {
    $cnt = 1;
    copy($tmp, $src);
    while ($cnt != $w_repeat) {
      echo indent($level+2) . "Stacking horizontally $cnt th ..." . PHP_EOL;
      $cmd = "convert +append \"" . addslashes($src) . "\" \"" . addslashes($tmp) . "\" \"" . $src . "\"";
      exec($cmd);
      echo indent($level+3) . $cmd . PHP_EOL;
      echo indent($level+2) . "Stacked horizontally $cnt th" . PHP_EOL . PHP_EOL;

      $cnt++;
    }

    unlink($tmp);
  }

  echo indent($level+2) . "Cropping ..." . PHP_EOL;
  $target_width = $dpi * $w_inch;
  $target_height = $dpi * $h_inch;
  $cmd = "mogrify -crop $target_width" . "x" . "$target_height" . "+0+0 \"" . addslashes($src) . "\"";
  exec($cmd);
  echo indent($level+3) . "$cmd" . PHP_EOL;
  echo indent($level+2) . "Cropped" . PHP_EOL . PHP_EOL;
  echo indent($level) . "Made $w_inch x $h_inch inch images." . PHP_EOL . PHP_EOL;
}

function rotate_image($file, $value, $level) {
  echo indent($level) . "Rotating $value : $file ..." . PHP_EOL;
  $path = get_dst_file_path($file);
  $cmd = "mogrify -rotate \"$value\" \"" . addslashes($path) . "\"";
  exec($cmd);
  echo indent($level+1) . "" . $cmd . PHP_EOL;
  echo indent($level) . "Rotated $value." . PHP_EOL . PHP_EOL;
}

function repeat_image($file, $repeat, $level) {
  echo indent($level) . "Repeating $repeat times with the image: $file ..." . PHP_EOL;
  $src = get_dst_file_path(rename_file_with_12_inch($file));
  $tmp = get_dst_file_path("tmp.jpg");
  
  if ($repeat > 1) {
    $cnt = 1;
    copy($src, $tmp);
    while ($cnt != $repeat) {
      echo indent($level+1) . "Stacking vertically $cnt th ..." . PHP_EOL;
      $cmd = "convert -append \"" . addslashes($tmp) . "\" \"" . addslashes($src) . "\" \"" . addslashes($src) . "\"";
      exec($cmd);
      echo indent($level+2) . "" . $cmd . PHP_EOL;
      echo indent($level+1) . "Stacked vertically $cnt th" . PHP_EOL . PHP_EOL;

      $cnt++;
    }

    unlink($tmp);
  }

  echo indent($level) . "Repeated $repeat times." . PHP_EOL . PHP_EOL;
}

function merge_order($order, $level) {

  echo indent($level) . "Merging order images..." . PHP_EOL;

  $final = get_dst_file_path("order-" . $order->order_num . ".jpg");
  for ($i = 1; $i < count($order->items); $i++) {
    
    $tile = $order->items[$i];
    echo indent($level+1) . "File: $tile->dst" . PHP_EOL;

    if ($i == 1) {
      $prev_tile = $order->items[$i-1];
      echo indent($level+1) . "Prev File: $prev_tile->dst" . PHP_EOL;
      $dst0 = get_dst_file_path($prev_tile->dst);
    } else {
      $dst0 = $final;
    }
    $dst1 = get_dst_file_path($tile->dst);

    // the first tile + pre pattern file
    echo indent($level+2) . "Stacking [the first tile] and [pre pattern] vertically ..." . PHP_EOL;
    $cmd = "convert -append \"" . addslashes($dst0) . "\" \"" . addslashes(PRE_PATTERN_FILE) . "\" \"" . $final . "\"";
    exec($cmd);
    echo indent($level+3) . $cmd . PHP_EOL;
    echo indent($level+2) . "Stacked vertically" . PHP_EOL . PHP_EOL;

    // then put more the second tile
    echo indent($level+2) . "Stacking [the second tile] vertically ..." . PHP_EOL;
    $cmd = "convert -append \"" . addslashes($final) . "\" \"" . addslashes($dst1) . "\" \"" . $final . "\"";
    exec($cmd);
    echo indent($level+3) . $cmd . PHP_EOL;
    echo indent($level+2) . "Stacked vertically" . PHP_EOL . PHP_EOL;

  }
  echo indent($level) . "Merged order images." . PHP_EOL . PHP_EOL;
}