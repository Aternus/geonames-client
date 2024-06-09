<?php

declare(strict_types=1);

namespace GeoNames;

use GeoNames\Client as GeoNamesClient;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Throwable;

final class ClientTest extends TestCase
{
    /** @var GeoNamesClient $client */
    protected $client;

    /** @var array<string, string>|null */
    protected $config;

    /** @var string $geonameId e.g. Israel */
    protected $geonameId = '294640';

    /** @var string $country Country code in ISO-3166 format e.g. Israel */
    protected $country = 'IL';

    /** @var float $lat e.g. Israel, Tel Aviv */
    protected $lat = 32.117425;

    /** @var float $lng e.g. Israel, Tel Aviv */
    protected $lng = 34.831990;

    public function setUp(): void
    {
        $this->config = [
            'token' => getenv('GEONAMES_TOKEN') ?: '',
            'username' => getenv('GEONAMES_USERNAME') ?: '',
        ];
        $this->client = new GeoNamesClient($this->config['username'], $this->config['token']);
    }

    public function testIsAValidInstanceOfClient(): void
    {
        $this->assertInstanceOf(GeoNamesClient::class, $this->client);
    }

    public function testConnectTimeout(): void
    {
        $g = $this->client;

        $this->assertEquals(0, $g->getConnectTimeout());

        $g->setConnectTimeout(10);

        $this->assertEquals(10, $g->getConnectTimeout());
    }

    public function testOverrideApiUrl(): void
    {
        $client = new GeoNamesClient(
            $this->config['username'],
            $this->config['token'],
            ['api_url' => 'https://api.geonames.org']
        );
        $this->assertEquals('https://api.geonames.org', $client->getOptions('api_url'));
    }

    public function testSetOptionsWithValidInputs(): void
    {
        $client = new GeoNamesClient(
            $this->config['username'],
            $this->config['token']
        );

        $options = [
            'api_url' => 'https://secure.geonames.org/',
            'connect_timeout' => 0,
            'fallback_api_url' => 'https://api.geonames.org/',
            'fallback_api_url_trigger_count' => 10,
            'token' => 'geonames_client_php_test_token',
            'username' => 'geonames_client_php_test_username',
        ];

        $result = $client->setOptions($options);
        $this->assertEquals($options, $result);
    }

    public function testSetOptionsWithEmptyToken(): void
    {
        $this->client->setOptions(['token' => '']);
        $this->assertEquals('', $this->client->getOptions('token'));
    }

    public function testSetOptionsWithInvalidTypeForUsername(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('username must be a string');
        $this->client->setOptions(['username' => 123]);
    }

    public function testSetOptionsWithInvalidOptionKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid_key is invalid');
        $this->client->setOptions(['invalid_key' => 'value']);
    }

    public function testSetOptionsMissingRequiredFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('username is required and cannot be empty');
        $this->client->setOptions(['username' => '']);
    }

    public function testUnsupportedEndpoint(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionCode($this->client::UNSUPPORTED_ENDPOINT);
        $this->client->spaceships();
    }

    public function testGetSupportedEndpoints(): void
    {
        $endpoints = $this->client->getSupportedEndpoints();
        $this->assertIsArray($endpoints);
        $this->assertContains('astergdem', $endpoints);
        $this->assertContains('wikipediaSearch', $endpoints);
    }

    public function testGetLastTotalResultsCountWhenResultIsLarge(): void
    {
        // search for a large result
        // (!) maxRows default is 100
        $arr = $this->client->search([
            'lang' => 'en',
            'q' => '東京都',
        ]);

        $this->assertIsArray($arr);
        $this->assertNotEmpty($arr);

        $count = count($arr);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertIsInt($total);
        $this->assertEquals(100, $count);
        $this->assertGreaterThan($count, $total);
    }

    public function testGetLastTotalResultsCountWhenResultIsMedium(): void
    {
        // search for a couple of results
        $arr = $this->client->search([
            'country' => 'CH',
            'featureClass' => 'P',
            'name_equals' => 'Grüningen (Stedtli)',
        ]);

        $this->assertIsArray($arr);
        $this->assertNotEmpty($arr);

        $count = count($arr);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertIsInt($total);
        $this->assertEquals($count, $total);
    }

    public function testGetLastTotalResultsCountWhenPlaceDoesntExist(): void
    {
        // search for a non-existing place
        $arr = $this->client->search([
            'name_equals' => 'öalkdjfpaoirhpauhrpgjanfdlijgbiopesrzgpi',
        ]);

        $this->assertIsArray($arr);
        $this->assertEmpty($arr);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertIsInt($total);
        $this->assertEquals(0, $total);
    }

    public function testGetLastTotalResultsForSingleEntryWhenThereIsNoTotalResultsCount(): void
    {
        $this->client->weatherIcao([
            'ICAO' => 'LLBG',
        ]);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertEquals(null, $total);
    }

    public function testGetLastTotalResultsForMultipleEntriesWhenThereIsNoTotalResultsCount(): void
    {
        // @see http://bboxfinder.com
        // Lng/ Lat
        // (xMin, yMin, xMax, yMax)
        // (west, south, east, north)
        $bbox_string = '33.760986,29.391748,35.661621,33.266250';
        $bbox_arr = array_map('trim', explode(',', $bbox_string));
        $bbox_params = [
            'east' => $bbox_arr[2],
            'north' => $bbox_arr[3],
            'south' => $bbox_arr[1],
            'west' => $bbox_arr[0],
        ];
        $arr = $this->client->weather($bbox_params);

        $this->assertIsArray($arr);
        $this->assertNotEmpty($arr);

        $count = count($arr);
        $total = $this->client->getLastTotalResultsCount();

        $this->assertEquals($count, $total);
    }

    public function testGetLastUrlRequested(): void
    {
        $arr = $this->client->search([
            'q' => 'London',
        ]);

        $this->assertIsArray($arr);

        $lastUrlRequested = $this->client->getLastUrlRequested();

        $this->assertIsString($lastUrlRequested);

        $g = $this->client;

        $class = new ReflectionClass($g);

        // get options protected property
        $options_property = $class->getProperty('options');
        $options_property->setAccessible(true);
        $options_value = $options_property->getValue($g);

        $url_value = $options_value['api_url'];
        $token_value = $options_value['token'];

        $urlExpected = empty($token_value) ? sprintf(
            '%s/searchJSON?q=London&username=%s',
            $url_value,
            $this->config['username']
        ) : sprintf(
            '%s/searchJSON?q=London&username=%s&token=%s',
            $url_value,
            $this->config['username'],
            $this->config['token']
        );

        $this->assertEquals($urlExpected, $lastUrlRequested);
    }

    public function testEndpointError(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionCode($this->client::INVALID_PARAMETER);
        $this->client->astergdem([]);
    }

    public function testParamsToQueryString(): void
    {
        $g = $this->client;

        $class = new ReflectionClass($g);
        $method = $class->getMethod('paramsToQueryString');
        $method->setAccessible(true);

        $paramsToQueryString = static function (array $params = []) use ($method, $g) {
            return $method->invokeArgs($g, [$params]);
        };

        $params = [];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('', $qs);

        $params = ['q' => 'foo'];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('q=foo', $qs);

        $params = ['q' => ['foo', 'bar']];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('q=foo&q=bar', $qs);

        $params = ['name_equals' => 'Grüningen', 'country' => 'CH'];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('name_equals=Gr%C3%BCningen&country=CH', $qs);

        $params = ['name_equals' => 'Grüningen', 'country' => ['CH']];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('name_equals=Gr%C3%BCningen&country=CH', $qs);

        $arr = $g->search($params);
        $this->assertIsArray($arr);
        $this->assertArrayHasKey(0, $arr);

        $params = ['name_equals' => 'Grüningen', 'country' => ['CH', 'DE']];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('name_equals=Gr%C3%BCningen&country=CH&country=DE', $qs);

        $arr = $g->search($params);
        $this->assertIsArray($arr);
        $this->assertArrayHasKey(0, $arr);

        $params = ['name_equals' => 'Grüningen', 'country' => []];
        $qs = $paramsToQueryString($params);
        $this->assertEquals('name_equals=Gr%C3%BCningen', $qs);

        $arr = $g->search($params);
        $this->assertIsArray($arr);
        $this->assertArrayHasKey(0, $arr);
    }

    public function testAstergram(): void
    {
        $obj = $this->client->astergdem([
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);
        $this->assertInstanceOf(stdClass::class, $obj);
        $this->assertObjectHasAttribute('astergdem', $obj);
        $this->assertEquals('45', $obj->astergdem);
    }

    public function testCountryInfo(): void
    {
        $arr = $this->client->countryInfo([
            'country' => $this->country,
            'lang' => 'ru',
        ]);
        $this->assertIsArray($arr);
        $this->assertArrayHasKey(0, $arr);
        $obj = $arr[0];
        $this->assertObjectHasAttribute('countryName', $obj);
        $this->assertEquals('Израиль', $obj->countryName);
    }

    public function testAddress(): void
    {
        $obj = $this->client->address([
            'lat' => 34.072713,
            'lng' => -118.402997,
        ]);
        $this->assertInstanceOf(stdClass::class, $obj);
        $this->assertObjectHasAttribute('countryCode', $obj);
        $this->assertEquals('US', $obj->countryCode);
        $this->assertObjectHasAttribute('locality', $obj);
        $this->assertEquals('Beverly Hills', $obj->locality);
    }

    public function testGet(): void
    {
        $obj = $this->client->get([
            'geonameId' => $this->geonameId,
            'lang' => 'en',
        ]);
        $this->assertInstanceOf(stdClass::class, $obj);
        $this->assertObjectHasAttribute('toponymName', $obj);
        $this->assertEquals('State of Israel', $obj->toponymName);
    }

    public function testOcean(): void
    {
        $obj = $this->client->ocean([
            'lat' => $this->lat,
            'lng' => $this->lng,
            'radius' => 10,
        ]);
        $this->assertInstanceOf(stdClass::class, $obj);
        $this->assertObjectHasAttribute('name', $obj);
        $this->assertEquals('Mediterranean Sea, Eastern Basin', $obj->name);
    }

    public function testSearch(): void
    {
        $arr = $this->client->search([
            'lang' => 'en',
            'q' => '東京都',
        ]);
        $this->assertIsArray($arr);
        $this->assertArrayHasKey(0, $arr);
        $obj = $arr[0];
        $this->assertObjectHasAttribute('name', $obj);
        $this->assertEquals('Tokyo', $obj->name);
    }

    /*
     * GitHub Example
     */
    public function testGitHubExample(): void
    {
        $g = $this->client;

        // get a list of supported endpoints
        $endpoints = $g->getSupportedEndpoints();

        // get info for country
        // note that I'm using the array destructor introduced in PHP 7.1
        [$country] = $g->countryInfo([
            'country' => 'IL',
            // display info in Russian
            'lang' => 'ru',
        ]);

        // country name (in Russian)
        $country_name = $country->countryName;

        // spoken languages (ISO-639-1)
        $country_languages = $country->languages;

        $this->assertCount(39, $endpoints);
        $this->assertEquals('Израиль', $country_name);
        $this->assertEquals('he,ar-IL,en-IL,', $country_languages);
    }
}
