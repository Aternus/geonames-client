<?php

declare(strict_types=1);

namespace GeoNames;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException as HttpClientException;
use InvalidArgumentException;
use stdClass;

/**
 * GeoNames API Client.
 *
 * @link https://www.geonames.org/export/ws-overview.html
 * @link https://www.geonames.org/export/web-services.html
 *
 * @method stdClass astergdem(array $params) Elevation - Aster Global Digital Elevation Model V2 2011.
 * @method array children(array $params)
 * @method array cities(array $params)
 * @method array contains(array $params)
 * @method stdClass countryCode(array $params)
 * @method array countryInfo(array $params) Country Info
 * @method stdClass countrySubdivision(array $params)
 * @method array earthquakes(array $params)
 * @method array findNearby(array $params)
 * @method array findNearbyPlaceName(array $params)
 * @method array findNearbyPostalCodes(array $params)
 * @method array findNearbyStreets(array $params)
 * @method array findNearbyStreetsOSM(array $params)
 * @method stdClass findNearByWeather(array $params)
 * @method array findNearbyWikipedia(array $params)
 * @method stdClass findNearestAddress(array $params)
 * @method stdClass findNearestIntersection(array $params)
 * @method stdClass findNearestIntersectionOSM(array $params)
 * @method array findNearbyPOIsOSM(array $params)
 * @method stdClass address(array $params)
 * @method stdClass geoCodeAddress(array $params)
 * @method stdClass get(array $params)
 * @method stdClass gtopo30(array $params) Elevation - GTOPO30 is a global digital elevation model (DEM)
 *                                         with a horizontal grid spacing of 30 arc seconds.
 * @method array hierarchy(array $params)
 * @method stdClass neighbourhood(array $params)
 * @method array neighbours(array $params)
 * @method stdClass ocean(array $params)
 * @method array postalCodeCountryInfo(array $params)
 * @method array postalCodeLookup(array $params)
 * @method array postalCodeSearch(array $params)
 * @method array search(array $params)
 * @method array siblings(array $params)
 * @method stdClass srtm1(array $params) Elevation - SRTM1 (Shuttle Radar Topography Mission).
 * @method stdClass srtm3(array $params) Elevation - SRTM3 (Shuttle Radar Topography Mission).
 * @method stdClass timezone(array $params)
 * @method array weather(array $params)
 * @method stdClass weatherIcao(array $params) Most recent weather observation using
 *                                             International Civil Aviation Organization (ICAO) code.
 * @method array wikipediaBoundingBox(array $params)
 * @method array wikipediaSearch(array $params)
 */
class Client
{
    /**
     * Exception codes defined by this library.
     */

    public const UNSUPPORTED_ENDPOINT = 1;
    public const JSON_DECODE_ERROR = 2;

    /**
     * Exception codes defined by the web service.
     *
     * @see https://www.geonames.org/export/webservice-exception.html
     */

    public const AUTHORIZATION_EXCEPTION = 10;
    public const RECORD_DOES_NOT_EXIST = 11;
    public const OTHER_ERROR = 12;
    public const DATABASE_TIMEOUT = 13;
    public const INVALID_PARAMETER = 14;
    public const NO_RESULT_FOUND = 15;
    public const DUPLICATE_EXCEPTION = 16;
    public const POSTAL_CODE_NOT_FOUND = 17;
    public const DAILY_LIMIT_OF_CREDITS_EXCEEDED = 18;
    public const HOURLY_LIMIT_OF_CREDITS_EXCEEDED = 19;
    public const WEEKLY_LIMIT_OF_CREDITS_EXCEEDED = 20;
    public const INVALID_INPUT = 21;
    public const SERVER_OVERLOADED_EXCEPTION = 22;
    public const SERVICE_NOT_IMPLEMENTED = 23;
    public const RADIUS_TOO_LARGE = 24;
    public const MAXROWS_TOO_LARGE = 25;

