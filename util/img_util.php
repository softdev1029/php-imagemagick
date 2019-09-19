<?php

function change_density($src, $level) {
  $dpi = get_dpi($src, $level);
  if ($dpi < DST_DPI) {
    echo indent($level) . "Starting change density..." . PHP_EOL;
    $dst = $src;
    $cmd = "convert \"" . $src . "\" -density " . DST_DPI . "% \"" . $dst . "\"";
    exec($cmd);
    echo indent($level) . $cmd . PHP_EOL;
    echo indent($level) . "Changed density." . PHP_EOL . PHP_EOL;
  }
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

function make_target_inch($file, $inch, $level) {
  echo indent($level) . "Making $inch inch images: $file ..." . PHP_EOL;
  $src = get_dst_file_path($file);
  $dst = get_dst_file_path(rename_file_with_12_inch($file));
  $tmp = get_dst_file_path("tmp.jpg");
  echo indent($level+1) . "File: $src" . PHP_EOL;
  $width = get_width($src, $level+2);
  $dpi = get_dpi($src, $level+2);
  $inches = $width / $dpi;
  echo indent($level+2) . "inches = $inches" . PHP_EOL;
  $repeat = ceil($inch / $inches);
  echo indent($level+2) . "Repeat Count = $repeat" . PHP_EOL . PHP_EOL;

  if ($repeat > 1) {
    $cnt = 1;
    copy($src, $tmp);
    while ($cnt != $repeat) {
      echo indent($level+2) . "Stacking vertically $cnt th ..." . PHP_EOL;
      $cmd = "convert -append \"" . addslashes($tmp) . "\" \"" . addslashes($src) . "\" " . $tmp;
      exec($cmd);
      echo indent($level+3) . "" . $cmd . PHP_EOL;
      echo indent($level+2) . "Stacked vertically $cnt th" . PHP_EOL . PHP_EOL;

      $cnt++;
    }

    $cnt = 1;
    copy($tmp, $dst);
    while ($cnt != $repeat) {
      echo indent($level+2) . "Stacking horizontally $cnt th ..." . PHP_EOL;
      $cmd = "convert +append \"" . addslashes($dst) . "\" \"" . addslashes($tmp) . "\" \"" . $dst . "\"";
      exec($cmd);
      echo indent($level+3) . $cmd . PHP_EOL;
      echo indent($level+2) . "Stacked horizontally $cnt th" . PHP_EOL . PHP_EOL;

      $cnt++;
    }

    unlink($tmp);
  } else {
    copy($src, $dst);
  }

  echo indent($level+2) . "Cropping ..." . PHP_EOL;
  $target_width = $dpi * $inch;
  $cmd = "mogrify -crop $target_width" . "x" . "$target_width" . "+0+0 \"" . addslashes($dst) . "\"";
  exec($cmd);
  echo indent($level+3) . "$cmd" . PHP_EOL;
  echo indent($level+2) . "Cropped" . PHP_EOL . PHP_EOL;
  echo indent($level) . "Made $inch inch images." . PHP_EOL . PHP_EOL;
}

function merge_order($order, $level) {
  echo indent($level) . "Resizing images..." . PHP_EOL;
  if (!isset($order->items)) {
    return;
  }
  for ($i = 0; $i < count($order->items); $i++) {
    $dst = get_dst_file_path(rename_file_with_12_inch($order->items[$i]->dst));
    echo indent($level+1) . "File: $dst" . PHP_EOL;
    
    echo indent($level+2) . "Resizing to 1000px ..." . PHP_EOL;
    $cmd = "convert \"" . addslashes($dst) . "\" -resize 1000x1000 \"" . addslashes($dst) . "\"";
    echo indent($level+3) . $cmd . PHP_EOL;
    exec($cmd);
    echo indent($level+2) . "Resized" . PHP_EOL . PHP_EOL;
  }
  echo indent($level) . "Resized." . PHP_EOL . PHP_EOL;

  echo indent($level) . "Merging order images..." . PHP_EOL;

  $final = get_dst_file_path("order-" . $order->order_num . ".jpg");
  for ($i = 1; $i < count($order->items); $i++) {
    
    $tile = $order->items[$i];
    echo indent($level+1) . "File: $tile->src" . PHP_EOL;

    if ($i == 1) {
      $prev_tile = $order->items[$i];
      echo indent($level+1) . "Prev File: $prev_tile->src" . PHP_EOL;
      $dst0 = get_dst_file_path(rename_file_with_12_inch($prev_tile->dst));
    } else {
      $dst0 = $final;
    }
    $dst1 = get_dst_file_path(rename_file_with_12_inch($tile->dst));

    echo indent($level+2) . "Stacking 2 tiles vertically ..." . PHP_EOL;
    $cmd = "convert -append \"" . addslashes($dst0) . "\" \"" . addslashes($dst1) . "\" \"" . $final . "\"";
    exec($cmd);
    echo indent($level+3) . $cmd . PHP_EOL;
    echo indent($level+2) . "Stacked 2 tiles vertically" . PHP_EOL . PHP_EOL;

  }
  echo indent($level) . "Merged order images." . PHP_EOL . PHP_EOL;
}