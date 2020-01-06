<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\IPtoCompany;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\API\Request;

/**
 * API for plugin IPtoCompany
 *
 * @method static \Piwik\Plugins\IPtoCompany\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getCompanies($idSite, $period, $date, $segment = false)
    {
        $response = Request::processRequest('Live.getLastVisitsDetails', [
            'idSite'        => $idSite,
            'period'        => $period,
            'date'          => $date,
            // 'token_auth'    => $_ENV['AUTH_TOKEN']
        ]);

        $result = $response->getEmptyClone($keepFilters = false);

        // Prepare an array containing the list of IP addresses to avoid multiple calls for the same IP address
        $ipList = [];

        foreach ($response->getRows() as $visitRow) {
            $visitIp = $visitRow->getColumn('visitIp');

            // try and get the row in the result DataTable for the IP
            $ipRow = $result->getRowFromLabel($visitIp);

            // Get the company name based on the IP
            $ipList = $this->getCompanyName($visitIp, $ipList);
            $companyName = $ipList[$visitIp];

            // if there is no row for this browser, create it
            if ($ipRow === false) {
                $result->addRowFromSimpleArray(array(
                    'IP'     => $visitIp,
                    'company'   => $companyName,
                    'last_visit_time'   => $visitRow->getColumn('lastActionDateTime'),
                    'type'   => $visitRow->getColumn('visitorType'),
                    'nb_visits'   => $visitRow->getColumn('visitCount'),
                    'last_visit_duration'   => $visitRow->getColumn('visitDurationPretty'),
                    'referrer_type'   => $visitRow->getColumn('referrerType'),
                    'referrer_name'   => $visitRow->getColumn('referrerName'),
                    'device'   => $visitRow->getColumn('deviceType'),
                    'country'   => $visitRow->getColumn('country'),
                    'city'   => $visitRow->getColumn('city'),
                ));
            }

            // if there is a row, increment the counter
            else {
                // $counter = $browserRow->getColumn('nb_visits');
                // $browserRow->setColumn('nb_visits', $counter + 1);
            }
        }

        return $result;
    }

    /**
     * Another example method that returns a data table.
     * @param string    $ip
     * @param array     $ipList
     * @return array
     */
    private function getCompanyName($ip, $ipList)
    {
        if(!$ipList[$ip]) {
            $companyName = gethostbyaddr($ip);
            $ipList[$ip] = $companyName === $ip ? "-" : $companyName;
        }

        return $ipList;
    }
}
