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
use Piwik\Common;
use Piwik\Db;
use Piwik\Container\StaticContainer;

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
    protected function init()
    {
        // User setting --> checkbox converted to bool
        $this->subscribedToEmailReport = $this->createSubscribedToEmailReportSetting();
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

    public function getSubscribedToEmailReportValueForUser($userLogin)
    {
        // Sanitize the user login
        $userLogin = htmlspecialchars($userLogin);

        try {
            $sql = "SELECT * FROM " . Common::prefixTable('plugin_setting') . "
                    WHERE plugin_name = 'IPtoCompany'
                    AND setting_name = 'subscribedToEmailReport'
                    AND setting_value = '1'
                    AND user_login = '" . $userLogin . "'";
            $result = Db::fetchAll($sql);
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }

        return count($result) == 1;
    }
}
