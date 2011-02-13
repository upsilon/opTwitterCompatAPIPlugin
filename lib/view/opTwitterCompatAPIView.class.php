<?php

abstract class opTwitterCompatAPIView extends sfPHPView
{
  public function configure()
  {
    $this->setExtension('.php');

    parent::configure();
  }

  /**
   * Renders the presentation.
   *
   * @param  string $_sfFile  Filename
   * @return string File content
   * @see    sfPHPView::renderFile()
   */
  protected function renderFile($_sfFile)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Render "%s"', $_sfFile))));
    }

    $this->loadCoreAndStandardHelpers();

    // EXTR_REFS can't be used (see #3595 and #3151)
    $vars = $this->attributeHolder->toArray();
    extract($vars);

    // render
    try
    {
      $returnValue = include($_sfFile);
      $output = $this->renderOutput($returnValue);
    }
    catch (Exception $e)
    {
      throw $e;
    }

    return $output;
  }

  abstract protected function renderOutput(array $values);

  static public function getContentType()
  {
    return 'text/plain';
  }
}
