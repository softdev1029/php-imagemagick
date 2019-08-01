<?php

class Store {
  var $img_array = array();

  function add($item) {
    array_push($this->img_array, $item);
  }
}