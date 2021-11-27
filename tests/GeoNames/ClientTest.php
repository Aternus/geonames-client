<?php

namespace GeoNames;

use PHPUnit\Framework\TestCase;
use GeoNames\Client as GeoNamesClient;

final class ClientTest extends TestCase
{
    /**
     * @var GeoNamesClient $client
     */
    protected $client;

    /** @var array|null */
    protected $config;

    protected $geonameId = '294640'; // Israel
    protected $country = 'IL'; // ISO-3166
    protected $lat = 32.117425; // Israel, Tel Aviv
    protected $lng = 34.831990; // Israel, Tel Aviv

    public function setUp(): void
    {
        $this->config = json_decode(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.json'),
            true
        );
        $this->client = new GeoNamesClient($this->config['username'], $this->config['token']);
    }

    public function testIsAValidInstanceOfClient()
    {
        $this->assertInstanceOf(GeoNamesClient::class, $this->client);
    }

    public function testUnsupportedEndpoint()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode($this->client::UNSUPPORTED_ENDPOINT);
        $this->client->spaceships();
    }

    public function testGetSupportedEndpoints()
    {
        $endpoints = $this->client->getSupportedEndpoints();
        $this->assertIsArray($endpoints);
        $this->assertContains('astergdem', $endpoints);
        $this->assertContains('wikipediaSearch', $endpoints);
    }

    public function testGetLastTotalResultsCountWhenResultIsLarge()
    {
        // search for a large result
        // (!) maxRows default is 100
        $arr = $this->client->search([
            'q'    => '東京都',
            'lang' => 'en',
        ]);

        $this->assertIsArray($arr);
        $this->assertNotEmpty($arr);

        $count = count($arr);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertIsInt($total);
        $this->assertEquals(100, $count);
        $this->assertGreaterThan($count, $total);
    }

    public function testGetLastTotalResultsCountWhenResultIsMedium()
    {
        // search for a couple of results
        $arr = $this->client->search([
            'name_equals'  => 'Grüningen (Stedtli)',
            'country'      => 'CH',
            'featureClass' => 'P',
        ]);

        $this->assertIsArray($arr);
        $this->assertNotEmpty($arr);

        $count = count($arr);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertIsInt($total);
        $this->assertEquals($count, $total);
    }

    public function testGetLastTotalResultsCountWhenPlaceDoesntExist()
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

    public function testGetLastTotalResultsForSingleEntryWhenThereIsNoTotalResultsCount()
    {
        $arr = $this->client->weatherIcao([
            'ICAO' => 'LLBG',
        ]);

        $total = $this->client->getLastTotalResultsCount();

        $this->assertEquals(null, $total);
    }

    public function testGetLastTotalResultsForMultipleEntriesWhenThereIsNoTotalResultsCount()
    {
        // @see http://bboxfinder.com
        // Lng/ Lat
        // (xMin, yMin, xMax, yMax)
        // (west, south, east, north)
        $bbox_string = '33.760986,29.391748,35.661621,33.266250';
        $bbox_arr = array_map('trim', explode(',', $bbox_string));
        $bbox_params = [
            'west'  => $bbox_arr[0],
            'south' => $bbox_arr[1],
            'east'  => $bbox_arr[2],
            'north' => $bbox_arr[3],
        ];
        $arr = $this->client->weather($bbox_params);

        $this->assertIsArray($arr);
        $this->assertNotEmpty($arr);

        $count = count($arr);
        $total = $this->client->getLastTotalResultsCount();

        $this->assertEquals($count, $total);
    }

    public function testGetLastUrlRequested()
    {
        $arr = $this->client->search([
            'q' => 'London',
        ]);

        $this->assertIsArray($arr);

        $lastUrlRequested = $this->client->getLastUrlRequested();

        $this->assertIsString($lastUrlRequested);

        $g = $this->client;

        $class = new \ReflectionClass($g);

        // get url protected property
        $url_property = $class->getProperty('url');
        $url_property->setAccessible(true);
        $url_value = $url_property->getValue($g);

        // get token protected property
        $token_property = $class->getProperty('token');
        $token_property->setAccessible(true);
        $token_value = $token_property->getValue($g);

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

    public function testEndpointError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode($this->client::INVALID_PARAMETER);
        $this->client->astergdem([]);
    }


    public function testParamsToQueryString()
    {
        $g = $this->client;

        $class = new \ReflectionClass($g);
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

    public function testAstergram()
    {
        $obj = $this->client->astergdem([
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('astergdem', $obj);
        $this->assertEquals('45', $obj->astergdem);
    }

    public function testCountryInfo()
    {
        $arr = $this->client->countryInfo([
            'country' => $this->country,
            'lang'    => 'ru',
        ]);
        $this->assertIsArray($arr);
        $this->assertArrayHasKey(0, $arr);
        $obj = $arr[0];
        $this->assertObjectHasAttribute('countryName', $obj);
        $this->assertEquals('Израиль', $obj->countryName);
    }

    public function testAddress()
    {
        $obj = $this->client->address([
            'lat' => 34.072713,
            'lng' => -118.402997,
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('countryCode', $obj);
        $this->assertEquals('US', $obj->countryCode);
        $this->assertObjectHasAttribute('locality', $obj);
        $this->assertEquals('Beverly Hills', $obj->locality);
    }

    public function testGet()
    {
        $obj = $this->client->get([
            'geonameId' => $this->geonameId,
            'lang'      => 'en',
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('toponymName', $obj);
        $this->assertEquals('State of Israel', $obj->toponymName);
    }

    public function testOcean()
    {
        $obj = $this->client->ocean([
            'lat'    => $this->lat,
            'lng'    => $this->lng,
            'radius' => 10,
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('name', $obj);
        $this->assertEquals('Mediterranean Sea, Eastern Basin', $obj->name);
    }

    public function testSearch()
    {
        $arr = $this->client->search([
            'q'    => '東京都',
            'lang' => 'en',
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
    public function testGitHubExample()
    {
        $g = $this->client;

        // get a list of supported endpoints
        $endpoints = $g->getSupportedEndpoints();

        // get info for country
        // note that I'm using the array destructor introduced in PHP 7.1
        [$country] = $g->countryInfo([
            'country' => 'IL',
            'lang'    => 'ru', // display info in Russian
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
