<?php

namespace ExtSession\Processors\Mgr\Session;

use ExtSession\Processors\Mgr\AbstractRemoveProcessor;
use ExtSession\Model\Session;

class Remove extends AbstractRemoveProcessor
{
    public $classKey = Session::class;
    public $objectType = Session::class;

}