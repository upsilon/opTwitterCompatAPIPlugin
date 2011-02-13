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

  public function forward404($message = null)
  {
    $this->forward('twApi', 'error404');
  }

  protected function validateParameters($validators, $default = array())
  {
    $result = array();
    try
    {
      foreach ($validators as $name => $validator)
      {
        $value = $this->getRequest()->getParameter($name, isset($default[$name]) ? $default[$name] : null);
        $result[$name] = $validator->clean($value);
      }
    }
    catch (sfValidatorError $e)
    {
      $this->forward('twApi', 'error403');
    }

    return $result;
  }

  protected function getMemberIdByBasic()
  {
    if (!isset($_SERVER['PHP_AUTH_USER']))
    {
      $this->forward('twApi', 'basicAuth');
    }
    else
    {
      $email = $_SERVER['PHP_AUTH_USER'];
      $password = md5($_SERVER['PHP_AUTH_PW']);
      $memberConfig = Doctrine::getTable('MemberConfig')->retrieveByNameAndValue('pc_address', $email);
      if ($memberConfig)
      {
        $data = $memberConfig->Member->getConfig('password');
        if ($data === $password)
        {
          return $memberConfig->member_id;
        }
      }
      $this->forward('twApi', 'basicAuth');
    }
  }

  protected function getMemberIdByOAuth()
  {
    require_once 'OAuth.php';
    $consumer = $token = null;

    try
    {
      $req = OAuthRequest::from_request();
      list($consumer, $token) = $this->getServer()->verify_request($req);
    }
    catch (OAuthException $e)
    {
      // do nothing
    }

    if ($consumer)
    {
      $information = Doctrine::getTable('OAuthConsumerInformation')->findByKeyString($consumer->key);
      if ($information)
      {
        $tokenType = $this->getRequest()->getParameter('token_type', 'member');
        if ('member' === $tokenType)
        {
          $accessToken = Doctrine::getTable('OAuthMemberToken')->findByKeyString($token->key, 'access');
          return $accessToken->member_id;
        }
      }
    }

    $this->forward('twApi', 'error401');
  }

  protected function getMemberId()
  {
    if ($this->getRequest()->hasParameter('oauth_token'))
    {
      return $this->getMemberIdByOAuth();
    }
    if (sfConfig::get('op_twcompat_use_basic_auth', false))
    {
      return $this->getMemberIdByBasic();
    }
    $this->forward('twApi', 'error401');
  }
}
