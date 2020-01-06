<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IPtoCompany\libraries;

use Piwik\Settings\Setting;

/**
 * Defines Settings for IPtoCompany.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class IPInfo
{
    const API_URL = 'https://ipinfo.io';
    const COUNTRIES_FILE_DEFAULT = __DIR__ . '/../data/countries.json';
    const REQUEST_TYPE_GET = 'GET';
    const STATUS_CODE_QUOTA_EXCEEDED = 429;

    public $accessToken;

    public function __construct($settings = [])
    {
        // Get the access token
        $systemSettings = new \Piwik\Plugins\IPtoCompany\SystemSettings();
        $accessToken = $systemSettings->ipInfoAccessToken->getValue();
        $this->accessToken = $accessToken;

        // Get the list of countries
        $countries_file = $settings['countries_file'] ?? self::COUNTRIES_FILE_DEFAULT;
        $this->countries = $this->readCountryNames($countries_file);
    }

    /**
     * Get formatted details for an IP address.
     * @param  string|null $ip_address IP address to look up.
     * @return string Formatted IPinfo data as a JSON string.
     * @throws Exception
     */
    public function getDetails($ip_address = null)
    {
        $responseDetails = $this->getRequestDetails((string) $ip_address);
        return $this->formatDetailsObject($responseDetails);
    }

    /**
     * Format details and return as an object.
     * @param  array  $details IP address details.
     * @return string Formatted IPinfo Details object as a JSON string.
     */
    private function formatDetailsObject($details = [])
    {
        $country = $details['country'] ?? null;
        $details['country_name'] = $this->countries[$country] ?? null;

        if (array_key_exists('loc', $details)) {
            $coords = explode(',', $details['loc']);
            $details['latitude'] = $coords[0];
            $details['longitude'] = $coords[1];
        } else {
            $details['latitude'] = null;
            $details['longitude'] = null;
        }

        return json_encode($details);
    }


    /**
     * Get details for a specific IP address.
     * @param  string $ip_address IP address to query API for.
     * @return array IP response data.
     * @throws Exception
     */
    private function getRequestDetails(string $ip_address)
    {
        $httpCode = 0;
        $response = NULL;
        $url = self::API_URL;

        if ($ip_address) {
            $url .= "/$ip_address";
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeaders());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);

            // Check if any error occurred
            if (!curl_errno($ch)) {
                $info = curl_getinfo($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            }

            curl_close($ch);

            $response = json_decode($result, TRUE);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if ($httpCode == self::STATUS_CODE_QUOTA_EXCEEDED) {
            throw new Exception('IPinfo request quota exceeded.');
        } elseif ($httpCode >= 400) {
            throw new Exception('Exception: ' . json_encode([
                'status' => $httpCode
            ]));
        }

        return $response;
    }
    /**
     * Build headers for API request.
     * @return array Headers for API request.
     */
    private function buildHeaders()
    {
        $headers = [
            'user-agent' => 'MatomoIPtoCompany',
            'accept' => 'application/json',
        ];

        if ($this->accessToken) {
            $headers['authorization'] = "Authorization: Bearer " . $this->accessToken;
        }

        return $headers;
    }

    /**
     * Read country names from a file and return as an array.
     * @param  string $countries_file JSON file of country_code => country_name mappings
     * @return array country_code => country_name mappings
     */
    private function readCountryNames($countries_file)
    {
        $file_contents = file_get_contents($countries_file);
        return json_decode($file_contents, true);
    }
}
