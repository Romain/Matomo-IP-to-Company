<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IPtoCompany;

use Piwik\Piwik;
use Piwik\Site;
use Piwik\API\Request;
use Piwik\Settings\Setting;
use Piwik\Container\StaticContainer;

class Tasks extends \Piwik\Plugin\Tasks
{
    private $staticContainer;

    public function __construct(StaticContainer $staticContainer, $settings = [])
    {
        $this->staticContainer = $staticContainer;
    }

    public function schedule()
    {
        foreach (\Piwik\Site::getSites() as $site) {
            // Foreach website, send a report by email to each user allowed to get stats of the concerned website
            $this->daily('getListOfCompaniesThatVisitedWebsiteYesterday', $site['idsite']);
        }
    }

    public function getListOfCompaniesThatVisitedWebsiteYesterday($siteId)
    {
        $logger     = $this->staticContainer->getContainer()->get('Psr\Log\LoggerInterface');
        $siteName   = Site::getNameFor($siteId);
        $recipients = $this->getAllUsersEmailsForSite($siteId);
        $superUsers = $this->getSuperUsersEmails();

        $companies  = \Piwik\API\Request::processRequest('IPtoCompany.getCompanies', [
            'idSite'    => $siteId,
            'period'    => 'day',
            // 'date'      => '2019-12-17'
            'date'      => 'yesterday'
        ]);

        // Generate the HTML
        $html = $this->convertCompaniesDataTableToHTML($companies);

        if(!empty($superUsers) && !empty($recipients)) {
            $mail = new \Piwik\Mail();
            $mail->setFrom($superUsers[0]);
            $mail->setReplyTo($superUsers[0]);
            $logger->info("IPtoCompany: Email sent from ".$superUsers[0]." for ".$siteName);

            foreach ($recipients as $recipient) {
                $mail->addTo($recipient);
                $logger->info("IPtoCompany: Email sent to ".$recipient." for ".$siteName);
            }

            $mail->setSubject( Piwik::translate('IPtoCompany_CompaniesReportSubject', $siteName) );

            try {
                $mail->setWrappedHtmlBody($html);
            } catch (Exception $e) {
                $logger->error("IPtoCompany: An error occured while sending the email: ".$e->message());
                throw $e;
            }

            $mail->send();
        }

        return;
    }

    /**
     * For the supplied website, get the emails of the users that have view access.
     *
     * @param string the site ID
     *
     * @return array    The returned array has the format
     *                    array(email1, email2, ...)
     */
    private function getAllUsersEmailsForSite($siteId)
    {
        $result     = [];
        $userSettings = new \Piwik\Plugins\IPtoCompany\UserSettings();

        // Get the users with a view access
        $response = Request::processRequest('UsersManager.getUsersWithSiteAccess', [
            'idSite' => $siteId,
            'access' => 'view'
        ]);

        foreach ($response as $user) {
            $subscribedToEmailReport = $userSettings->getSubscribedToEmailReportValueForUser($user['login']);

            if($subscribedToEmailReport) {
                $result[] = $user['email'];
            }
        }

        // Get the users with a write access
        $response = Request::processRequest('UsersManager.getUsersWithSiteAccess', [
            'idSite' => $siteId,
            'access' => 'write'
        ]);

        foreach ($response as $user) {
            $subscribedToEmailReport = $userSettings->getSubscribedToEmailReportValueForUser($user['login']);

            if($subscribedToEmailReport) {
                $result[] = $user['email'];
            }
        }

        // Get the users with admin access
        $response = Request::processRequest('UsersManager.getUsersWithSiteAccess', [
            'idSite' => $siteId,
            'access' => 'admin'
        ]);

        foreach ($response as $user) {
            $subscribedToEmailReport = $userSettings->getSubscribedToEmailReportValueForUser($user['login']);

            if($subscribedToEmailReport) {
                $result[] = $user['email'];
            }
        }

        // Get the users with superuser access
        $response   = Request::processRequest('UsersManager.getUsersHavingSuperUserAccess', []);

        foreach ($response as $superUser) {
            $subscribedToEmailReport = $userSettings->getSubscribedToEmailReportValueForUser($superUser['login']);

            if($subscribedToEmailReport) {
                $result[] = $superUser['email'];
            }
        }

        return $result;
    }

    /**
     * Get the email address of the super user.
     *
     * @return array    The returned array has the format
     *                    array(email1, email2, ...)
     */
    private function getSuperUsersEmails()
    {
        $response   = Request::processRequest('UsersManager.getUsersHavingSuperUserAccess', []);
        $result     = [];

        foreach ($response as $superUser) {
            $result[] = $superUser['email'];
        }

        return $result;
    }

    /**
     * Get the email address of the super user.
     *
     * @param array      The list of companies
     *
     * @return string    The generated HTML
     */
    private function convertCompaniesDataTableToHTML($companies)
    {
        $html = "<p>" . Piwik::translate('IPtoCompany_Hi') . "</p>"
            ."<p>" . Piwik::translate('IPtoCompany_FindBelowCompaniesReport') . "</p>"
            ;

        $rows = $companies->getRows();

        if(!empty($rows)) {
            $html .= "<p>"
            ."<table style='border: solid 2px #000; width: 100%;'>"
            ."<thead>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>IP</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_Company') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_LastVisit') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_Type') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_NumberOfVisits') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_LastVisitDuration') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_ReferrerType') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_ReferrerName') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_Device') . "</td>"
            ."<td style='border-right: solid 1px #000; border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_Country') . "</td>"
            ."<td style='border-bottom: solid 1px #000;'>" . Piwik::translate('IPtoCompany_City') . "</td>"
            ."</thead>";

            foreach ($rows as $row) {
                $columns    = $row->getColumns();
                $counter    = 0;
                $nbColumns  = count($columns);

                $html      .= "<tr>";

                foreach ($columns as $key => $value) {
                    $counter++;
                    $styles = "border-bottom: solid 1px #DEDEDE;";

                    if($counter < $nbColumns) {
                        $styles .= " border-right: solid 1px #DEDEDE;";
                    }

                    $html .= "<td style='" . $styles . "'>" . $value . "</td>";
                }

                $html .= "</tr>";
            }

            $html .= "</table></p>";
        }
        else {
            $html .= "<p style='text-align: center; margin-top: 40px; margin-bottom: 40px; font-weight: bold;'><em>" . Piwik::translate('IPtoCompany_NoOneVisitedWebsiteYesterday') . "</em></p>";
        }

        $html .= "<br />"
            ."<p>" . Piwik::translate('IPtoCompany_HaveANiceDay') . "</p>";

        return $html;
    }
}
