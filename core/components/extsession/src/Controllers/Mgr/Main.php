<?php

namespace ExtSession\Controllers\Mgr;

use ExtSession\ExtSessionConfig;

/**
 * Main Controller
 *
 * @package ExtSession
 * @subpackage Controller
 */
class Main extends AbstractController
{
    public function loadCustomCssJs()
    {
        $language = $this->modx->getOption('cultureKey', null, 'en');
        $options = array_merge([], [
            'connectorUrl' => ExtSessionConfig::CONNECTOR_URL . '?page=main',
            'language' => $language,
            'grid_session_fields' => ['id', 'access', 'user_bot', 'user_agent', 'user_id', 'Profile.fullname', 'user_ip'],
        ]);

        $options = json_encode($options, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->addHtml("<script type='text/javascript'>extsession.config={$options};</script>");

        $assetsUrl = ExtSessionConfig::ASSETS_URL . 'mgr/';
        $jsUrl = $assetsUrl . 'js/';
        $cssUrl = $assetsUrl . 'css/';

        $this->addCss($assetsUrl . 'vendor/gridfilters/css/GridFilters.css');
        $this->addCss($cssUrl . 'main.css');

        $this->addLastJavascript($assetsUrl . 'vendor/gridfilters/GridFilters.js');
        $this->addLastJavascript($assetsUrl . 'vendor/gridfilters/filter/Filter.js');
        $this->addLastJavascript($assetsUrl . 'vendor/gridfilters/filter/StringFilter.js');

        $this->addJavascript($jsUrl . 'extsession.js');
        $this->addJavascript($jsUrl . 'misc/tools.js');
        $this->addJavascript($jsUrl . 'misc/combo.js');

        $this->addLastJavascript($jsUrl . 'session/session.grid.js');
        $this->addLastJavascript($jsUrl . 'main.panel.js');

        $this->addHtml('<script>Ext.onReady(function() { MODx.add({ xtype: "extsession-panel-main"});});</script>');
    }
}

return Main::class;