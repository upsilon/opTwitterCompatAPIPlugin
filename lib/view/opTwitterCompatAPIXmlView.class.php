<?php

class opTwitterCompatAPIXmlView extends opTwitterCompatAPIView
{
  protected $domDocument;

  public function renderOutput(array $values)
  {
    $this->domDocument = new DOMDocument('1.0', 'UTF-8');
    $this->arrayToXml($values);
    return $this->domDocument->saveXML();
  }

  protected function arrayToXml(array $array, DOMElement $parentElement = null)
  {
    foreach ($array as $key => $data)
    {
      if (is_array($data))
      {
        if (0 !== count($data))
        {
          $elementKeys = array_keys($data);
          $elementKey  = $elementKeys[0];
        }

        if (is_numeric($key) && 0 !== count($data))
        {
          if (1 < count($data))
          {
            throw new RuntimeException();
          }

          $dData = $data[$elementKey];

          if (is_numeric($elementKey))
          {
            throw new RuntimeException();
          }

          if (is_array($dData))
          {
            $element = $this->domDocument->createElement($elementKey);
            $this->arrayToXml($dData, $element);
          }
          else
          {
            $element = $this->domDocument->createElement($elementKey, $dData);
          }
        }
        else
        {
          $element = $this->domDocument->createElement($key);
          if ('statuses' === $key)
          {
            $element->setAttribute('type', 'array');
          }
          $this->arrayToXml($data, $element);
        }
      }
      else
      {
        $element = $this->domDocument->createElement($key, $data);
      }

      if ($parentElement)
      {
        $parentElement->appendChild($element);
      }
      else
      {
        $this->domDocument->appendChild($element);
      }
    }
  }

  static public function getContentType()
  {
    return 'application/xml';
  }
}
