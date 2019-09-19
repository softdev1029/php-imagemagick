<?php

include 'constant.php';
include 'model/ImageItem.php';
include 'model/TileItem.php';
include 'model/OrderItem.php';
include 'model/TileStore.php';
include 'model/OrderStore.php';
include 'util/log_util.php';
include 'util/file_util.php';
include 'util/img_util.php';

$tile_store = new TileStore();
$order_store = new OrderStore();

init_dir();

// read the tile CSV file and initialize the store of tiles
read_tile_csv($tile_store, 0);

//
read_order_csv($tile_store, $order_store, 0);

//
merge_orders($order_store, 0);