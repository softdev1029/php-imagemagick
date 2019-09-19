<?php

class OrderStore {
  var $orders = array();

  function add($key, $item) {
    //$this->orders[$key] = $item;
    array_push($this->orders, $item);
  }

  function get($key) {
    if (array_key_exists($key, $this->orders)) {
      return $this->orders[$key];
    }
    return null;
  }
}