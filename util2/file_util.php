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
  return addslashes("" . MOCKUP_DIR . "/" . $mark . "_" . $desk . "");
}

function check_ext($file) {
  $ext = get_ext($file);
  if ($ext == 'SVG' || $ext == "svg") {
    return false;
  }
  return true;
}

function get_ext($file) {
  $arr = explode(".", $file);
  $cnt = count($arr);
  return $arr[$cnt - 1];
}

function rename_file_with_scale($src, $scale) {
  $ext = get_ext($src);
  $dst = str_replace("." . $ext, "(" . $scale . ")." . $ext, $src);
  return $dst;
}

function rename_file_with_12_inch($src) {
  $ext = get_ext($src);
  $dst = str_replace("." . $ext, "-12x12." . $ext, $src);
  return $dst;
}

function rename_final($src) {
  $ext = get_ext($src);
  $dst = str_replace("." . $ext, "-final." . $ext, $src);
  return $dst;
}

function init_store(&$store) {
  echo "Initializing Store of Images ..." . PHP_EOL;
  $dir_name = SRC_DIR;
  $dir = new DirectoryIterator($dir_name);

  echo "\tLopping the input CSV file: " . SRC_DIR . "/" . SRC_CSV . PHP_EOL;
  $row = 1;
  if (($handle = fopen(SRC_DIR . "/" . SRC_CSV, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $num = count($data);
          $row++;
          
          $src = $data[0];

          if (!file_exists(get_src_file_path($src, false))) {
            echo "\t\tFile not exist: " . $src . PHP_EOL;
            continue;
          }
          echo "\t\tFile: " . $src . PHP_EOL;

          $img_item = new ImageItem();
          $img_item->src = $src;
          $img_item->dst = array();
          $img_item->scale = array();

          $src_path = get_src_file_path($img_item->src, false);

          // change density
          change_density($src_path);

          foreach ($data as $scale) {
            if ($scale == $src) {
              continue;
            }
            $dst = rename_file_with_scale($src, $scale);
            array_push($img_item->dst, $dst);
            array_push($img_item->scale, $scale);
            
            $dst = get_dst_file_path($dst, false);
            copy($src_path, $dst);
            echo "\t\t Copied file: src=" . $src_path . ", dst=" . $dst . PHP_EOL;
          }

          $store->add($img_item);
      }
      fclose($handle);
  }

  echo "Initialized Store of Images." . PHP_EOL . PHP_EOL;
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
  echo "Initializing Directories ..." . PHP_EOL;
  if (!file_exists(SRC_DIR)) {
    echo "\tThere is not source image directory: " . SRC_DIR . PHP_EOL;
    die();
  }
  echo "\tFound the source image directory: " . SRC_DIR . PHP_EOL;

  deleteDir(DST_DIR);
  echo "\tDeleted the old directory: " . DST_DIR . PHP_EOL;
  
  if (!file_exists(DST_DIR) && !mkdir(DST_DIR, 0777, true)) {
    echo "\tFailed to create the converted directory for image files." . PHP_EOL;
  }
  echo "\tMade the destination directory: " . DST_DIR . PHP_EOL;

  echo "Initialized Directories." . PHP_EOL . PHP_EOL;
}

function read_csv($src) {
  $row = 1;
  if (($handle = fopen($src, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $num = count($data);
          echo "$num fields in line $row: \n";
          $row++;
          for ($c=0; $c < $num; $c++) {
              echo "\t" . $data[$c] . "\n";
          }
      }
      fclose($handle);
  }
}