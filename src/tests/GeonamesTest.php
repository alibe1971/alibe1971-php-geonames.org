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
    public function postalCodeCountryInfo() {
        // Test the list of the country where the postal code is available
        $t=$this->geo->postalCodeCountryInfo();
        $c=count($t->geonames);
        $this->assertGreaterThan(30,$c,'Is not a so big list');
    }

}