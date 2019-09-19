<?php

class TileStore {
  var $img_array = array();

  function add($key, $item) {
    $this->img_array[$key] = $item;
  }

  function get($key) {
    if (array_key_exists($key, $this->img_array)) {
      return $this->img_array[$key];
    }
    return null;
  }
}