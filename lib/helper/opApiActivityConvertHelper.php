<?php

function activity_to_status(ActivityData $activity, $isTermUser = false)
{
  $result = array(
    'created_at' => op_format_date_twitter_compat($activity->created_at),
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
    'profile_image_url' => op_member_image_path($member, true),
    'screen_name' => $member->name,
  );
}

function op_format_date_twitter_compat($date)
{
  return gmdate('D M j H:i:s O Y', strtotime($date));
}

function op_member_image_path(Member $member, $absolute = false)
{
  $profileimg = $member->getImageFileName();
  if (!$profileimg)
  {
    static $noimage = null;
    if ($noimage !== null)
    {
      return $noimage;
    }

    use_helper('opUtil');
    return $noimage = op_image_path('no_image.gif', $absolute);
  }

  use_helper('sfImage');
  return sf_image_path($profileimg, array(), $absolute);
}

