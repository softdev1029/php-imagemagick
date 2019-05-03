<?php

include 'constant.php';
include 'model/ImageItem.php';
include 'model/Store.php';
include 'util/log_util.php';
include 'util/file_util.php';
include 'util/img_util.php';

if (!file_exists(SRC_DIR)) {
  debug(sprintf('There is not source image directory: %s', SRC_DIR));
  die();
}

if (!file_exists(DST_DIR) && !mkdir(DST_DIR, 0777, true)) {
  debug('Failed to create the converted directory for image files.');
}

if (!file_exists(DST_CSV_DIR) && !mkdir(DST_CSV_DIR, 0777, true)) {
  debug('Failed to create the output directory for CSV files.');
}

$store = new Store();

rename_files($store);

change_color_to_black($store);

get_image_dimen($store);

resize_image($store);

make_csv_file($store);

// debug($store->img_array);