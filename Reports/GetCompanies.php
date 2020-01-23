<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IPtoCompany\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Widget\WidgetsList;
use Piwik\Report\ReportWidgetFactory;

use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetCompanies extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('IPtoCompany_Companies');
        $this->dimension     = null;
        $this->documentation = Piwik::translate('');
        $this->subcategoryId = Piwik::translate('IPtoCompany_Companies');

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 26;

        // By default standard metrics are defined but you can customize them by defining an array of metric names
        $this->metrics = [
            'nb_visits'
        ];

        $this->columns = [
            'IP',
            'company',
            'last_visit_time',
            'type',
            'nb_visits',
            'last_visit_duration',
            'referrer_type',
            'referrer_name',
            'device',
            'country',
            'city'
        ];

        // Uncomment the next line if your report does not contain any processed metrics, otherwise default
        // processed metrics will be assigned
        // $this->processedMetrics = array();

        // Uncomment the next line if your report defines goal metrics
        // $this->hasGoalMetrics = true;

        // Uncomment the next line if your report should be able to load subtables. You can define any action here
        // $this->actionToLoadSubTables = $this->action;

        // Uncomment the next line if your report always returns a constant count of rows, for instance always
        // 24 rows for 1-24hours
        // $this->constantRowsCount = true;

        // If a subcategory is specified, the report will be displayed in the menu under this menu item
        // $this->subcategoryId = 'IPtoCompany_Foo';
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        if (!empty($this->dimension)) {
            $view->config->addTranslations(array('label' => $this->dimension->getName()));
        }

        $view->config->show_search = true;
        // $view->requestConfig->filter_sort_column = 'nb_visits';
        // $view->requestConfig->filter_limit = 10';

        $view->config->addTranslation('company', Piwik::translate('IPtoCompany_Company'));
        $view->config->addTranslation('last_visit_time', Piwik::translate('IPtoCompany_LastVisit'));
        $view->config->addTranslation('type', Piwik::translate('IPtoCompany_Type'));
        $view->config->addTranslation('nb_visits', Piwik::translate('IPtoCompany_NumberOfVisits'));
        $view->config->addTranslation('last_visit_duration', Piwik::translate('IPtoCompany_LastVisitDuration'));
        $view->config->addTranslation('referrer_type', Piwik::translate('IPtoCompany_ReferrerType'));
        $view->config->addTranslation('referrer_name', Piwik::translate('IPtoCompany_ReferrerName'));
        $view->config->addTranslation('device', Piwik::translate('IPtoCompany_Device'));
        $view->config->addTranslation('country', Piwik::translate('IPtoCompany_Country'));
        $view->config->addTranslation('city', Piwik::translate('IPtoCompany_City'));

        $view->config->columns_to_display = $this->columns;
    }

    /**
     * Here you can define related reports that will be shown below the reports. Just return an array of related
     * report instances if there are any.
     *
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
        return array(); // eg return array(new XyzReport());
    }

    /**
     * Here we define a method to be able to create a widget with this report.
     */
    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        // we have to do it manually since it's only done automatically if a subcategoryId is specified,
        // we do not set a subcategoryId since this report is not supposed to be shown in the UI
        $widgetsList->addWidgetConfig($factory->createWidget());
    }

    /**
     * A report is usually completely automatically rendered for you but you can render the report completely
     * customized if you wish. Just overwrite the method and make sure to return a string containing the content of the
     * report. Don't forget to create the defined twig template within the templates folder of your plugin in order to
     * make it work. Usually you should NOT have to overwrite this render method.
     *
     * @return string
    public function render()
    {
        $view = new View('@IPtoCompany/showCompanies');
        $view->myData = array();

        return $view->render();
    }
    */

    /**
     * By default your report is available to all users having at least view access. If you do not want this, you can
     * limit the audience by overwriting this method.
     *
     * @return bool
    public function isEnabled()
    {
        return Piwik::hasUserSuperUserAccess()
    }
     */
}
