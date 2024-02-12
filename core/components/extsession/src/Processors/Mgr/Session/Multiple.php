<?php

namespace ExtSession\Processors\Mgr\Session;

use ExtSession\Processors\Mgr\AbstractMultipleProcessor;
use ExtSession\Model\Session;

class Multiple extends AbstractMultipleProcessor
{
    public $classKey = Session::class;
    public $objectType = Session::class;
    public $primaryKeyField = 'id';

}