    /**
     * Array of supported endpoints (listed alphabetically) and their corresponding root property (if any).
     *
     * Note: Only JSON endpoints are supported.
     *
     * @see https://www.geonames.org/export/ws-overview.html
     *
     * @var array<string, string|bool> $endpoints
     */
    protected $endpoints = [
        'address' => 'address',
        'astergdem' => false,
        'children' => 'geonames',
        'cities' => 'geonames',
        'contains' => 'geonames',
        'countryCode' => false,
        'countryInfo' => 'geonames',
        'countrySubdivision' => false,
        'earthquakes' => 'earthquakes',
        'findNearby' => 'geonames',
        'findNearbyPlaceName' => 'geonames',
        'findNearbyPOIsOSM' => 'poi',
        'findNearbyPostalCodes' => 'postalCodes',
        // US only
        'findNearbyStreets' => 'streetSegment',
        'findNearbyStreetsOSM' => 'streetSegment',
        'findNearByWeather' => 'weatherObservation',
        'findNearbyWikipedia' => 'geonames',
        // US only
        'findNearestAddress' => 'address',
        // US only
        'findNearestIntersection' => 'intersection',
        'findNearestIntersectionOSM' => 'intersection',
        'geoCodeAddress' => 'address',
        'get' => false,
        'gtopo30' => false,
        'hierarchy' => 'geonames',
        // US only
        'neighbourhood' => 'neighbourhood',
        'neighbours' => 'geonames',
        'ocean' => 'ocean',
        'postalCodeCountryInfo' => 'geonames',
        'postalCodeLookup' => 'postalcodes',
        // not a typo
        'postalCodeSearch' => 'postalCodes',
        'search' => 'geonames',
        'siblings' => 'geonames',
        'srtm1' => false,
        'srtm3' => false,
        'timezone' => false,
        'weather' => 'weatherObservations',
        'weatherIcao' => 'weatherObservation',
        'wikipediaBoundingBox' => 'geonames',
        'wikipediaSearch' => 'geonames',
    ];

    /** @var int|null */
    protected $lastTotalResultsCount = null;

    /** @var string|null */
    protected $lastUrlRequested = null;

    /**
     * GeoNames Client Options
     *
     * @var array{
     *  username: string,
     *  token: string,
     *  api_url: string,
     *  fallback_api_url: string,
     *  connect_timeout: int,
     *  fallback_api_url_trigger_count: int,
     * }
     */
    protected $options = [
        'api_url' => 'https://secure.geonames.org/',
        'connect_timeout' => 0,
        'fallback_api_url' => 'https://api.geonames.org/',
        'fallback_api_url_trigger_count' => 10,
        'token' => '',
        'username' => '',
    ];

    /**
     * Constructor.
     *
     * Creates a new GeoNames API Client instance.
     *
     * Options:
     * - username: GeoNames username.
     * - token: GeoNames token (i.e. premium user key).
     * - api_url: URL of the GeoNames web service.
     * - connect_timeout: HTTP Client connection timeout.
     *   The number of seconds to wait while trying to connect to a server.
     *   The default behavior, `0`, means to wait indefinitely.
     *
     * - fallback_api_url: ⚠️ NOT IMPLEMENTED YET. Fallback URL of the GeoNames web service.
     * - fallback_api_url_trigger_count: ⚠️ NOT IMPLEMENTED YET. Number of connection timeouts
     *   before using the `fallback_api_url`
     *
     * @link https://www.geonames.org/
     *
     * @see https://www.geonames.org/commercial-webservices.html
     *
     * @param string $username Required for both Free and Commercial users.
     * @param string $token Optional. Commercial users only.
     * @param array{
     *  username?: string,
     *  token?: string,
     *  api_url?: string,
     *  connect_timeout?: int,
     * } $options Optional. Client options.
     */
    public function __construct(string $username, string $token = '', array $options = [])
    {
        $legacy_options = [
            'username' => $username,
        ];

        if (!empty($token)) {
            $legacy_options['token'] = $token;
        }

        $this->setOptions(array_merge($legacy_options, $options));
    }

    /**
     * Returns an array of supported endpoints.
     *
     * @return array<string>
     *
     * @see $endpoints
     */
    public function getSupportedEndpoints(): array
    {
        return array_keys($this->endpoints);
    }

