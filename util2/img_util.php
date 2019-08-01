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
