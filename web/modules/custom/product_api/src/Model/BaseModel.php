<?php

namespace Drupal\product_api\Model;

class BaseModel {
  public function __construct($arr) {
    foreach ($arr as $key => $val) {
      $this->$key = $val;
    }
  }
}
