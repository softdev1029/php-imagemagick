<?php

function change_color_to_black($store) {
  foreach($store->img_array as $img_item) {
    $src = get_src_tmp_file_path($img_item->dst);
    $dst = get_dst_file_path(get_png_name($img_item->dst));
    $cmd = "convert " . $src . " -colorspace LinearGray " . $dst;
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
      $dst = get_dst_file_path(get_png_name($img_item->dst), $proportion);
      $size = 'size' . $i;
      $rw = $img_item->w * $proportion / 100;
      $rh = $img_item->h * $proportion / 100;
      $img_item->$size = "w=" . $rw . ", h=" . $rh;
      $cmd = "convert " . $src . " -resize " . $proportion . "% " . $dst;
      exec($cmd);
      debug($cmd);
      $i++;
    }
  } 
}