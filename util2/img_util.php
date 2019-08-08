<?php

function change_density($src) {
  $dpi = get_dpi($src);
  if ($dpi < DST_DPI) {
    echo "\t\t\tStarting change density..." . PHP_EOL;
    $dst = $src;
    $cmd = "convert \"" . $src . "\" -density " . DST_DPI . "% \"" . $dst . "\"";
    exec($cmd);
    echo "\t\t\t" . $cmd . PHP_EOL;
    echo "\t\t\tChanged density." . PHP_EOL . PHP_EOL;
  }
}

function resize_image(&$store) {
  echo "Starting resize..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    for ($i = 0; $i < count($img_item->dst); $i++) {
      $src = get_dst_file_path($img_item->dst[$i]);
      $dst = $src;
      $ratio = DST_INFO[$i]["scale"];
      $cmd = "convert \"" . $src . "\" -resize " . $ratio . "% \"" . $dst . "\"";
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

      if ($repeat > 1) {
        $cnt = 1;
        copy($src, $tmp);
        while ($cnt != $repeat) {
          echo "\t\tStacking vertically $cnt th ..." . PHP_EOL;
          $cmd = "convert -append \"" . addslashes($tmp) . "\" \"" . addslashes($src) . "\" " . $tmp;
          exec($cmd);
          echo "\t\t\t" . $cmd . PHP_EOL;
          echo "\t\tStacked vertically $cnt th" . PHP_EOL . PHP_EOL;

          $cnt++;
        }

        $cnt = 1;
        copy($tmp, $dst);
        while ($cnt != $repeat) {
          echo "\t\tStacking horizontally $cnt th ..." . PHP_EOL;
          $cmd = "convert +append \"" . addslashes($dst) . "\" \"" . addslashes($tmp) . "\" \"" . $dst . "\"";
          exec($cmd);
          echo "\t\t\t" . $cmd . PHP_EOL;
          echo "\t\tStacked horizontally $cnt th" . PHP_EOL . PHP_EOL;

          $cnt++;
        }

        unlink($tmp);
      } else {
        copy($src, $dst);
      }

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

function merge_4_types(&$store) {
  echo "Resizing images..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    for ($i = 0; $i < count($img_item->dst); $i++) {
      $dst = get_dst_file_path(rename_file_with_12_inch($img_item->dst[$i]));
      echo "\tFile: $dst" . PHP_EOL;
      
      echo "\t\tResizing to 1000px ..." . PHP_EOL;
      $cmd = "convert \"" . addslashes($dst) . "\" -resize 1000x1000 \"" . addslashes($dst) . "\"";
      echo "\t\t\t$cmd" . PHP_EOL;
      exec($cmd);
      echo "\t\tResized" . PHP_EOL . PHP_EOL;
    }
  }
  echo "Resized." . PHP_EOL . PHP_EOL;

  echo "Merging 4 type images..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    echo "\tFile: $img_item->src" . PHP_EOL;
    $dst0 = get_dst_file_path(rename_file_with_12_inch($img_item->dst[0]));
    $dst1 = get_dst_file_path(rename_file_with_12_inch($img_item->dst[1]));
    $dst2 = get_dst_file_path(rename_file_with_12_inch($img_item->dst[2]));
    $dst3 = get_dst_file_path(rename_file_with_12_inch($img_item->dst[3]));
    $final = get_dst_file_path(rename_final($img_item->src));

    $tmp_dst_4_2 = get_dst_file_path("tmp_4_2.jpg");
    $tmp_dst_3_1 = get_dst_file_path("tmp_3_1.jpg");
    
    echo "\t\tStacking 4th and 2nd vertically ..." . PHP_EOL;
    $cmd = "convert -append \"" . addslashes($dst3) . "\" \"" . addslashes($dst1) . "\" \"" . $tmp_dst_4_2 . "\"";
    exec($cmd);
    echo "\t\t\t" . $cmd . PHP_EOL;
    echo "\t\tStacked 4th and 2nd vertically" . PHP_EOL . PHP_EOL;

    echo "\t\tStacking 3rd and 1st vertically ..." . PHP_EOL;
    $cmd = "convert -append \"" . addslashes($dst2) . "\" \"" . addslashes($dst0) . "\" \"" . $tmp_dst_3_1 . "\"";
    exec($cmd);
    echo "\t\t\t" . $cmd . PHP_EOL;
    echo "\t\tStacked 3rd and 1st vertically" . PHP_EOL . PHP_EOL;

    echo "\t\tStacking horizontally ..." . PHP_EOL;
    $cmd = "convert +append \"" . addslashes($tmp_dst_4_2) . "\" \"" . addslashes($tmp_dst_3_1) . "\" \"" . $final . "\"";
    exec($cmd);
    echo "\t\t\t" . $cmd . PHP_EOL;
    echo "\t\tStacked horizontally" . PHP_EOL . PHP_EOL;
  }
  echo "Merged 4 type images." . PHP_EOL . PHP_EOL;
}