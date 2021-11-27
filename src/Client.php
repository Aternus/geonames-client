<?php

namespace GeoNames;

use Exception;
use GuzzleHttp\Exception\GuzzleException as HttpClientException;
use GuzzleHttp\Client as HttpClient;

/**
 * GeoNames API Client.
 *
 * @link http://www.geonames.org/export/ws-overview.html
 * @link http://www.geonames.org/export/web-services.html
 *
 * @method \stdClass astergdem(array $params) Elevation - Aster Global Digital Elevation Model V2 2011.
 * @method array     children(array $params)
 * @method array     cities(array $params)
 * @method array     contains(array $params)
 * @method \stdClass countryCode(array $params)
 * @method array     countryInfo(array $params) Country Info
 * @method \stdClass countrySubdivision(array $params)
 * @method array     earthquakes(array $params)
 * @method array     findNearby(array $params)
 * @method array     findNearbyPlaceName(array $params)
 * @method array     findNearbyPostalCodes(array $params)
 * @method array     findNearbyStreets(array $params)
 * @method array     findNearbyStreetsOSM(array $params)
 * @method \stdClass findNearByWeather(array $params)
 * @method array     findNearbyWikipedia(array $params)
 * @method \stdClass findNearestAddress(array $params)
 * @method \stdClass findNearestIntersection(array $params)
 * @method \stdClass findNearestIntersectionOSM(array $params)
 * @method array     findNearbyPOIsOSM(array $params)
 * @method \stdClass address(array $params)
 * @method \stdClass geoCodeAddress(array $params)
 * @method \stdClass get(array $params)
 * @method \stdClass gtopo30(array $params) Elevation - GTOPO30 is a global digital elevation model (DEM)
 *                                                      with a horizontal grid spacing of 30 arc seconds.
 *
 * @method array     hierarchy(array $params)
 * @method \stdClass neighbourhood(array $params)
 * @method array     neighbours(array $params)
 * @method \stdClass ocean(array $params)
 * @method array     postalCodeCountryInfo(array $params)
 * @method array     postalCodeLookup(array $params)
 * @method array     postalCodeSearch(array $params)
 * @method array     search(array $params)
 * @method array     siblings(array $params)
 * @method \stdClass srtm1(array $params) Elevation - SRTM1 (Shuttle Radar Topography Mission).
 * @method \stdClass srtm3(array $params) Elevation - SRTM3 (Shuttle Radar Topography Mission).
 * @method \stdClass timezone(array $params)
 * @method array     weather(array $params)
 * @method \stdClass weatherIcao(array $params) Most recent weather observation using
 *                                              International Civil Aviation Organization (ICAO) code.
 *
 * @method array     wikipediaBoundingBox(array $params)
 * @method array     wikipediaSearch(array $params)
 */
class Client
{
    /**
     * Exception codes defined by this library.
     */
    const UNSUPPORTED_ENDPOINT = 1;
    const JSON_DECODE_ERROR = 2;

    /**
     * Exception codes defined by the web service.
     *
     * @see http://www.geonames.org/export/webservice-exception.html
     */
    const AUTHORIZATION_EXCEPTION = 10;
    const RECORD_DOES_NOT_EXIST = 11;
    const OTHER_ERROR = 12;
    const DATABASE_TIMEOUT = 13;
    const INVALID_PARAMETER = 14;
    const NO_RESULT_FOUND = 15;
    const DUPLICATE_EXCEPTION = 16;
    const POSTAL_CODE_NOT_FOUND = 17;
    const DAILY_LIMIT_OF_CREDITS_EXCEEDED = 18;
    const HOURLY_LIMIT_OF_CREDITS_EXCEEDED = 19;
    const WEEKLY_LIMIT_OF_CREDITS_EXCEEDED = 20;
    const INVALID_INPUT = 21;
    const SERVER_OVERLOADED_EXCEPTION = 22;
    const SERVICE_NOT_IMPLEMENTED = 23;
    const RADIUS_TOO_LARGE = 24;
    const MAXROWS_TOO_LARGE = 25;

