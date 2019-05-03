<?php

function get_png_name($svg_name) {
  return str_replace("svg", "png", $svg_name);
}

function get_src_file_path($file_name) {
  return SRC_DIR . "/" . $file_name;
}

function get_dst_file_path($file_name, $proportion = 0) {
  if ($proportion != 0) {
    return DST_DIR . "/" . $proportion . "_" . $file_name;
  }
  return DST_DIR . "/" . $file_name;
}

function get_dst_csv_file_path() {
  $file_name = "output_" . date("Ymd") . ".csv";
  return DST_CSV_DIR . "/" . $file_name;
}

function rename_file($src) {
  $dst = date("Ymd") . "_" . str_replace(" ", "_", $src);
  return $dst;
}

function rename_files(&$store) {
  $i = 0;
  $dir_name = SRC_DIR;
  $dir = new DirectoryIterator($dir_name);
  foreach ($dir as $file_info) {
    if (!$file_info->isDot()) {
      $src = $file_info->getFilename();
      $dst = rename_file($src);
      $img_item = new ImageItem();
      $img_item->src = $src;
      $img_item->dst = $dst;
      $img_item->dir = $dir_name;
      $store->add($img_item);

      $src = get_src_file_path($img_item->src);
      $dst = get_src_file_path($img_item->dst);
      rename($src, $dst);
      debug('src=' . $src . ', dst=' . $dst);
      $i++;
    }
  }
}

function make_csv_file($store) {
  // header('Content-Type: text/csv');
  // header('Content-Disposition: attachment; filename="sample.csv"');

  // $fp = fopen('php://output', 'wb');
  $fp = fopen(get_dst_csv_file_path(), 'w');
  $head = array(
    'Old File Name',
    'New SKU',
    'Width',
    'Height',
    'Size1',
    'Size2',
    'Size3',
    'Size4',
    'Size5',
    'Size6'
  );
  fputcsv($fp, $head);
  foreach ( $store->img_array as $img_item ) {
      $rlt = fputcsv($fp, (array)$img_item);
  }
  fclose($fp);
}