<?php

function change_color_to_black($store) {
  foreach($store->img_array as $img_item) {
    $src = get_src_tmp_file_path($img_item->dst);
    $dst = get_dst_file_path(get_png_name($img_item->dst));
    $cmd = "convert $src -colorspace LinearGray -flatten -fuzz 1% -trim +repage $dst";
    exec($cmd);
    debug($cmd);
    $cmd = "convert $dst -transparent white $dst";
    exec($cmd);
    debug($cmd);
    $cmd = "convert $dst -negate -threshold 0 -negate $dst";
    exec($cmd);
    debug($cmd);
    $cmd = "convert $dst -background black -alpha remove $dst";
    exec($cmd);
    debug($cmd);
    $cmd = "convert $dst -transparent white $dst";
    exec($cmd);
    debug($cmd);
  }
}

function get_image_dimen(&$store) {
  foreach ($store->img_array as $img_item) {
    $src = get_src_tmp_file_path($img_item->dst);
    $dst = get_dst_file_path(get_png_name($img_item->dst));
    $cmd = "identify -ping -format '%w' $dst";
    $rlt = shell_exec($cmd);
    $img_item->w = $rlt;
    debug('w=' . $rlt);

    $cmd = "identify -ping -format '%h' $dst";
    $rlt = shell_exec($cmd);
    $img_item->h = $rlt;
    debug('h=' . $rlt);
  }
}

function resize_image(&$store) {
  debug("Starting resize...");
  foreach ($store->img_array as $img_item) {
    $i = 1;
    foreach ($store->proportion_array as $proportion) {
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
      $img_item->$size = "w=" . ($rw / UNIT) . ", h=" . ($rh / UNIT);
      $cmd = "convert " . $src . " -resize " . $ratio . "% " . $dst;
      exec($cmd);
      debug($cmd);
      $i++;
    }
  } 
}

function merge_mark_desk(&$store) {
  debug("Merging mark and desk...");
  foreach ($store->img_array as $img_item) {
    $i = 0;
    $dir_name = DESK_DIR;
    $dir = new DirectoryIterator($dir_name);
    foreach ($dir as $file_info) {
      if (!$file_info->isDot()) {
        
        // the desk image
        $desk = $file_info->getFilename();
        $desk = get_desk_file_path($desk);

        // get the width of desk image
        $cmd = "identify -ping -format '%w' $desk";
        $desk_w = shell_exec($cmd);
        debug($cmd);

        // get the height of desk image
        $cmd = "identify -ping -format '%h' $desk";
        $desk_h = shell_exec($cmd);
        debug($cmd);
        
        // the mark image
        $mark = get_dst_file_path(get_png_name($img_item->dst));

        // get the width of mark image
        $cmd = "identify -ping -format '%w' $mark";
        $mark_w = shell_exec($cmd);
        debug($cmd);

        // get the height of mark image
        $cmd = "identify -ping -format '%h' $mark";
        $mark_h = shell_exec($cmd);
        debug($cmd);

        $mark_w_final = $desk_w * MARK_RATIO / 100;
        $ratio = $mark_w_final / $mark_w * 100;
        $mark_h_final = $mark_h * $ratio / 100;
        $offset_y = $mark_h_final / 2;

        // if resized mark height is larger than desk height
        if ($mark_h_final > $desk_h / 2) {
          $img_item->error = "decal too tall for mockup";
          debug("Error: mark height $mark_h_final is larger than half of desk height $desk_h");
        } else {
          $img_item->error = "";
        }

        // resize mark image
        $cmd = "convert $mark -resize $ratio% tmp.png";
        exec($cmd);
        debug($cmd);

        // merge mark and desk
        $dst = get_mockup_file_path(get_png_name($img_item->dst), $file_info->getFilename());
        $cmd = "magick $desk tmp.png -gravity center -geometry -0-$offset_y -compose over -composite " . $dst;

        exec($cmd);
        debug($cmd);

        unlink("tmp.png");

        $i++;
      }
    }
  }
}