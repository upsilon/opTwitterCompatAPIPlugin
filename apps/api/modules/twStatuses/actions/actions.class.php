<?php

class twStatusesActions extends opTwitterCompatAPIActions
{
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

    $params = $this->validateParameters($validators, $default);

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
    $params = $this->validateParameters($validators, array('term_user' => false));

    $this->activity = Doctrine::getTable('ActivityData')->updateActivity($memberId, $params['status']);
    $this->term_user = $params['term_user'];
  }
}
