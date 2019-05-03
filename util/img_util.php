<?php

function change_color_to_black($store) {
  foreach($store->img_array as $img_item) {
    $src = get_src_tmp_file_path($img_item->dst);
    $dst = get_dst_file_path(get_png_name($img_item->dst));
    $cmd = "convert " . $src . " -colorspace LinearGray -flatten -fuzz 1% -trim +repage " . $dst;
    exec($cmd);
    debug($cmd);
    $cmd = "convert " . $dst . " -transparent white " . $dst;
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
        $rw = $rh / $img_item->h * $img_item->w;
        $rh = $proportion['size'] * UNIT;
      }
      $ratio = $rw / $img_item->w * 100;
      $img_item->$size = "w=" . $rw . ", h=" . $rh;
      $cmd = "convert " . $src . " -resize " . $ratio . "% " . $dst;
      exec($cmd);
      debug($cmd);
      $i++;
    }
  } 
}

function merge_mark_desk($store) {
  debug("Merging mark and desk...");
  foreach ($store->img_array as $img_item) {
    $i = 0;
    $dir_name = DESK_DIR;
    $dir = new DirectoryIterator($dir_name);
    foreach ($dir as $file_info) {
      if (!$file_info->isDot()) {
        $desk = $file_info->getFilename();
        $desk = get_desk_file_path($desk);
        
        $mark = get_dst_file_path(get_png_name($img_item->dst));

        $dst = get_mockup_file_path(get_png_name($img_item->dst), $file_info->getFilename());
        $cmd = "magick " . $desk . " " . $mark . " -gravity center -compose over -composite " . $dst;

        exec($cmd);
        debug($cmd);

        $i++;
      }
    }
  }
}