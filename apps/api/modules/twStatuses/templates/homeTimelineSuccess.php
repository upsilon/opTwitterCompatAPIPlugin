<?php

use_helper('opApiActivityConvert');

$statuses = array();
foreach ($activities as $activity)
{
  $statuses[] = array(
    'status' => activity_to_status($activity, $term_user),
  );
}

return array('statuses' => $statuses);
