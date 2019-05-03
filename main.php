<?php

include 'constant.php';
include 'model/ImageItem.php';
include 'model/Store.php';
include 'util/log_util.php';
include 'util/file_util.php';
include 'util/img_util.php';

$store = new Store();

init_dir();

rename_files($store);

change_color_to_black($store);

get_image_dimen($store);

resize_image($store);

make_csv_file($store);

merge_mark_desk($store);

// debug($store->img_array);