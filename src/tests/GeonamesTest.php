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
        // Change language
        $this->geo->set([
            'lang'=>'it'
        ]);
        $t=$this->geo->get('3175395');
        $this->assertEquals('Italia',$t->name,'Not correct geonamesId');
    }

}
