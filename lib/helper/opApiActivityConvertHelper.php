<?php

function activity_to_status(ActivityData $activity, $isTermUser = false)
{
  $result = array(
    'created_at' => $activity->created_at,
    'id' => $activity->id,
    'text' => $activity->body,
  );
  if ($isTermUser)
  {
    $result['user'] = array('id' => $activity->member_id);
  }
  else
  {
    $result['user'] = member_to_user($activity->Member);
  }

  return $result;
}

function member_to_user(Member $member)
{
  return array(
    'id' => $member->id,
    'name' => $member->name,
    'screen_name' => $member->name,
  );
}
