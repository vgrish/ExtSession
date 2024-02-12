<?php

namespace ExtSession\Processors\Mgr\Session;

use xPDO\Om\xPDOQuery;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use ExtSession\Model\Session;
use ExtSession\Processors\Mgr\AbstractGetListProcessor;

class GetList extends AbstractGetListProcessor
{
    public $objectType = Session::class;
    public $classKey = Session::class;
    public $classAlias = 'Session';
    public $defaultSortField = 'access';
    public $defaultSortDirection = 'DESC';

    protected $searchFields = ['user_ip', 'user_agent', 'Profile.fullname', 'Profile.email', 'Profile.phone'];

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $alias = $this->getClassAlias();

        $c->leftJoin(modUserProfile::class, 'Profile');
        $c->select($this->modx->getSelectColumns($this->classKey, $alias, '', ['data'], true));
        $c->select($this->modx->getSelectColumns(modUserProfile::class, 'Profile', 'Profile.', ['fullname'], false));

        return parent::prepareQueryBeforeCount($c);
    }

    /** {@inheritDoc} */
    public function prepareArray(array $row, $toPls = true)
    {
        $row = parent::prepareArray($row, $toPls);

        $row['actions'] = [];
        // Remove
        $row['actions'][] = [
            'cls' => '',
            'icon' => "icon icon-trash-o red",
            'title' => $this->modx->lexicon('extsession_action_remove'),
            'multiple' => $this->modx->lexicon('extsession_action_remove'),
            'action' => 'Remove',
            'menu' => true,
        ];

        return $row;
    }
}