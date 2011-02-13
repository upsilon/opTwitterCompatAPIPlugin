<?php

class twStatusesActions extends opTwitterCompatAPIActions
{
  public function forward404($message = null)
  {
    $this->forward('twApi', 'error404');
  }

  protected function validate($validators, $default = array())
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

  public function executeHomeTimeline(sfWebRequest $request)
  {
    $memberId = $this->getMemberId();
    $validators = array(
      'since_id'         => new sfValidatorInteger(array('required' => false, 'min' => 1)),
      'max_id'           => new sfValidatorInteger(array('required' => false, 'min' => 1)),
      'count'            => new sfValidatorInteger(array('required' => false, 'max' => 200, 'min' => 1)),
      'page'             => new sfValidatorInteger(array('required' => false, 'min' => 1)),
      'term_user'        => new sfValidatorBoolean(array('required' => false)),
    );

    $default = array(
      'count'            => 20,
      'page'             => 1,
      'term_user'        => false
    );

    $params = $this->validate($validators, $default);

    $q = Doctrine::getTable('ActivityData')->createQuery();
    $dql = 'member_id = ?';
    $dqlParams = array($memberId);
    $friendIds = Doctrine::getTable('MemberRelationship')->getFriendMemberIds($memberId);
    $flags = Doctrine::getTable('ActivityData')->getViewablePublicFlags(ActivityDataTable::PUBLIC_FLAG_FRIEND);
    if ($friendIds)
    {
      $query = new Doctrine_Query();
      $query->andWhereIn('member_id', $friendIds);
      $query->andWhereIn('public_flag', $flags);

      $dql .= ' OR '.implode(' ', $query->getDqlPart('where'));
      $dqlParams = array_merge($dqlParams, $friendIds, $flags);
    }
    $q->andWhere('('.$dql.')', $dqlParams);
    $q->andWhere('in_reply_to_activity_id IS NULL');

    $q->limit($params['count']);
    if ($params['since_id'])
    {
      $q->andWhere('id > ?', $params['since_id']);
    }
    if ($params['max_id'])
    {
      $q->andWhere('id <= ?', $params['max_id']);
    }

    if (1 !== $params['page'])
    {
      $q->offset(($params['page'] - 1) * $params['count']);
    }

    $this->activities = $q->orderBy('id DESC')->execute();
    $this->term_user = $params['term_user'];
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $memberId = $this->getMemberId();
    $validators = array(
      'status'    => new opValidatorString(array('required' => true, 'trim' => true, 'max_length' => 140)),
      'term_user' => new sfValidatorBoolean(array('required' => false)),
    );
    $params = $this->validate($validators, array('term_user' => false));

    $this->activity = Doctrine::getTable('ActivityData')->updateActivity($memberId, $params['status']);
    $this->term_user = $params['term_user'];
  }
}
