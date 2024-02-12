<?php

namespace ExtSession\Processors\Mgr\Session;

use ExtSession\Processors\Mgr\AbstractTruncateProcessor;
use ExtSession\Model\Session;

class Truncate extends AbstractTruncateProcessor
{
    public $classKey = Session::class;
    public $objectType = Session::class;

}