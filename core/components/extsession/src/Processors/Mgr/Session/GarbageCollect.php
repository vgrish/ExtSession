<?php

namespace ExtSession\Processors\Mgr\Session;

use ExtSession\Processors\Mgr\AbstractProcessor;
use ExtSession\Model\Session;
use ExtSession\ExtSessionHandler;

class GarbageCollect extends AbstractProcessor
{
    public $classKey = Session::class;
    public $objectType = Session::class;

    public function process()
    {
        if ($sh = new ExtSessionHandler($this->modx)) {
            $sh->gc(0);
        }
        return $this->success('');
    }

}