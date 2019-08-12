<?php

function change_color_to_black($store) {
  echo "Changing the color to black..." . PHP_EOL;
  foreach($store->img_array as $img_item) {
    echo "\tFile: $img_item->dst" . PHP_EOL;
    $src = get_src_tmp_file_path($img_item->dst);
    $dst = get_dst_file_path(get_png_name($img_item->dst));
    $cmd = "convert -background none -size 2000x2000 $src $dst";
    exec($cmd);
    echo "\t\t$cmd" . PHP_EOL;
    echo "\t\tRemoved the background." . PHP_EOL;
    $cmd = "convert $dst -colorspace Gray $dst";
    exec($cmd);
    echo "\t\t$cmd" . PHP_EOL;
    echo "\t\tChanged to the gray color." . PHP_EOL;
  }
  echo "Changed the color." . PHP_EOL . PHP_EOL;
}

function get_image_dimen(&$store) {
  echo "Getting the dimension..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    $src = get_src_tmp_file_path($img_item->dst);
    $dst = get_dst_file_path(get_png_name($img_item->dst));
    $cmd = "identify -ping -format %w $dst";
    $rlt = shell_exec($cmd);
    $img_item->w = $rlt;

    $cmd = "identify -ping -format %h $dst";
    $rlt = shell_exec($cmd);
    $img_item->h = $rlt;

    echo "\tFile=$img_item->dst, w=$img_item->w, h=$img_item->h" . PHP_EOL;
  }
  echo "Got the dimesion." . PHP_EOL . PHP_EOL;
}

function resize_image(&$store) {
  echo "Starting to resize..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    echo "\tFile: " . $img_item->dst . PHP_EOL;
    $i = 1;
    foreach ($store->proportion_array as $proportion) {
      echo "\t\tProportion: type=" . $proportion['type'] . ", size=" . $proportion['size'] . PHP_EOL;
      $src = get_dst_file_path(get_png_name($img_item->dst));
      $dst = get_dst_file_path(get_png_name($img_item->dst), $proportion['type'] . $proportion['size']);
      $size = 'size' . $i;
      $ratio = $img_item->w / $img_item->h;
      if ($proportion['type'] == 'w') {
        $rw = $proportion['size'] * UNIT;
        $rh = $rw / $img_item->w * $img_item->h;
      } else {
        $rh = $proportion['size'] * UNIT;
        $rw = $rh / $img_item->h * $img_item->w;
      }
      $ratio = $rw / $img_item->w * 100;
      $img_item->size = "w=" . ($rw / UNIT) . ", h=" . ($rh / UNIT);

      echo "\t\t\tsize=$img_item->size" . PHP_EOL;
      echo "\t\t\tratio=$ratio" . PHP_EOL;
      $cmd = "convert " . $src . " -resize " . $ratio . "% " . $dst;
      exec($cmd);
      echo "\t\t\t$cmd" . PHP_EOL;
      $i++;
    }
  }
  echo "Resized." . PHP_EOL . PHP_EOL;
}

function merge_mark_desk(&$store) {
  echo "Merging mark and desk..." . PHP_EOL;
  foreach ($store->img_array as $img_item) {
    echo "\tFile=$img_item->dst" . PHP_EOL;
    $i = 0;
    $dir_name = DESK_DIR;
    $dir = new DirectoryIterator($dir_name);
    foreach ($dir as $file_info) {
      if (!$file_info->isDot()) {
        echo "\t\tDesk file=" . $file_info->getFilename() . PHP_EOL;
        
        // the desk image
        $desk = $file_info->getFilename();
        $desk = get_desk_file_path($desk);

        // get the width of desk image
        $cmd = "identify -ping -format %w $desk";
        $desk_w = shell_exec($cmd);

        // get the height of desk image
        $cmd = "identify -ping -format %h $desk";
        $desk_h = shell_exec($cmd);
        
        // the mark image
        $mark = get_dst_file_path(get_png_name($img_item->dst));

        // get the width of mark image
        $cmd = "identify -ping -format %w $mark";
        $mark_w = shell_exec($cmd);

        // get the height of mark image
        $cmd = "identify -ping -format %h $mark";
        $mark_h = shell_exec($cmd);

        echo "\t\t\tDesk w=$desk_w, h=$desk_h,\t Mark w=$mark_w, h=$mark_h" . PHP_EOL;

        $mark_w_final = $desk_w * MARK_RATIO / 100;
        $ratio = $mark_w_final / $mark_w * 100;
        $mark_h_final = $mark_h * $ratio / 100;
        $offset_y = $mark_h_final / 2;

        // if resized mark height is larger than desk height
        if ($mark_h_final > $desk_h / 2) {
          $img_item->error = "decal too tall for mockup";
          echo "\t\t\tError: mark height $mark_h_final is larger than half of desk height $desk_h" . PHP_EOL;
        } else {
          $img_item->error = "";
        }

        // resize mark image
        $cmd = "convert $mark -resize $ratio% tmp.png";
        exec($cmd);
        echo "\t\t\t$cmd" . PHP_EOL;

        // merge mark and desk
        $dst = get_mockup_file_path(get_png_name($img_item->dst), $file_info->getFilename());
        $cmd = "magick $desk tmp.png -gravity center -geometry -0-$offset_y -compose over -composite " . $dst;

        exec($cmd);
        echo "\t\t\t$cmd" . PHP_EOL;

        unlink("tmp.png");

        insert_white_space($dst);
        
        $i++;
      }
    }
  }
  echo "Merged." . PHP_EOL . PHP_EOL;
}

function insert_white_space($src) {
  echo "\t\t\tInserting white spaces..." . PHP_EOL;
  $w = FINAL_WIDTH;
  $cmd = "convert $src -gravity center -extent $w" . "x" ."$w $src";
  exec($cmd);
  echo "\t\t\t\t$cmd" . PHP_EOL;

  echo "\t\t\tInserted white spaces." . PHP_EOL;
}