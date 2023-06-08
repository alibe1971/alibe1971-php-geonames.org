<?php

namespace Alibe\Geonames\tests;

/*
|---------------------------------------------------
| Test with PhpUnit
|---------------------------------------------------
|
|
*/

use PHPUnit\Framework\TestCase;
use Alibe\Geonames\geonames;

final class GeonamesTest extends TestCase {

    private $geo;

    /* Inistializing */
    public function __construct() {
        parent::__construct();
        $this->geo=new geonames(getenv('username'));
    }

    /*protected function setUp() {
        $this->geo=new geonames('alibe71');
    }

    protected function tearDown() {
        $this->geo = NULL;
    }*/


    public function testInitialize() {
        $this->assertIsObject($this->geo,'Failed initialization');
    }

    public function testConnection() {
        $t=$this->geo->get('3175395');
        if(isSet($t->status)) {
            if(isSet($t->status->message)) {
                $this->assertTrue(false,"\n".$t->status->message."\n");
                die();
            }
        }
        $this->assertTrue(true);
    }

    /* RAW CALL */
    public function testRawCalls() {
        // Test as object
        $t=$this->geo->rawCall(
            'get',
            [
                'geonameId'=>2643743
            ],
            'object'
        );
        $this->assertIsObject($t,'Failed return object');

        // Test as array
        $t=$this->geo->rawCall(
            'get',
            [
                'geonameId'=>2643743
            ],
            'array'
        );
        $this->assertIsArray($t,'Failed return array');

        // Test as JSON
        $t=$this->geo->rawCall(
            'getJSON',
            [
                'geonameId'=>2643743
            ]
        );
        $success=is_string($t) && is_array(json_decode($t, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
        $this->assertTrue($success,'Failed return json');

        // Test as XML
        $t=$this->geo->rawCall(
            'get',
            [
                'geonameId'=>2643743
            ]
        );
        $parser=xml_parser_create();
        $success=xml_parse($parser,$t);
        $this->assertEquals(1,$success,'Failed return xml');
    }

    /* Get Webservice */
    public function testGet() {
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italy',$t->name,'Not correct name');

        // Change language Italian
        $this->geo->set([
            'lang'=>'it'
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italia',$t->name,'Not correct name');

        // Reset language
        $this->geo->set([
            'lang'=>false
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italy',$t->name,'Not correct name');

        // Change language Francese
        $this->geo->set([
            'lang'=>'fr'
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italie',$t->name,'Not correct name');

        // Reset all
        $this->geo->reset();
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italy',$t->name,'Not correct name');
    }

    /* countryInfo Webservice */
    public function testCountryInfo() {
        // Test the list of the country
        $t=$this->geo->countryInfo();
        $c=count($t->geonames);
        $this->assertGreaterThan(30,$c,'Is not a so big list');

        // Test the single country
        $t=$this->geo->countryInfo('ie');
        $c=count($t->geonames);
        $this->assertEquals(1,$c,'Is not a single country');
        $this->assertEquals('2963597',$t->geonames[0]->geonameId,'Not correct geonamesId');

        // Test the list of the country
        $t=$this->geo->countryInfo(['ie','it']);
        $c=count($t->geonames);
        $this->assertEquals(2,$c,'Not correct count');
        // Consider the return in alphabetical order
        $this->assertEquals('2963597',$t->geonames[0]->geonameId,'Not correct geonamesId for Ireland');
        $this->assertEquals('3175395',$t->geonames[1]->geonameId,'Not correct geonamesId for Italy');
    }

    /* postalCodeCountryInfo Webservice */
    public function testPostalCodeCountryInfo() {
        // Test the list of the country where the postal code is available
        $t=$this->geo->postalCodeCountryInfo();
        $c=count($t->geonames);
        $this->assertGreaterThan(30,$c,'Is not a so big list');
    }

    /* getAltitude Webservice */
    public function testGetAltitude() {
        // Set the position
        $this->geo->set([
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756,
                'radius'=>1,
            ]
        ]);
        // Test a not existent method
        $t=$this->geo->getAltitude('fake');
        $this->assertEmpty($t,'It is not empty');

        // Test srtm1
        $t=(array) $this->geo->getAltitude('srtm1');
        $this->assertArrayHasKey('srtm1',$t,'Key not present');

        // Test srtm3
        $t=(array) $this->geo->getAltitude('srtm3');
        $this->assertArrayHasKey('srtm3',$t,'Key not present');

        // Test astergdem
        $t=(array) $this->geo->getAltitude('astergdem');
        $this->assertArrayHasKey('astergdem',$t,'Key not present');

        // Test gtopo30
        $t=(array) $this->geo->getAltitude('gtopo30');
        $this->assertArrayHasKey('gtopo30',$t,'Key not present');
    }


    /* countryCode Webservice */
    public function testCountryCode() {
        // Set the position
        $this->geo->set([
            'lang'=>'fr',
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756,
                'radius'=>1,
            ]
        ]);
        $t=(array) $this->geo->countryCode();
        $this->assertArrayHasKey('countryCode',$t,'Key not present');
    }

    /* ocean Webservice */
    public function testOcean() {
        // Set the position
        $this->geo->set([
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756,
                'radius'=>50,
            ]
        ]);
        $t=$this->geo->ocean();
        $this->assertEquals('2960856',$t->ocean->geonameId,'Not correct geonamesId for Celtic See');
    }

    /* timezone Webservice */
    public function testTimezone() {
        // Set the position
        $this->geo->set([
            'lang'=>'fr',
            'date'=>'last monday of september 2004',
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756,
                'radius'=>1,
            ]
        ]);
        $t=$this->geo->timezone();
        $this->assertArrayHasKey('timezoneId',(array) $t,'Key not present');
        $this->assertEquals('2004-09-27',$t->dates[0]->date,'Not correct geonamesId for the date');
    }

    /* neighbourhood Webservice (US only) */
    public function testNeighbourhood() {
        // Set the position outside US (wrong)
        $this->geo->set([
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756
            ]
        ]);
        $t=$this->geo->neighbourhood();
        $this->assertArrayHasKey('status',(array) $t,'Key not present');

        // Set the position inside US (right)
        $this->geo->set([
            'position'=>[
                'lat'=>40.78343,
                'lng'=>-73.96625
            ]
        ]);
        $t=$this->geo->neighbourhood();
        $this->assertEquals('NY',$t->neighbourhood->adminCode1,'Not correct adminCode');
    }

    /* countrySubdivision Webservice */
    public function testCountrySubdivision() {
        // Case of Object
        $this->geo->set([
            'lang'=>'de',
            'position'=>[
                'lat'=>47.3,
                'lng'=>10.2,
            ]
        ]);
        $t=$this->geo->countrySubdivision();
        $this->assertEquals('8',$t->countrySubdivision->code,'Not correct Code');

        // Case of array
        $this->geo->set([
            'lang'=>'de',
            'maxRows'=>100,
            'level'=>2,
            'position'=>[
                'lat'=>47.3,
                'lng'=>10.2,
                'radius'=>40
            ]
        ]);
        $t=$this->geo->countrySubdivision();
        $this->assertArrayHasKey('code',(array) $t->countrySubdivision[0],'Key not present');
    }


    /* findNearby Webservice */
    public function testFindNearby() {
        $this->geo->set([
            'maxRows'=>100,
            'position'=>[
                'lat'=>47.3,
                'lng'=>9,
                'radius'=>200
            ],
            'style'=>'SHORT',
            'featureCode'=>'ADM1',
            'localCountry'=>true,
        ]);
        $t=$this->geo->findNearby();
        $this->assertArrayHasKey('geonameId',(array) $t->geonames[0],'Key not present');
    }

    /* exetendedFindNearby Webservice */
    public function testExtendedFindNearby() {
        // Set the position in Ireland
        $this->geo->set([
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756,
                'radius'=>1,
            ]
        ]);
        $t=$this->geo->extendedFindNearby();
        $this->assertEquals('6295630',$t->geonames[0]->geonameId,'Not correct geonamesId for Earth');

        // Set the position in US
        $this->geo->set([
            'position'=>[
                'lat'=>37.451,
                'lng'=>-122.18,
            ]
        ]);
        $t=$this->geo->extendedFindNearby();
        $this->assertEquals('California',$t->address->adminName1,'Not correct address');

        // Set the position in Atlantic Ocean
        $this->geo->set([
            'position'=>[
                'lat'=>40.78343,
                'lng'=>-43.96625,
            ]
        ]);
        $t=$this->geo->extendedFindNearby();
        $this->assertEquals('3411923',$t->ocean->geonameId,'Not correct geonamesId for North Atlantic Ocean');
    }


    /* findNearbyPlaceName Webservice */
    public function testFindNearbyPlaceName() {
        // Set the position in Ireland
        $this->geo->set([
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756,
                'radius'=>1,
            ]
        ]);
        $t=$this->geo->findNearbyPlaceName();
        $this->assertEquals('2965140',$t->geonames[0]->geonameId,'Not correct geonamesId for Cork (Ireland)');
    }


    /* findNearbyPostalCodes Webservice */
    public function testFindNearbyPostalCodes() {
        // Use the postal code
        $this->geo->set([
            'position'=>[
                'radius'=>1,
            ]
        ]);
        $t=$this->geo->findNearbyPostalCodes('pl','05091');
        $this->assertEquals('PL',$t->postalCodes[0]->countryCode,'Not correct geonamesId for Poland');

        // Use the location (Switzerland)
        $this->geo->set([
            'position'=>[
                'lat'=>46.8525538,        'lng'=>10.4666838,
                'radius'=>30
            ],
            'style'=>'short',
            'maxRows'=>200,
        ]);
        $t=$this->geo->findNearbyPostalCodes();
        $this->assertEquals('CH',$t->postalCodes[0]->countryCode,'Not correct geonamesId for Switzerland');

        // Use the location (Switzerland) and filter for Italy
        $t=$this->geo->findNearbyPostalCodes('it');
        $this->assertEquals('IT',$t->postalCodes[0]->countryCode,'Not correct geonamesId for Italy');
    }


    /* findNearbyStreets Webservice */
    public function testFindNearbyStreets() {
        $this->geo->set([
            'position'=>[
                'lat'=>47.3,
                'lng'=>-122.18,
                'radius'=>1
            ],
        ]);
        $t=$this->geo->findNearbyStreets();
        $this->assertEquals('Lake Holm',$t->streetSegment[0]->placename,'Not correct geonamesId for Lake Holm');
    }


    /* findNearestIntersection Webservice */
    public function testFindNearestIntersection() {
        $this->geo->set([
            'position'=>[
                'lat'=>47.3,
                'lng'=>-122.18,
                'radius'=>1
            ],
        ]);
        $t=$this->geo->findNearestIntersection();
        $this->assertEquals('Lake Holm',$t->intersection->placename,'Not correct geonamesId for Lake Holm');
    }

    /* findNearestAddress Webservice */
    public function testFindNearestAddress() {
        // Test from preset
        $this->geo->set([
            'position'=>[
                'lat'=>47.3,
                'lng'=>-122.18,
                'radius'=>1
            ],
        ]);
        $t=$this->geo->findNearestAddress();
        $this->assertEquals('Lake Holm',$t->address->placename,'Not correct geonamesId for Lake Holm');

        $t=$this->geo->findNearestAddress([
            [
                'lat'=>38.569594,
                'lng'=>-121.483778,
            ],
            [
                'lat'=>37.451,
                'lng'=>-122.18,
            ],
        ]);
        $this->assertEquals('Sacramento',$t->address[0]->placename,'Not correct geonamesId for Sacramento');
        $this->assertEquals('Menlo Park',$t->address[1]->placename,'Not correct geonamesId for Menlo Park');
    }

    /* findNearestIntersectionOSM Webservice */
    public function testFindNearestIntersectionOSM() {
        $this->geo->set([
          'maxRows'=>1,
          'includeGeoName'=>true,
          'position'=>[
            'lat'=>51.8985,
            'lng'=>-8.4756,
            'radius'=>1
          ]
        ]);
        $t=$this->geo->findNearestIntersectionOSM();
        $this->assertEquals('IE',$t->intersection->countryCode,'Not correct street for IE');
    }

    /* findNearbyStreetsOSM Webservice */
    public function testFindNearbyStreetsOSM() {
        $this->geo->set([
          'maxRows'=>1,
          'position'=>[
            'lat'=>51.8985,
            'lng'=>-8.4756,
            'radius'=>1
          ]
        ]);
        $t=$this->geo->findNearbyStreetsOSM();
        $this->assertEquals('40950946',$t->streetSegment->wayId,'Not correct wayId');
    }

    /* findNearbyPOIsOSM Webservice */
    public function testFindNearbyPOIsOSM() {
        $this->geo->set([
          'maxRows'=>2,
          'position'=>[
            'lat'=>51.8985,
            'lng'=>-8.4756,
            'radius'=>1
          ]
        ]);
        $t=$this->geo->findNearbyPOIsOSM();
        $this->assertIsArray($t->poi,'Not array');
    }

    /* cities Webservice */
    public function testCities() {
        $this->geo->set([
            'maxRows'=>1,
            'geoBox'=>[
                'north'=>44.1,
                'south'=>-9.9,
                'east'=>55.2,
                'west'=>22.4,
            ]
        ]);
        $t=$this->geo->cities();
        $this->assertEquals('360630',$t->geonames[0]->geonameId,'Not correct geonamesId');
    }


    /* earthquakes Webservice */
    public function testEarthquakes() {
        $this->geo->set([
            'minMagnitude'=>7,
            'geoBox'=>[
                'north'=>44.1,
                'south'=>-9.9,
                'east'=>55.2,
                'west'=>22.4,
            ]
        ]);
        $t=$this->geo->earthquakes();
        $this->assertEquals('7.2',$t->earthquakes[0]->magnitude,'Not correct magnitude');
    }


    /* weather Webservice */
    public function testWeather() {
        $this->geo->set([
            'maxRows'=>1,
            'geoBox'=>[
                'north'=>44.1,
                'south'=>-9.9,
                'east'=>55.2,
                'west'=>22.4,
            ]
        ]);
        $t=$this->geo->weather();
        $this->assertArrayHasKey('ICAO',(array) $t->weatherObservations[0],'Key not present');
    }

    /* weatherIcao Webservice */
    public function testWeatherIcao() {
        $t=$this->geo->weatherIcao('EICK');
        $this->assertEquals('EICK',$t->weatherObservation->ICAO,'Not correct ICAO');
    }

    /* findNearByWeather Webservice */
    public function testFindNearByWeather() {
        $this->geo->set([
          'position'=>[
            'lat'=>51.8985,
            'lng'=>-8.4756,
            'radius'=>200
          ]
        ]);
        $t=$this->geo->findNearByWeather();
        $this->assertEquals('EICK',$t->weatherObservation->ICAO,'Not correct ICAO');
    }


    /* children Webservice */
    public function testChildren() {
        $t=$this->geo->children('3175395');
        $this->assertEquals('IT',$t->geonames[0]->countryCode,'Not correct countryCode');
    }

    /* hierarchy Webservice */
    public function testHierarchy() {
        $t=$this->geo->hierarchy('3175395');
        $this->assertEquals('6295630',$t->geonames[0]->geonameId,'Not correct geonameId');
    }

    /* siblings Webservice */
    public function testSiblings() {
        $t=$this->geo->siblings('2965139');
        $this->assertEquals('IE',$t->geonames[0]->countryCode,'Not correct countryCode');
    }

    /* neighbours Webservice */
    public function testNeighbours() {
        // Test with id
        $t=$this->geo->neighbours('3175395');
        $this->assertEquals(6,$t->totalResultsCount,'Not correct result');
        // Test with cc
        $t=$this->geo->neighbours('it');
        $this->assertEquals(6,$t->totalResultsCount,'Not correct result');
    }


    /* contains Webservice */
    public function testContains() {
        $this->geo->set([
            'featureClass'=>'P',
            'featureCode'=>['PPLL','PPL'],
        ]);
        $t=$this->geo->contains('6539972');
        $this->assertEquals('IT',$t->geonames[0]->countryCode,'Not correct countryCode');

    }

    /* postalCodeLookup Webservice */
    public function testPostalCodeLookup() {
        $t=$this->geo->postalCodeLookup('05091','kr');
        $this->assertEquals('KR',$t->postalcodes[0]->countryCode,'Not correct countryCode');
    }

    /* geoCodeAddress Webservice */
    public function testGeoCodeAddress() {
        $t=$this->geo->geoCodeAddress(
            'Main',
            'us',
            '4217'
        );
        $this->assertEquals('Kentucky',$t->address->adminName1,'Not correct adminName1');
    }


    /* address Webservice */
    public function testAddress() {
        $this->geo->set([
            'position'=>[
                'lat'=>52.358,
                'lng'=>4.881,
                'radius'=>500
            ],
            'maxRows'=>2
        ]);
        $t=$this->geo->address();
        $this->assertEquals('NL',$t->address[0]->countryCode,'Not correct countryCode');
    }

    /* wikipediaBoundingBox Webservice */
    public function testwikipediaBoundingBox() {
        $this->geo->set([
            'maxRows'=>10,
            'geoBox'=>[
                'north'=>44.1,
                'south'=>-9.9,
                'east'=>55.2,
                'west'=>22.4,
            ]
        ]);
        $t=$this->geo->wikipediaBoundingBox();
        $this->assertArrayHasKey('wikipediaUrl',(array) $t->geonames[0],'Key not present');
    }

    /* findNearbyWikipedia Webservice */
    public function testFindNearbyWikipedia() {
        // Use the postal code
        $this->geo->set([
            'position'=>[
                'radius'=>20,
                'maxRows'=>1,
            ]
        ]);
        $t=$this->geo->findNearbyWikipedia('pl','05091');
        $this->assertArrayHasKey('wikipediaUrl',(array) $t->geonames[0],'Key not present');

        // Use the location
        $this->geo->set([
            'position'=>[
                'lat'=>51.8985,
                'lng'=>-8.4756
            ]
        ]);
        $t=$this->geo->findNearbyWikipedia('pl','05091');
        $this->assertArrayHasKey('wikipediaUrl',(array) $t->geonames[0],'Key not present');
    }


    /* wikipediaSearch Webservice */
    public function testWikipediaSearch() {
        $this->geo->set([
            'lang'=>'en',
            'maxRows'=>200
        ]);
        $t=$this->geo->wikipediaSearch([
            'title'=>'Cork',
            'place'=>'Saints Peter and Paul',
        ]);
        $this->assertArrayHasKey('wikipediaUrl',(array) $t->geonames[0],'Key not present');
    }

    /* postalCodeSearch Webservice */
    public function testPostalCodeSearch() {
        $this->geo->set([
            'geoBox'=>false,
            'maxRows'=>10,
            'charset'=>'UTF-8',
            'isReduced'=>false,
            'style'=>'full'
        ]);

        $t=$this->geo->postalCodeSearch([
            'postalcode'=>'091',
            'placename'=>'cork',
            'operator'=>'or',
            'countryBias'=>'ie'
        ]);
        $this->assertArrayHasKey('postalCode',(array) $t->postalCodes[0],'Key not present');
    }

}