    /**
     * URL of the GeoNames web service.
     *
     * @var string $url
     */
    protected $url = 'https://secure.geonames.org';

    /**
     * Auth username.
     *
     * @see http://www.geonames.org/commercial-webservices.html
     *
     * @var string $username
     */
    protected $username;

    /**
     * Auth token.
     *
     * @see http://www.geonames.org/commercial-webservices.html
     *
     * @var string $token
     */
    protected $token;

    /**
     * Array of supported endpoints (listed alphabetically) and their corresponding root property (if any).
     *
     * Note: Only JSON endpoints are supported.
     *
     * @see http://www.geonames.org/export/ws-overview.html
     *
     * @var array $endpoints
     */
    protected $endpoints = [
        'astergdem'                  => false,
        'children'                   => 'geonames',
        'cities'                     => 'geonames',
        'contains'                   => 'geonames',
        'countryCode'                => false,
        'countryInfo'                => 'geonames',
        'countrySubdivision'         => false,
        'earthquakes'                => 'earthquakes',
        'findNearby'                 => 'geonames',
        'findNearbyPlaceName'        => 'geonames',
        'findNearbyPostalCodes'      => 'postalCodes',
        'findNearbyStreets'          => 'streetSegment', // US only
        'findNearbyStreetsOSM'       => 'streetSegment',
        'findNearByWeather'          => 'weatherObservation',
        'findNearbyWikipedia'        => 'geonames',
        'findNearestAddress'         => 'address', // US only
        'findNearestIntersection'    => 'intersection', // US only
        'findNearestIntersectionOSM' => 'intersection',
        'findNearbyPOIsOSM'          => 'poi',
        'address'                    => 'address',
        'geoCodeAddress'             => 'address',
        'get'                        => false,
        'gtopo30'                    => false,
        'hierarchy'                  => 'geonames',
        'neighbourhood'              => 'neighbourhood', // US only
        'neighbours'                 => 'geonames',
        'ocean'                      => 'ocean',
        'postalCodeCountryInfo'      => 'geonames',
        'postalCodeLookup'           => 'postalcodes',
        'postalCodeSearch'           => 'postalCodes', // not a typo
        'search'                     => 'geonames',
        'siblings'                   => 'geonames',
        'srtm1'                      => false,
        'srtm3'                      => false,
        'timezone'                   => false,
        'weather'                    => 'weatherObservations',
        'weatherIcao'                => 'weatherObservation',
        'wikipediaBoundingBox'       => 'geonames',
        'wikipediaSearch'            => 'geonames',
    ];

    /**
     * @var int|null
     */
    protected $lastTotalResultsCount = null;

    /**
     * @var string|null
     */
    protected $lastUrlRequested = null;

    /**
     * Constructor.
     *
     * Creates a new GeoNames API Client instance.
     *
     * @link http://www.geonames.org/
     *
     * @param string      $username Required for both Free and Commercial users.
     * @param string|null $token    Optional. Commercial users only.
     */
    public function __construct(string $username, string $token = null)
    {
        $this->username = $username;
        $this->token = $token;
    }

