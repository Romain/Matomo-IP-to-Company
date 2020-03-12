<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IPtoCompany;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NotEmpty;

/**
 * Defines Settings for IPtoCompany.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $ipInfoAccessToken;

    /** @var Setting */
    public $cacheLifeTimeForResults;

    protected function init()
    {
        // Create a setting to store the IPInfo API token
        $this->ipInfoAccessToken = $this->createIpInfoAccessTokenSetting();
        $this->cacheLifeTimeForResults = $this->createCacheLifeTimeForResultsSetting();
    }

    private function createIpInfoAccessTokenSetting()
    {
        return $this->makeSetting('ipInfoAccessToken', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('IPtoCompany_YourIPInfoAccessToken');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('IPtoCompany_PasteYourAccessToken');
            // $field->validators[] = new NotEmpty();
        });
    }

    private function createCacheLifeTimeForResultsSetting()
    {
        return $this->makeSetting('cacheLifeTimeForResults', $default = 2, FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = Piwik::translate('IPtoCompany_LifeTimeOfCacheForResultsInWeeks');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('IPtoCompany_PasteYourAccessToken');
            // $field->validators[] = new NotEmpty();
        });
    }
}
