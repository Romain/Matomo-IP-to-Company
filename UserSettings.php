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
 * $settings = new UserSettings();
 * $settings->autoRefresh->getValue();
 * $settings->color->getValue();
 */
class UserSettings extends \Piwik\Settings\Plugin\UserSettings
{
    /** @var Setting */
    public $subscribedToEmailReport;

    protected function init()
    {
        // User setting --> checkbox converted to bool
        $this->autoRefresh = $this->createSubscribedToEmailReportSetting();
    }

    private function createSubscribedToEmailReportSetting()
    {
        return $this->makeSetting('subscribedToEmailReport', $default = false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('IPtoCompany_SubscribeToEmailReport');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('IPtoCompany_WantToReceiveDailyReport');
            // $field->validators[] = new NotEmpty();
        });
    }
}
