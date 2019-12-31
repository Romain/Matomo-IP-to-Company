<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\IPtoCompany\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Site;
use GuzzleHttp\Client;


/**
 * This class allows you to add your own widget to the Piwik platform. In case you want to remove widgets from another
 * plugin please have a look at the "configureWidgetsList()" method.
 * To configure a widget simply call the corresponding methods as described in the API-Reference:
 * http://developer.piwik.org/api-reference/Piwik/Plugin\Widget
 */
class GetCompanies extends Widget
{
    public function __construct()
    {
        // Set the path of the env file
        $path       = __DIR__.'/../.env';
        $fp         = fopen($path, 'r');

        // Parse the process and add the parameters to the environment
        while (!feof($fp))
        {
            $line = fgets($fp);

            // Process line however you like
            $line = trim($line);

            $values = explode("=", $line);

            // Add to environment
            if(count($values) == 2) {
                $_ENV[$values[0]] = $values[1];
            }
        }

        // Close the process
        fclose($fp);
    }

    public static function configure(WidgetConfig $config)
    {
        /**
         * Set the category the widget belongs to. You can reuse any existing widget category or define
         * your own category.
         */
        $config->setCategoryId('General_Visitors');

        /**
         * Set the subcategory the widget belongs to. If a subcategory is set, the widget will be shown in the UI.
         */
        $config->setSubcategoryId('IPtoCompany_Companies');

        /**
         * Set the name of the widget belongs to.
         */
        $config->setName(Piwik::translate('IPtoCompany_Companies'));

        /**
         * Set the order of the widget. The lower the number, the earlier the widget will be listed within a category.
         */
        $config->setOrder(10);

        /**
         * Optionally set URL parameters that will be used when this widget is requested.
         * $config->setParameters(array('myparam' => 'myvalue'));
         */

        /**
         * Define whether a widget is enabled or not. For instance some widgets might not be available to every user or
         * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
         * set `true` or `false`. If your widget is only available to users having super user access you can do the
         * following:
         *
         * $config->setIsEnabled(\Piwik\Piwik::hasUserSuperUserAccess());
         * or
         * if (!\Piwik\Piwik::hasUserSuperUserAccess())
         *     $config->disable();
         */
         $config->setIsEnabled(!Piwik::isUserIsAnonymous());
         $config->setIsWidgetizable();
    }

    /**
     * This method renders the widget. It's on you how to generate the content of the widget.
     * As long as you return a string everything is fine. You can use for instance a "Piwik\View" to render a
     * twig template. In such a case don't forget to create a twig template (eg. myViewTemplate.twig) in the
     * "templates" directory of your plugin.
     *
     * @return string
     */
    public function render()
    {
        Piwik::checkUserIsNotAnonymous();
        $template = 'showCompanies';

        // Get the ID of the current site
        $idSite = Common::getRequestVar('idSite');
        // $site = new Site($idSite);

        // Get the selected period and date(s)
        $period = Common::getRequestVar('period');
        $date   = Common::getRequestVar('date');

        // Send a request to the API to get the visits during the selected period

        return $this->renderTemplate($template, [
            "api_token" => $_ENV['AUTH_TOKEN'],
            "period"    => $period,
            "date"      => $date
        ]);
    }

}
