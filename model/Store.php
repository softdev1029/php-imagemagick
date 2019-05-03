<?php

class Store {
  var $img_array = array();
  var $proportion_array = array(
    array('type' => 'w', 'size' => 12),
    array('type' => 'w', 'size' => 20),
    array('type' => 'w', 'size' => 28),
    array('type' => 'w', 'size' => 35),
    array('type' => 'w', 'size' => 48),
    array('type' => 'h', 'size' => 48),
  );

  function add($item) {
    array_push($this->img_array, $item);
  }
}