    public function getLastTotalResultsCount(): ?int
    {
        return $this->lastTotalResultsCount;
    }

    public function getLastUrlRequested(): ?string
    {
        return $this->lastUrlRequested;
    }

    public function getConnectTimeout(): int
    {
        return $this->getOptions('connect_timeout');
    }

    public function setConnectTimeout(int $connect_timeout): void
    {
        $this->setOptions([
            "connect_timeout" => $connect_timeout,
        ]);
    }

    /**
     * @return string|int|array{
     *  username: string,
     *  token: string,
     *  api_url: string,
     *  connect_timeout: int,
     * }
     */
    public function getOptions(string $key = '')
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        return $this->options;
    }

    /**
     * @param array{
     *  username?: string,
     *  token?: string,
     *  api_url?: string,
     *  connect_timeout?: int,
     * } $options
     *
     * @throws InvalidArgumentException When any option is invalid.
     *
     * @return array{
     *  username: string,
     *  token: string,
     *  api_url: string,
     *  connect_timeout: int,
     * }
     */
    public function setOptions(array $options): array
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'username':
                case 'token':
                case 'api_url':
                case 'fallback_api_url':
                    if (is_string($value)) {
                        break;
                    }

                    throw new InvalidArgumentException("{$key} must be a string");
                case 'connect_timeout':
                case 'fallback_api_url_trigger_count':
                    if (is_int($value)) {
                        break;
                    }

                    throw new InvalidArgumentException("{$key} must be an integer");
                default:
                    throw new InvalidArgumentException("{$key} is invalid");
            }
        }

        $merged_options = array_merge($this->options, $options);

        foreach (['username', 'api_url', 'fallback_api_url', 'fallback_api_url_trigger_count'] as $key) {
            if (empty($merged_options[$key])) {
                throw new InvalidArgumentException("{$key} is required and cannot be empty");
            }
        }

        return $this->options = $merged_options;
    }

    /**
     * Convert Parameters Array to a Query String.
     *
     * Escape values according to RFC 1738.
     *
     * @see https://forum.geonames.org/gforum/posts/list/8.page
     * @see rawurlencode()
     *
     * @param array<mixed> $params Associative array of query parameters.
     *
     * @return string The query string.
     */
    protected function paramsToQueryString(array $params = []): string
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

    /**
     * Method call interceptor.
     *
     * Queries the endpoint using the parameters.
     *
     * @param string $endpoint The endpoint to call.
     * @param array<mixed> $params Optional. Parameters to pass to the endpoint.
     *
     * @return object|array<mixed> The response object or array.
     *
     * @throws HttpClientException When HttpClient had a fatal failure.
     *
     * @throws Exception When an invalid method is called or when the web service returns an error.
     */
    public function __call(string $endpoint, array $params = [])
    {
        $this->lastTotalResultsCount = null;
        $this->lastUrlRequested = null;

        // check that the endpoint is supported
        if (!in_array($endpoint, $this->getSupportedEndpoints(), true)) {
            throw new Exception("Unsupported endpoint: {$endpoint}", self::UNSUPPORTED_ENDPOINT);
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
        $params['username'] = $this->options['username'];

        if (!empty($this->options['token'])) {
            $params['token'] = $this->options['token'];
        }

        // HttpClient arguments
        $HttpClient_args = [
            'base_uri' => $this->options['api_url'],
            'connect_timeout' => $this->options['connect_timeout'],
            // @see https://curl.haxx.se/docs/caextract.html
            'verify' => __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem',
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

        $this->lastUrlRequested = $this->options['api_url'] . '/' . $uri;

        // send GET request
        $response = $HttpClient->get($uri);

        // decode the response body
        $response_object = json_decode((string)$response->getBody());

        // check that json_decode() worked correctly
        if (!is_object($response_object)) {
            throw new Exception("Could not JSON decode the response body.", self::JSON_DECODE_ERROR);
        }

        // check for errors in response
        if (isset($response_object->status->message, $response_object->status->value)) {
            throw new Exception($response_object->status->message, (int)$response_object->status->value);
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
}
