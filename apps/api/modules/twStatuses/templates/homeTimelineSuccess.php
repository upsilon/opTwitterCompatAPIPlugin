<?php

$statuses = array();
foreach ($activities as $activity)
{
  $statuses[] = array(
    'status' => opActivityDataConverter::activityToStatus($activity, $term_user),
  );
}

return array('statuses' => $statuses);
