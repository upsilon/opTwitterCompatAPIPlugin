<?php

class opTwitterCompatAPIActions extends sfActions
{
  public function execute($request)
  {
    $format = $this->getRequest()->getParameter('sf_format');
    $viewClass = 'opTwitterCompatAPI'.ucfirst(strtolower($format));

    $moduleName = strtolower($this->moduleName);
    sfConfig::set('mod_'.$moduleName.'_view_class', $viewClass);

    $viewClass .= 'View';
    $this->forward404Unless(class_exists($viewClass));

    $contentType = $viewClass::getContentType();
    $this->getResponse()->setContentType($contentType);

    return parent::execute($request);
  }
}
