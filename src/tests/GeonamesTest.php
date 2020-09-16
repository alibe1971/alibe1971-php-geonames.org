<?php

namespace Alibe\Geonames\Tests;

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
        // username=pippo vendor/bin/phpunit src/tests
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
        $this->assertEquals('Italy',$t->name,'Not correct geonamesId');

        // Change language Italian
        $this->geo->set([
            'lang'=>'it'
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italia',$t->name,'Not correct geonamesId');

        // Reset language
        $this->geo->set([
            'lang'=>false
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italy',$t->name,'Not correct geonamesId');

        // Change language Francese
        $this->geo->set([
            'lang'=>'fr'
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italie',$t->name,'Not correct geonamesId');

        // Reset all
        $this->geo->reset();
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italy',$t->name,'Not correct geonamesId');
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

}
