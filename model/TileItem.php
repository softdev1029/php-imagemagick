<?php

/**
 * This TileItem represents an element which is included in an order.
 */
class TileItem {
  public $quantity;
  
  // string (ex: ADH-S)
  public $material;

  // string (ex: 0001)
  public $sku_id;

  // int (ex: 70 means 70% of the original tile)
  public $scale;

  // int - width in inches
  public $w;

  // int - height in inches
  public $h;

  // original file name
  public $src;

  // scaled file name
  public $dst;
}