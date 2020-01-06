<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IPtoCompany;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Plugin;
use Piwik\SettingsPiwik;
use Piwik\Widget\WidgetsList;
use \Exception;

class IPtoCompany extends \Piwik\Plugin
{
    /**
     * @see https://developer.matomo.org/guides/extending-database
     */
    public function activate()
    {
        try {
            $sql = "CREATE TABLE " . Common::prefixTable('ip_to_company') . " (
                        id INTEGER NOT NULL AUTO_INCREMENT,
                        ip VARCHAR( 15 ) NOT NULL ,
                        as_number VARCHAR( 10 ) NULL ,
                        as_name VARCHAR( 200 ) NULL ,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
                        PRIMARY KEY ( id )
                    )  DEFAULT CHARSET=utf8 ";
            Db::exec($sql);
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * @see https://developer.matomo.org/guides/extending-database
     */
    public function deactivate()
    {
        Db::dropTables(Common::prefixTable('ip_to_company'));
    }

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            //'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Widget.filterWidgets' => 'filterWidgets'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/IPtoCompany/stylesheets/iptocompany.less";
    }

    /*public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/bower_components/iframe-resizer/js/iframeResizer.min.js";

        $jsFiles[] = "plugins/Marketplace/angularjs/plugins/plugin-name.directive.js";
        $jsFiles[] = "plugins/Marketplace/angularjs/licensekey/licensekey.controller.js";
        $jsFiles[] = "plugins/Marketplace/angularjs/marketplace/marketplace.controller.js";
        $jsFiles[] = "plugins/Marketplace/angularjs/marketplace/marketplace.directive.js";
    }*/

    /**
     * @param WidgetsList $list
     */
    public function filterWidgets($list)
    {
        if (!SettingsPiwik::isInternetEnabled()) {
            $list->remove('IPtoCompany_Companies');
        }
    }
}
