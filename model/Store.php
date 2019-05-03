<?php

class Store {
  var $img_array = array();
  var $proportion_array = array(
    12,
    20,
    28,
    35,
    48,);

  function add($item) {
    array_push($this->img_array, $item);
  }
}