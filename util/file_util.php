<?php

function get_png_name($svg_name) {
  return str_replace("svg", "png", $svg_name);
}

function get_src_file_path($file_name, $escape=true) {
  if ($escape) {
    return addslashes("" . SRC_DIR . "/" . $file_name . "");
  }
  return "" . SRC_DIR . "/" . $file_name . "";
}

function get_src_tmp_file_path($file_name, $escape=true) {
  if ($escape) {
    return addslashes("" . SRC_TMP_DIR . "/" . $file_name . "");
  }
  return "" . SRC_TMP_DIR . "/" . $file_name . "";
}

function get_dst_file_path($file_name, $proportion = '0') {
  if ($proportion != '0') {
    return addslashes("" . DST_DIR . "/" . $proportion . "_" . $file_name . "");
  }
  return addslashes("" . DST_DIR . "/" . $file_name . "");
}

function get_dst_csv_file_path() {
  $file_name = "output_" . date("Ymd") . ".csv";
  return addslashes(DST_CSV_DIR . "/" . $file_name);
}

function get_desk_file_path($file_name) {
  return addslashes("" . DESK_DIR . "/" . $file_name . "");
}

function get_mockup_file_path($mark, $desk) {
  return addslashes("" . MOCKUP_DIR . "/" . remove_ext($mark) . "_" . $desk . "");
}

function get_ext($file) {
  $arr = explode(".", $file);
  $cnt = count($arr);
  return $arr[$cnt - 1];
}

function remove_ext($file) {
  return str_replace("." . get_ext($file), "", $file);
}

function rename_file($src) {
  $dst = date("Ymd") . "_" . str_replace(" ", "_", $src);
  return $dst;
}

function rename_files(&$store) {
  echo "Copying files to the temporary folder..." . PHP_EOL;
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
      $store->add($img_item);

      $src = get_src_file_path($img_item->src, false);
      $dst = get_src_tmp_file_path($img_item->dst, false);
      copy($src, $dst);
      echo "\tCopy src= $src, dst= $dst" . PHP_EOL;
      $i++;
    }
  }
  echo "Copied." . PHP_EOL . PHP_EOL;
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
    'Size6',
    'Error',
  );
  fputcsv($fp, $head);
  foreach ( $store->img_array as $img_item ) {
      $rlt = fputcsv($fp, (array)$img_item);
  }
  fclose($fp);
}

function deleteDir($dirPath) {
  if (!file_exists($dirPath)) {
    return;
  }

  if (! is_dir($dirPath)) {
      throw new InvalidArgumentException("$dirPath must be a directory");
  }
  if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
  }
  $files = glob($dirPath . '*', GLOB_MARK);
  foreach ($files as $file) {
      if (is_dir($file)) {
          deleteDir($file);
      } else {
          unlink($file);
      }
  }
  rmdir($dirPath);
}

function init_dir() {
  if (!file_exists(SRC_DIR)) {
    debug(sprintf('There is not source image directory: %s', SRC_DIR));
    die();
  }
  
  if (!file_exists(DESK_DIR)) {
    debug(sprintf('There is not desk image directory: %s', DESK_DIR));
    die();
  }

  deleteDir(SRC_TMP_DIR);
  deleteDir(DST_DIR);
  deleteDir(DST_CSV_DIR);
  deleteDir(MOCKUP_DIR);
  
  if (!file_exists(SRC_TMP_DIR) && !mkdir(SRC_TMP_DIR, 0777, true)) {
    debug('Failed to create the temporary directory for image files.');
  }
  
  if (!file_exists(DST_DIR) && !mkdir(DST_DIR, 0777, true)) {
    debug('Failed to create the converted directory for image files.');
  }
  
  if (!file_exists(DST_CSV_DIR) && !mkdir(DST_CSV_DIR, 0777, true)) {
    debug('Failed to create the output directory for CSV files.');
  }

  if (!file_exists(MOCKUP_DIR) && !mkdir(MOCKUP_DIR, 0777, true)) {
    debug('Failed to create the output directory for mockup files.');
  }
}