    /**
     * Method call interceptor.
     *
     * Queries the endpoint using the parameters.
     *
     * @param string $endpoint The endpoint to call.
     * @param array  $params   Optional. Parameters to pass to the endpoint.
     *
     * @return object \stdClass The response object.
     * @throws HttpClientException When HttpClient had a fatal failure.
     *
     * @throws Exception When an invalid method is called or when the web service returns an error.
     */
    public function __call(string $endpoint, array $params = [])
    {
        $this->lastTotalResultsCount = null;
        $this->lastUrlRequested = null;

        // check that the endpoint is supported
        if (!in_array($endpoint, $this->getSupportedEndpoints())) {
            throw new \Exception(
                "Unsupported endpoint: {$endpoint}",
                self::UNSUPPORTED_ENDPOINT
            );
        }

        // handle params
        if (isset($params[0]) && is_array($params[0])) {
            // arguments have been supplied to the callback
            $params = $params[0];
        }

        // handle request type
        if (isset($params['type'])) {
            // only JSON is supported
            unset($params['type']);
        }

        // handle authentication
        $params['username'] = $this->username;

        if (!empty($this->token)) {
            $params['token'] = $this->token;
        }

        // HttpClient arguments
        $HttpClient_args = [
            'base_uri' => $this->url,
            // @see https://curl.haxx.se/docs/caextract.html
            'verify'   => __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem',
        ];

        // handle proxy
        if (!empty($params['proxy'])) {
            $HttpClient_args = array_merge(
                $HttpClient_args,
                [
                    'proxy' => $params['proxy'],
                ]
            );
            unset($params['proxy']);
        }

        // create HttpClient instance
        $HttpClient = new HttpClient($HttpClient_args);

        // build the query string
        $query_string = $this->paramsToQueryString($params);

        $uri = $endpoint . 'JSON?' . $query_string;

        $this->lastUrlRequested = $this->url . '/' . $uri;

        // send GET request
        $response = $HttpClient->get($uri);

        // decode the response body
        $response_object = json_decode((string)$response->getBody());

        // check that json_decode() worked correctly
        if (!is_object($response_object)) {
            throw new \Exception(
                "Could not JSON decode the response body.",
                self::JSON_DECODE_ERROR
            );
        }

        // check for errors in response
        if (isset($response_object->status->message, $response_object->status->value)) {
            throw new \Exception(
                $response_object->status->message,
                (int)$response_object->status->value
            );
        }

        // return the value of the root property from the response object (if the endpoint supports it)
        $root_property = $this->endpoints[$endpoint];

        // root property is defined
        if ($root_property !== false && property_exists($response_object, $root_property)) {
            $response_data = $response_object->{$root_property};
            if (property_exists($response_object, 'totalResultsCount')) {
                $this->lastTotalResultsCount = $response_object->totalResultsCount;
            } elseif (is_array($response_data)) {
                $this->lastTotalResultsCount = count($response_data);
            }
            return $response_data;
        }

        // root property is not defined
        $this->lastTotalResultsCount = null;
        if (is_array($response_object)) {
            $this->lastTotalResultsCount = count($response_object);
        }
        return $response_object;
    }

    /**
     * Returns an array of supported endpoints.
     *
     * @return array An array of endpoints supported.
     * @see $endpoints
     *
     */
    public function getSupportedEndpoints()
    {
        return array_keys($this->endpoints);
    }

    /**
     * @return int|null
     */
    public function getLastTotalResultsCount()
    {
        return $this->lastTotalResultsCount;
    }

    /**
     * @return string|null
     */
    public function getLastUrlRequested()
    {
        return $this->lastUrlRequested;
    }

    /**
     * Convert Parameters Array to a Query String.
     *
     * Escape values according to RFC 1738.
     *
     * @see http://forum.geonames.org/gforum/posts/list/8.page
     * @see rawurlencode()
     *
     * @param array $params Associative array of query parameters.
     *
     * @return string The query string.
     */
    protected function paramsToQueryString(array $params = [])
    {
        $query_string = [];
        foreach ($params as $name => $value) {
            if (empty($name)) {
                continue;
            }
            if (is_array($value)) {
                if (empty($value)) {
                    // skip empty arrays
                    continue;
                }
                foreach ($value as $key => $item) {
                    if (!is_string($key)) {
                        $key = $name;
                    }
                    $item = (string)$item;
                    $query_string[] = $key . '=' . rawurlencode($item);
                }
            } else {
                $value = (string)$value;
                $query_string[] = $name . '=' . rawurlencode($value);
            }
        }
        return implode('&', $query_string);
    }
}
