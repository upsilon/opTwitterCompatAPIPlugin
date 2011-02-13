<?php

class opTwitterCompatAPIJsonView extends opTwitterCompatAPIView
{
  protected function renderOutput(array $values)
  {
    return json_encode($this->removeArrayKeys($values));
  }

  protected function removeArrayKeys(array $source)
  {
    $values = array();
    foreach (array_shift($source) as $value)
    {
      $values[] = array_shift($value);
    }

    return $values;
  }

  static public function getContentType()
  {
    return 'application/json';
  }
}
