<?php

include 'constant.php';
include 'model/ImageItem.php';
include 'model/Store.php';
include 'util2/log_util.php';
include 'util2/file_util.php';
include 'util2/img_util.php';

$store = new Store();

// resize_svg('./PatternTile/InputPatterns', 'pattern-01.svg', 1000, 1000);

init_dir();

// copy 4 scaled types (not scaled yet)
init_store($store);

// sale 4 types
// resize_image($store);

// 12 inch
make_12_inch($store);

// rename_files($store);

// change_color_to_black($store);

// get_image_dimen($store);

// resize_image($store);

// merge_mark_desk($store);

// make_csv_file($store);

// debug($store->img_array);