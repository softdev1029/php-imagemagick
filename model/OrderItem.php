<?php

class OrderItem {
  // int
  public $order_num;
  
  // the array of TileItem
  public $items = array();

  function add($key, $item) {
    //$this->items[$key] = $item;
    array_push($this->items, $item);
  }

  function get($key) {
    if (array_key_exists($key, $this->items)) {
      return $this->items[$key];
    }
    return null;
  }
}