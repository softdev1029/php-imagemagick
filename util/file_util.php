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

/**
 * Read the tile CSV file.
 * Then construct the tile store.
 */
function read_tile_csv(&$tile_store, $level) {
  echo indent($level) . "Reading the tile CSV and initializing the store of tiles ..." . PHP_EOL;
  $dir_name = SRC_DIR;
  $dir = new DirectoryIterator($dir_name);

  echo indent($level+1) . "Loopping records in the tile CSV file: " . SRC_DIR . "/" . TILE_CSV . PHP_EOL;
  $row = 1;
  if (($handle = fopen(SRC_DIR . "/" . TILE_CSV, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $num = count($data);
          $row++;
          
          $src = $data[0];
          $src_path = get_src_file_path($src, false);

          if (!file_exists($src_path)) {
            echo indent($level+2) . "File not exist: " . $src . PHP_EOL;
            continue;
          }
          echo indent($level+2) . "File: " . $src . PHP_EOL;

          $img_item = new ImageItem();
          $img_item->src = $src;
          $img_item->sku_id = $data[1];
          $img_item->dst = array();
          $img_item->scale = array();

          // change density
          change_density($src_path, $level+3);

          foreach ($data as $key => $scale) {
            // 1st is filename, 2nd is SKU
            if ($key == 0 || $key == 1) {
              continue;
            }
            $dst = rename_file_with_scale($src, $scale);
            array_push($img_item->dst, $dst);
            array_push($img_item->scale, $scale);
            
            $dst_path = get_dst_file_path($dst, false);
            copy($src_path, $dst_path);
            echo indent($level+2) . "Copied file: src=" . $src_path . ", dst=" . $dst_path . PHP_EOL;
          }

          $tile_store->add($img_item->sku_id, $img_item);
      }
      fclose($handle);
  }

  echo indent($level) . "Initialized the store of tiles." . PHP_EOL . PHP_EOL;
}

function parse_tile_info($info, &$tile_item, $level) {

  //echo indent($level) . "Parsing the tile info: $info ..." . PHP_EOL;
  $slice = explode("-", $info);
  // must be the format of P-HTV-G-0001-100-12x12
  if (count($slice) < 6) {
    return -1;
  }
  $tile_item->material = $slice[1] . "-" . $slice[2];
  $tile_item->sku_id = $slice[3];
  $tile_item->scale = $slice[4];

  // width, height

  $wh = $slice[5];
  //echo indent($level) . "wh=$wh" . PHP_EOL;
  $wh_slice = explode("x", $wh);

  // must be the format of 12X12
  if (count($wh_slice) < 2) {
    return -2;
  }
  $tile_item->w = $wh_slice[0];
  $tile_item->h = $wh_slice[1];
  return 0;
}

function read_order_csv(&$tile_store, &$order_store, $level) {
  echo indent($level) . "Initializing the store of order ..." . PHP_EOL;
  $dir_name = SRC_DIR;
  $dir = new DirectoryIterator($dir_name);

  echo indent($level+1) . "Loopping the order CSV file: " . SRC_DIR . "/" . ORDER_CSV . PHP_EOL;
  $row = 1;
  $old_order_num = -1;
  if (($handle = fopen(SRC_DIR . "/" . ORDER_CSV, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $num = count($data);
          $row++;
          
          $order_num = $data[0];
          if ($old_order_num != $order_num) {
            $order_item = new OrderItem();
            $order_item->order_num = $order_num;
            $old_order_num = $order_num;

            echo indent($level+2) . "Order #$order_num---------" . PHP_EOL;

            $order_store->add($order_num, $order_item);
          }
          $tile_item = new TileItem();
          $tile_item->quantity = $data[1];

          $res = parse_tile_info($data[2], $tile_item, $level+4);
          if ($res < 0) {
            echo indent($level+3) . "Tile info is incorrect: $res, skipped." . PHP_EOL;
            continue;
          }

          echo indent($level+3) . "Tile: qty=$tile_item->quantity, material=$tile_item->material, sku_id=$tile_item->sku_id, scale=$tile_item->scale, w=$tile_item->w, h=$tile_item->h" . PHP_EOL;

          $tile = $tile_store->get($tile_item->sku_id);
          if (!isset($tile)) {
            echo indent($level+3) . "Tile file (sku_id=$tile_item->sku_id) doesn't exist, skipped." . PHP_EOL;
            continue;
          }
          $tile_item->src = $tile->src;
          $src_path = get_src_file_path($tile->src, false);

          // change density
          change_density($src_path, $level+4);

          $dst = rename_file_with_scale($tile->src, $tile_item->scale);
          $tile_item->dst = $dst;
          $dst_path = get_dst_file_path($dst, false);
          copy($src_path, $dst_path);
          echo indent($level+4) . "Copied file: src=" . $src_path . ", dst=" . $dst_path . PHP_EOL;

          // scaling each tile
          resize_image($dst, $tile_item->scale, $level+4);

          // rotate
          rotate_image($dst, -90, $level+4);
          
          // make target inch
          make_target_inch($dst, $tile_item->w, $level+4);

          $order_item->add($tile_item->sku_id, $tile_item);
      }
      fclose($handle);
  }

  echo indent($level) . "Initialized the store of order." . PHP_EOL . PHP_EOL;
}

function merge_orders(&$order_store, $level) {
  echo indent($level) . "Merging orders ..." . PHP_EOL;
  for ($i = 0; $i < count($order_store->orders); $i++) {
    merge_order($order_store->orders[$i], $level+1);
  }
  echo indent($level) . "Merged all orders." . PHP_EOL . PHP_EOL;
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