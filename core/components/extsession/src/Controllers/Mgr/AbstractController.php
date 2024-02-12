<?php

namespace ExtSession\Controllers\Mgr;

use MODX\Revolution\modExtraManagerController;
use ExtSession\ExtSession;
use ExtSession\ExtSessionConfig;

/**
 * The mgr manager controller for ExtSession.
 *
 */
abstract class AbstractController extends modExtraManagerController
{
    /** @var ExtSession $extsession */
    public $extsession;

    /**
     * The version hash
     *
     * @var string $versionHash
     */
    public $versionHash;

    /**
     * @return void
     */
    public function initialize()
    {
       // $this->extsession = $this->modx->services->get('extsession');
        $this->versionHash = '?v=' . dechex(crc32(ExtSessionConfig::VERSION));
        $this->versionHash = '?v=' . dechex(time());
        parent::initialize();
    }

    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['extsession:default'];
    }

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }

    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('extsession');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="extsession-panel-main-div"></div>';

        return '';
    }

    /**
     * @param string $script
     */
    public function addCss($script)
    {
        parent::addCss($script . $this->versionHash);
    }

    /**
     * @param string $script
     */
    public function addJavascript($script)
    {
        parent::addJavascript($script . $this->versionHash);
    }

    /**
     * @param string $script
     */
    public function addLastJavascript($script)
    {
        parent::addLastJavascript($script . $this->versionHash);
    }

}