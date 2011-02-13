<?php

class opTwitterCompatAPIJsonView extends opTwitterCompatAPIView
{
  protected function renderOutput(array $values)
  {
    return json_encode($this->removeArrayKeys($values));
  }

  protected function removeArrayKeys(array $source)
  {
    $source = array_shift($source);
    if (count($source) > 1)
    {
      return $source;
    }

    $values = array();
    foreach ($source as $value)
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
