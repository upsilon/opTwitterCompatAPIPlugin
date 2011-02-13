<?php

class twAccountActions extends opTwitterCompatAPIActions
{
  public function executeVerifyCredentials(sfWebRequest $request)
  {
    $memberId = $this->getMemberId();
    $this->member = Doctrine::getTable('Member')->find($memberId);
  }
}
