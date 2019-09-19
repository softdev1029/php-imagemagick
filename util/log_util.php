<?php

function debug($str) {
  var_dump($str);
}

function indent($level) {
  $indent = "";
  for ($i = 0; $i < $level; $i++) {
    $indent .= "\t";
  }
  return $indent;
}