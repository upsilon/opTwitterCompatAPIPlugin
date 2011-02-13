<?php

class twApiActions extends sfActions
{
  public function executeBasicAuth(sfWebRequest $request)
  {
    $response = $this->getResponse();
    $response->setHttpHeader('WWW-Authenticate', 'Basic realm="Please enter your address and password"');
    $response->setStatusCode(401);
    return $this->renderText('401 Unauthorized');
  }

  public function executeError401(sfWebRequest $request)
  {
    $this->getResponse()->setStatusCode(401);
    return $this->renderText('401 Unauthorized');
  }

  public function executeError403(sfWebRequest $request)
  {
    $this->getResponse()->setStatusCode(403);
    return $this->renderText('403 Forbidden');
  }

  public function executeError404(sfWebRequest $request)
  {
    $this->getResponse()->setStatusCode(404);
    return $this->renderText('404 Not Found');
  }
}
