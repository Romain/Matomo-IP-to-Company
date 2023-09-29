<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\IPtoCompany;

use Piwik\Db;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\API\Request;
use Piwik\Plugins\IPtoCompany\Libraries\IPInfo;
use Piwik\Container\StaticContainer;
use \Exception;

const EMPTY_HOSTNAME = "-";

/**
 * API for plugin IPtoCompany
 *
 * @method static \Piwik\Plugins\IPtoCompany\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public $cacheLifeTimeForResults;
    private $staticContainer;

    public function __construct(StaticContainer $staticContainer)
    {
        // Get the access token
        $systemSettings = new \Piwik\Plugins\IPtoCompany\SystemSettings();
        $cacheLifeTimeForResults = $systemSettings->cacheLifeTimeForResults->getValue();
        $this->cacheLifeTimeForResults = $cacheLifeTimeForResults <= 0 ? 2 : $cacheLifeTimeForResults;
        $this->staticContainer = $staticContainer;
    }

    /**
     * Returns a data table with the visits.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @param int $filterLimit
     * @return DataTable
     */
    public function getCompanies($idSite, $period, $date, $segment = false, $filterLimit = 200)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $logger = $this->staticContainer->getContainer()->get(\Piwik\Log\LoggerInterface::class);

        $response = Request::processRequest('Live.getLastVisitsDetails', [
            'idSite'                => $idSite,
            'period'                => $period,
            'date'                  => $date,
            'segment'               => $segment,
            'flat'                  => FALSE,
            'doNotFetchActions'     => FALSE,
            'countVisitorsToFetch'  => $filterLimit
            // 'token_auth'    => $_ENV['AUTH_TOKEN']
        ]);
        $response->applyQueuedFilters();

        $result = $response->getEmptyClone($keepFilters = false);

        // Prepare an array containing the list of IP addresses to avoid multiple calls for the same IP address
        $ipList = [];

        // Get the list of IPs saved in the DB
        $dbList = \Piwik\API\Request::processRequest('IPtoCompany.getStoredCompanies', []);

        foreach ($response->getRows() as $visitRow) {
            $visitIp = $visitRow->getColumn('visitIp');

            // try and get the row in the result DataTable for the IP
            $ipRow = $result->getRowFromLabel($visitIp);

            // Get the company name based on the IP
            $ipList = $this->getIPDetails($visitIp, $ipList, $dbList);
            $companyName = $ipList[$visitIp];

            // if there is no row for this IP, create it
            if ($ipRow === false) {
                $result->addRowFromSimpleArray(array(
                    'IP'     => $visitIp,
                    'company'   => stripslashes($companyName),
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
            /*else {
                $counter = $ipRow->getColumn('nb_visits');
                $ipRow->setColumn('nb_visits', $counter + 1);
            }*/
        }

        return $result;
    }


    /**
     * Returns an array containing the IP and the company
     * @param string $period
     * @param string $date
     * @return array
     */
    public function getStoredCompanies()
    {
        $rows = NULL;

        try {
            $rows = Db::fetchAll("SELECT * FROM " . Common::prefixTable('ip_to_company'));
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }

        return $rows;
    }


    /**
     * Another example method that returns a data table.
     * @param string    $ip
     * @return array
     */
    private function getIPDetails($ip, $ipList, $dbList)
    {
        $itemFound      = FALSE;
        $companyName    = NULL;
        $hostname       = filter_var($ip, FILTER_VALIDATE_IP) ? gethostbyaddr($ip) : EMPTY_HOSTNAME;

        if(!isset($ipList[$ip])) {
            $delay = new \Datetime();
            $delay->sub(new \DateInterval('P' . $this->cacheLifeTimeForResults . 'W'));

            // Check if the IP address exists in the DB and if the record is younger than the defined cache
            foreach ($dbList as $item) {
                $itemDate = new \Datetime($item['updated_at']);

                if(($item['ip'] == $ip) && ($delay <= $itemDate)) {
                    $ipList[$ip]    = $item['as_name'];
                    $itemFound      = TRUE;
                }
                elseif(($item['ip'] == $ip) && ($itemDate < $delay)) {
                    $companyDetails = $this->getCompanyDetails($ip);
                    $companyName    = $companyDetails['as_name'];
                    $itemFound      = $companyName ? TRUE : FALSE;
                    $ipList[$ip]    = $companyName ? $companyName : ($hostname === $ip ? EMPTY_HOSTNAME : $hostname);

                    // We update the DB only if we got results from the getCompanyDetails method.
                    if(($ipList[$ip] != $hostname) && $companyName) {
                        $this->updateCompanyDetails($item, [
                            'as_number' => $companyDetails['as_number'],
                            'as_name'   => $companyDetails['as_name']
                        ]);
                    }
                }
            }
        }

        // If the IP doesn't exist in the DB, and if it is a valid IP, try to get the details
        if(!isset($ipList[$ip]) && !$itemFound && filter_var($ip, FILTER_VALIDATE_IP)) {
            $companyDetails = $this->getCompanyDetails($ip);
            $companyName    = $companyDetails['as_name'];
            $itemFound      = $companyName ? TRUE : FALSE;
            $ipList[$ip]    = $companyName ? $companyName : ($hostname === $ip ? EMPTY_HOSTNAME : $hostname);

            // We insert the item in the DB only if we got results from the getCompanyDetails method.
            if(($ipList[$ip] != $hostname) && $companyName) {
                $this->insertCompanyDetails([
                    'ip'        => $ip,
                    'as_number' => $companyDetails['as_number'],
                    'as_name'   => $companyDetails['as_name']
                ]);
            }
        }

        // If the IP is not valid, just return the empty hostname
        elseif(!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ipList[$ip] = EMPTY_HOSTNAME;
        }

        return $ipList;
    }


    /**
     * Another example method that returns a data table.
     * @param string    $ip
     * @return array
     */
    private function getCompanyDetails($ip)
    {
        $ipInfo         = new IPInfo();

        // If no token has been set, stop here.
        if(!$ipInfo->accessToken) {
            return [
                "as_name"   => NULL,
                "as_number" => NULL
            ];
        }

        try {
            $details        = $ipInfo->getDetails($ip);
        } catch (\Exception $e) {
            return [
                "as_name"   => NULL,
                "as_number" => NULL
            ];
        }

        $details        = json_decode($details);
        $companyName    = NULL;
        $asNumber       = NULL;

        if(isset($details->company) && isset($details->company->name)) {
            $companyName = $details->company->name;

            if($details->company->domain) {
                $companyName .= " (" . $details->company->domain . ")";
            }
        }
        elseif(isset($details->org) && !isset($details->org->name)) {
            $orgElements    = explode(" ", $details->org);
            $asNumber       = array_shift($orgElements);
            $asName         = count($orgElements) > 1 ? implode(" ", $orgElements) : $orgElements[0];
            $companyName    = $asName;
        }

        return [
            "as_name"   => $companyName,
            "as_number" => $asNumber
        ];
    }

    /**
     * A private methode to update the company details that we found in the DB
     * @param array    $item
     * @param array    $data
     * @return boolean
     */
    private function updateCompanyDetails($item, $data)
    {
        try {
            // If the server is running PHP 7.4.0 or newer
            if($this->isPHPVersionMoreRecentThan("7.4.0")) {
                $asName = filter_var($data['as_name'], FILTER_SANITIZE_ADD_SLASHES);
            }
            else {
                $asName = filter_var($data['as_name'], FILTER_SANITIZE_MAGIC_QUOTES);
            }

            $sql = "UPDATE " . Common::prefixTable('ip_to_company') . "
                SET as_number = '{$data['as_number']}', as_name = '{$asName}'
                WHERE id = {$item['id']}";
            Db::exec($sql);
        } catch (Exception $e) {
            throw $e;
        }

        return TRUE;
    }

    /**
     * A private methode to save the company details in the DB
     * @param array    $data
     * @return boolean
     */
    private function insertCompanyDetails($data)
    {
        try {
            if($this->isPHPVersionMoreRecentThan("7.4.0")) {
                $asName = filter_var($data['as_name'], FILTER_SANITIZE_ADD_SLASHES);
            }
            else {
                $asName = filter_var($data['as_name'], FILTER_SANITIZE_MAGIC_QUOTES);
            }
            $sql = "INSERT INTO " . Common::prefixTable('ip_to_company') . "
                (ip, as_number, as_name) VALUES
                ('{$data['ip']}', '{$data['as_number']}', '{$asName}')";
            Db::exec($sql);
        } catch (Exception $e) {
            throw $e;
        }

        return TRUE;
    }

    /**
     * A private methode to save the company details in the DB
     * @param array    $data
     * @return boolean
     */
    private function isPHPVersionMoreRecentThan($version)
    {
        $phpVersion         = phpversion();
        $phpVersionParts    = explode(".", $phpVersion);
        $phpMinVersionParts = explode(".", $version);

        if((int)$phpVersionParts[0] < (int)$phpMinVersionParts[0]) {
            return FALSE;
        }
        elseif((int)$phpVersionParts[1] < (int)$phpMinVersionParts[1]) {
            return FALSE;
        }
        elseif((int)$phpVersionParts[2] < (int)$phpMinVersionParts[2]) {
            return FALSE;
        }

        return TRUE;
    }
}
