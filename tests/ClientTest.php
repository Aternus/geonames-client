<?php

namespace GeoNames\Tests;

use PHPUnit\Framework\TestCase;
use GeoNames\Client as GeoNamesClient;

final class ClientTest extends TestCase
{
    /**
     * @var GeoNamesClient $client
     */
    protected $client;

    protected $geonameId = '294640'; // Israel
    protected $country = 'IL'; // ISO-3166
    protected $lat = 32.117425; // Israel, Tel Aviv
    protected $lng = 34.831990; // Israel, Tel Aviv

    public function setUp()
    {
        $config = json_decode(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.json'),
            true
        );
        $this->client = new GeoNamesClient($config['username'], $config['token']);
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
        $this->assertInternalType('array', $endpoints);
        $this->assertContains('astergdem', $endpoints);
        $this->assertContains('wikipediaSearch', $endpoints);
    }

    public function testEndpointError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode($this->client::INVALID_PARAMETER);
        $this->client->astergdem([]);
    }


    public function testAstergram()
    {
        $obj = $this->client->astergdem([
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('astergdem', $obj);
        $this->assertAttributeEquals('45', 'astergdem', $obj);
    }

    public function testCountryInfo()
    {
        $arr = $this->client->countryInfo([
            'country' => $this->country,
            'lang'    => 'ru',
        ]);
        $this->assertInternalType('array', $arr);
        $this->assertArrayHasKey(0, $arr);
        $obj = $arr[0];
        $this->assertObjectHasAttribute('countryName', $obj);
        $this->assertAttributeEquals('Израиль', 'countryName', $obj);
    }

    public function testAddress()
    {
        $obj = $this->client->address([
            'lat' => 34.072713,
            'lng' => -118.402997,
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('countryCode', $obj);
        $this->assertAttributeEquals('US', 'countryCode', $obj);
        $this->assertObjectHasAttribute('locality', $obj);
        $this->assertAttributeEquals('Beverly Hills', 'locality', $obj);
    }

    public function testGet()
    {
        $obj = $this->client->get([
            'geonameId' => $this->geonameId,
            'lang'      => 'en',
        ]);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertObjectHasAttribute('toponymName', $obj);
        $this->assertAttributeEquals('State of Israel', 'toponymName', $obj);
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
        $this->assertAttributeEquals('Mediterranean Sea, Eastern Basin', 'name', $obj);
    }

    public function testSearch()
    {
        $arr = $this->client->search([
            'q'    => '東京都',
            'lang' => 'en',
        ]);
        $this->assertInternalType('array', $arr);
        $this->assertArrayHasKey(0, $arr);
        $obj = $arr[0];
        $this->assertObjectHasAttribute('name', $obj);
        $this->assertAttributeEquals('Tokyo', 'name', $obj);
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
