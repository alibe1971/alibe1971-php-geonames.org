<?php

namespace Alibe\Geonames;

use Alibe\Geonames\Lib\Exec;

class geonames {
    /** @var array Default request options */
    protected $conn;
    protected $defSet;

    /** @var class the execution class */
    protected $exe;

    /** @var string the geonames.org username */
    protected $clID;

    /**
     * Constructor to get the configuration skeletron
     * Example of call
     *     $geo = new Alibe\Geonames\geonames();
     *
    */
    public function __construct($clID) {
        $this->conn=include('Config/basic.php');
        $this->defSet=$this->conn['settings'];
        $this->clID=$clID;
        $this->set();
    }

    /**
     * Set the call parameters.
     * The call settings remain for the execution of the script and they are used to create complex query to geonames.org api site.
     *
     *
     * @param array $arr The array with the parameters to set
     * Example of basic call
     *     $geo->set([
     *         'format' => 'object',
     *         'lang' => 'en'
     *     ]);
     * "format" is the format of the return for every call;
     *          it can be "object" (deafult) or "array";
     *          it is used for every call except for the rawCall, if that contain the parameter 'asIs' (see below)
     * "lang" is optional; if it is present it is used by geonames.org api to translate the name of the location (where is possible)
     *
     * @return this object
    */
    public function set($arr=[]) {
        if(!empty($arr)) {
            foreach ($arr as $k => $v) {
                if($v===false || $v===null) {
                    $arr[$k]=$this->defSet[$k];
                }
            }
        }
        $this->conn['settings']=array_replace_recursive($this->conn['settings'],$arr);
        $this->exe=new Exec($this->clID,$this->conn);
        return $this;
    }

    /**
     * Reset the call parameters.
     * It set all the parameters at the default state.
     *
     * Example of basic call
     *     $geo->reset();
     *
     * @return this object
    */
    public function reset() {
        $this->conn['settings']=$this->defSet;
        $this->exe=new Exec($this->clID,$this->conn);
        return $this;
    }

  /***********************************/
 /* Geonames.org Original functions */
/***********************************/

      /************/
     /* RAW CALL */
    /************/
    /**
     * Raw call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/ws-overview.html
     * The call is prepared and done without any filters.
     * From the set configuration it take the parameters "clID","format" and "lang" (if present).
     * If the parameter "lang" is present in the call, ot use the call parameter instead of the configuration parameter.
     *
     * @param string $command, the main command for the api
     * @param array $params, the array with the parameters to use for the call
     * @param string $format, (optional, default as false) if it is set as false, the call ignore the format parameter and it return the raw response form the api call; else if it is set as string, it has to be 'object' or 'array'.
     * Example of call
     *     $geo->rawCall(
     *         'getJSON',
     *         [
     *            'geonameId' => 2643743,
     *         ],
     *         true
     *     );
     *
     * @return object|array|response of the call without filters.
    */
    public function rawCall($command,$params=[],$format=false) {
        $fCall='JSON';
        $asIs=true;
        $preset=$this->conn['settings']['format'];
        if($format) {
            $asIs=false;
            if($format===true) {
                $format=$preset;
            }
            $this->set([
                'format'=>$format
            ]);
            unset($params['type']);
            $command=preg_replace('/JSON$/','',$command);
            $command=preg_replace('/XML$/','',$command);
            $command=preg_replace('/RDF$/','',$command);
            $command=preg_replace('/CSV$/','',$command);
            $command=preg_replace('/RSS$/','',$command);
            if(preg_match('/^rssToGeo/',$command)) {
                $fCall='RSS';
            }
        } else {
            $fCall='';
        }
        $call=$this->exe->get([
            'cmd'=>$command,
            'query'=>$params,
            'asIs'=>$asIs
        ],$fCall);
        $this->set([
            'format'=>$preset
        ]);
        return $call;
    }

      /******************/
     /* Get Webservice */
    /******************/
    /**
     * Call to get the geonameId properties form geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#get
     *
     * @param integer $id, the geonameId in the database of geonames.org.
     * Example of call (it assumes the main set is already done).
     *     //Set the parameters (optional)
     *     $geo->set([
     *        'lang'=>'en',
     *        'style'=>'full',
     *     ]);
     *     // Call it
     *     $geo->get(3175395); // Example for Italy
     *
     * @return object|array of the call.
    */
    public function get($id) {
        return $this->exe->get([
            'cmd'=>'get',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

      /*********************/
     /* Search Webservice */
    /*********************/
    /**
     * Search call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/geonames-search.html
     *
     * The search parameters has to be set previusly using the "set" method inside the section array 'search';
     * (See the geonames documentation)
     *     //Set the search parameters
     *     $geo->set([
     *        'search'=>[
     *            'q'=>'london',
     *        ]
     *     ]);
     *     // Call it
     *     $geo->search();
     *
     * @return object|array of the call.
    */
    public function search() {
        $query=$this->conn['settings']['search'];
        unset($query['type']);
        return $this->exe->get([
            'cmd'=>'search',
            'query'=>$query
        ]);
    }

      /***********************/
     /* rssToGeo Webservice */
    /***********************/
    /**
     * rssToGeo search call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/rss-to-georss-converter.html
     *
     * @param string $url, the url of the rss feed.
     * The rssToGeo parameters has to be set previusly using the "set" method inside the section array 'rssToGeo';
     *
     * Example of call (it assumes the main set is already done).
     *     //Set the rssToGeo parameters
     *     $geo->set([
     *        'rssToGeo'=>[
     *          'feedLanguage' => false,
     *          'type' => false,
     *          'geoRSS' => false,
     *          'addUngeocodedItems' => false,
     *          'country' => false,
     *        ]
     *     ]);
     *     // Call it
     *     $geo->rssToGeo('https://rss.nytimes.com/services/xml/rss/nyt/World.xml');
     *
     * @return object|array of the call.
    */
    public function rssToGeo($url) {
      $query=$this->conn['settings']['rssToGeo'];
      $query['feedUrl']=$url;
      unset($query['type']);
      return $this->exe->get([
          'cmd'=>'rssToGeo',
          'query'=>$query,
          'preOutput'=>'rssConvert'
      ],'RSS');
    }

      /*******************************/
     /* Place Hierarchy Webservices */
    /*******************************/
    /**
     * Children call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/place-hierarchy.html#children
     *
     * @param integer $id, the geonameId in the database of geonames.org.
     * @param string $hrk, the kind of hierarchy in the database of geonames.org.
     *
     * Example of call (it assumes the main set is already done).
     *     //Set the search parameters
     *     $geo->set([
     *        'maxRows'=>30
     *     ]);
     *     // Call it
     *     $geo->children(3175395);
     *
     * @return object|array of the call.
    */
    public function children($id,$hrk=false) {
        return $this->exe->get([
            'cmd'=>'children',
            'query'=>[
                'geonameId'=>$id,
                'hierarchy'=>$hrk
            ]
        ]);
    }

    /**
     * Hierarchy call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/place-hierarchy.html#hierarchy
     *
     * @param integer $id, the geonameId in the database of geonames.org.
     *
     * Example of call (it assumes the main set is already done).
     *     // Call it
     *     $geo->hierarchy(3175395);
     *
     * @return object|array of the call.
    */
    public function hierarchy($id) {
        return $this->exe->get([
            'cmd'=>'hierarchy',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

    /**
     * Siblings call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/place-hierarchy.html#siblings
     *
     * @param integer $id, the geonameId in the database of geonames.org.
     *
     * Example of call (it assumes the main set is already done).
     *     // Call it
     *     $geo->siblings(3175395);
     *
     * @return object|array of the call.
    */
    public function siblings($id) {
        return $this->exe->get([
            'cmd'=>'siblings',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

    /**
     * Neighbours call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/place-hierarchy.html#neighbours
     *
     * @param integer|string $id, the geonameId or the countri code in the database of geonames.org.
     *
     * Example of call (it assumes the main set is already done).
     *     // Call it
     *     $geo->neighbours(3175395);
     *
     * @return object|array of the call.
    */
    public function neighbours($id) {
        $query=[
            'geonameId'=>$id
        ];
        if(intval($id)==0) {
            $query=[
                'country'=>$id
            ];
        }
        return $this->exe->get([
            'cmd'=>'neighbours',
            'query'=>$query
        ]);
    }

    /**
     * Contains call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/place-hierarchy.html#contains
     *
     * @param integer|string $id, the geonameId or the countri code in the database of geonames.org.
     *
     * Example of call (it assumes the main set is already done).
     *     //Set the filter parameters (optional)
     *     $geo->set([
     *        'featureClass'=>'P',
     *        'featureCode'=>'PPLL',
     *     ]);
     *     // Call it
     *     $geo->contains(6539972);
     *
     * @return object|array of the call.
    */
    public function contains($id) {
        return $this->exe->get([
            'cmd'=>'contains',
            'query'=>[
                'geonameId'=>$id,
                'featureClass'=>$this->conn['settings']['featureClass'],
                'featureCode'=>$this->conn['settings']['featureCode'],
            ]
        ]);
    }



      /**********************/
     /* GeoBox Webservices */
    /**********************/
    /**
     * The geobox is an area where to search the data.
     * The geobox has to be set before to call the methods that use it.
     *     //Set the geobox
     *     $geo->set([
     *          'geoBox'=>[
     *               'north'=>44.1,
     *               'south'=>-9.9,
     *               'east'=>22.4,
     *               'west'=>55.2,
     *          ]
     *     ]);
    */

    /**
     * Cities inside Geobox call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/JSON-webservices.html
     *
     *
     * Example of call (it assumes the main set is already done).
     *     GEOBOX parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'lang'=>'en',     // (optional)
     *        'maxRows'=>200,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->cities();
     *
     * @return object|array of the call.
    */
    public function cities() {
        return $this->execByGeoBox('cities',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }

    /**
     * Earthquakes inside Geobox call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/JSON-webservices.html#earthquakesJSON
     *
     *
     * Example of call (it assumes the main set is already done).
     *     GEOBOX parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>200,   // (optional)
     *        'date'=>'today',  // (optional, filter the event before the date ,'Y-m-d' format, default 'today')
     *        'minMagnitude'=>'2.4',  // (optional, filter the event with magnitude greather than)
     *     ]);
     *     // Call it
     *     $geo->earthquakes();
     *
     * @return object|array of the call.
    */
    public function earthquakes() {
        return $this->execByGeoBox('earthquakes',[
            'maxRows'=>$this->conn['settings']['maxRows'],
            'date'=>date('Y-m-d',strtotime($this->conn['settings']['date'])),
            'minMagnitude'=>$this->conn['settings']['minMagnitude'],
        ]);
    }


      /*************************/
     /* Position Webservices  */
    /*************************/
    /**
     * The position settings is contains the coordinates for the position and the radius (in Km) where to search the data.
     * The position has to be set before to call the methods that use it.
     *     //Set the position
     *     $geo->set([
     *          'position'=>[
     *               'lat'=>40.78343,
     *               'lng'=>-73.96625,
     *               'radius'=>1
     *          ]
     *     ]);
    */

    /**
     * CountryCode from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#countrycode
     *
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *     ]);
     *     // Call it
     *     $geo->countryCode();
     *
     * @return object|array of the call.
    */
    public function countryCode() {
        return $this->execByPosition('countryCode');
    }

    /**
     * ocean from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#ocean
     *
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     // Call it
     *     $geo->ocean();
     *
     * @return object|array of the call.
    */
    public function ocean() {
        return $this->execByPosition('ocean');
    }


    /**
     * Timezone from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#timezone
     *
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *        'date'=>'today',  // (optional, filter the event before the date ,'Y-m-d' format, default 'today')
     *     ]);
     *     // Call it
     *     $geo->timezone();
     *
     * @return object|array of the call.
    */
    public function timezone() {
        return $this->execByPosition('timezone',[
            'date'=>date('Y-m-d',strtotime($this->conn['settings']['date'])),
        ]);
    }

    /**
     * Neighbourhood from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#neighbourhood
     *
     * RESTRICTION: US LOCATION ONLY
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     // Call it
     *     $geo->neighbourhood();
     *
     * @return object|array of the call.
    */
    public function neighbourhood() {
        return $this->execByPosition('neighbourhood');
    }


    /**
     * CountrySubdivision from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#countrysubdiv
     *
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *     ]);
     *     // Call it
     *     $geo->countrySubdivision();
     *
     * @return object|array of the call.
    */
    public function countrySubdivision() {
        return $this->execByPosition('countrySubdivision');
    }

    /**
     * FindNearby from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#findNearby
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'featureClass'=>'T',
     *        'featureCode'=>'PASS',
     *     ]);
     *     // Call it
     *     $geo->findNearby();
     *
     * @return object|array of the call.
    */
    public function findNearby() {
        return $this->execByPosition('findNearby',[
            'featureClass'=>$this->conn['settings']['featureClass'],
            'featureCode'=>$this->conn['settings']['featureCode'],
        ]);
    }

    /**
     * ExtendedFindNearby from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#extendedFindNearby
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     // Call it
     *     $geo->extendedFindNearby();
     *
     * @return object|array of the call.
    */
    public function extendedFindNearby() {
        return $this->execByPosition('extendedFindNearby');
    }

    /**
     * FindNearbyPlaceName from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#findNearbyPlaceName
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *        'localCountry'=>true,
     *        'cities'=>'cities5000',
     *        'style'=>'FULL',
     *     ]);
     *     // Call it
     *     $geo->findNearbyPlaceName();
     *
     * @return object|array of the call.
    */
    public function findNearbyPlaceName() {
        return $this->execByPosition('findNearbyPlaceName',[
            'localCountry'=>$this->conn['settings']['localCountry'],
            'cities'=>$this->conn['settings']['cities'],
            'style'=>$this->conn['settings']['style'],
        ]);
    }


    /**
     * findNearbyPostalCodes from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#findNearbyPostalCodes
     *
     * @param string $cc, the country code.
     * @param string $zip, the postal code.
     *
     * If Country code and postal code are set,
     * then use them.
     * else use the position sets and country code if it is set.
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set (if needed)
     *
     *     //PRESET IN CASE OF POSTAL CODE
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *        'maxRows'=>10,  // (optional)
     *        'position'=>[
     *              'radius'=>1 // (optional)
     *         ]
     *     ]);
     *
     *     //PRESET IN CASE OF POSITION
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *        'maxRows'=>10, // (optional)
     *        'style'=>'FULL', // (optional)
     *        'localCountry'=>true, // (optional)
     *        'isReduced'=>true, // (optional)
     *     ]);
     *
     *     // Call it
     *     $geo->findNearbyPostalCodes();
     *
     * @return object|array of the call.
    */
    public function findNearbyPostalCodes($cc=false,$zip=false) {
        if($cc && $zip) {
            $query=[
                'country'=>$cc,
                'postalcode'=>$zip,
                'maxRows'=>$this->conn['settings']['maxRows'],
                'radius'=>$this->conn['settings']['position']['radius'],
            ];
        } else {
            $query=$this->conn['settings']['position'];
            $query['maxRows']=$this->conn['settings']['maxRows'];
            if($cc) {
                $query['country']=$cc;
            }
            $query['style']=$this->conn['settings']['style'];
            $query['localCountry']=$this->conn['settings']['localCountry'];
            $query['isReduced']=$this->conn['settings']['isReduced'];
        }
        return $this->exe->get([
            'cmd'=>'findNearbyPostalCodes',
            'query'=>$query
        ]);
    }


    /**
     * FindNearbyStreets from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/maps/us-reverse-geocoder.html#findNearbyStreets
     *
     * RESTRICTION: US LOCATION ONLY
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>'en',   // (optional)
     *     ]);
     *     // Call it
     *     $geo->findNearbyStreets();
     *
     * @return object|array of the call.
    */
    public function findNearbyStreets() {
        return $this->execByPosition('findNearbyStreets',[
            'maxRows'=>$this->conn['settings']['maxRows'],
        ]);
    }

    /**
     * FindNearestIntersection from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/maps/us-reverse-geocoder.html#findNearestIntersection
     *
     * RESTRICTION: US LOCATION ONLY
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>10,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->findNearestIntersection();
     *
     * @return object|array of the call.
    */
    public function findNearestIntersection() {
        return $this->execByPosition('findNearestIntersection',[
            'maxRows'=>$this->conn['settings']['maxRows'],
            'filter'=>$this->conn['settings']['filter'],
        ]);
    }

    /**
     * FindNearestAddress from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/maps/us-reverse-geocoder.html#findNearestAddress
     *
     * RESTRICTION: US LOCATION ONLY
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>10,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->findNearestAddress();
     *
     * @return object|array of the call.
    */
    public function findNearestAddress() {
        return $this->execByPosition('findNearestAddress',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }

    /**
     * FindNearestIntersectionOSM from Position call to geonames.org using Open Street Map.
     * Geonames.org documentation: https://www.geonames.org/maps/osm-reverse-geocoder.html#findNearestIntersectionOSM
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>10,   // (optional)
     *        'includeGeoName'=> true // (optional)
     *     ]);
     *     // Call it
     *     $geo->findNearestIntersectionOSM();
     *
     * @return object|array of the call.
    */
    public function findNearestIntersectionOSM() {
        return $this->execByPosition('findNearestIntersectionOSM',[
            'maxRows'=>$this->conn['settings']['maxRows'],
            'includeGeoName'=>$this->conn['settings']['includeGeoName'],
        ]);
    }

    /**
     * FindNearbyStreetsOSM from Position call to geonames.org using Open Street Map.
     * Geonames.org documentation: https://www.geonames.org/maps/osm-reverse-geocoder.html#findNearbyStreetsOSM
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>10,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->findNearbyStreetsOSM();
     *
     * @return object|array of the call.
    */
    public function findNearbyStreetsOSM() {
        return $this->execByPosition('findNearbyStreetsOSM',[
            'maxRows'=>$this->conn['settings']['maxRows'],
        ]);
    }

    /**
     * FindNearbyPOIsOSM from Position call to geonames.org using Open Street Map.
     * The point of interest.
     * Geonames.org documentation: https://www.geonames.org/maps/osm-reverse-geocoder.html#findNearbyPOIsOSM
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>10,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->findNearbyPOIsOSM();
     *
     * @return object|array of the call.
    */
    public function findNearbyPOIsOSM() {
        return $this->execByPosition('findNearbyPOIsOSM',[
            'maxRows'=>$this->conn['settings']['maxRows'],
        ]);
    }


      /***********************/
     /* Weather Webservices */
    /***********************/
    /**
     * Weather station inside Geobox call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/JSON-webservices.html#weatherJSON
     *
     *
     * Example of call (it assumes the main set is already done).
     *     GEOBOX parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>200,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->weather();
     *
     * @return object|array of the call.
    */
    public function weather() {
        return $this->execByGeoBox('weather',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }

    /**
     * weatherIcao. Call to get the weather station with ICAO code.
     * Geonames.org documentation: https://www.geonames.org/export/JSON-webservices.html#weatherIcaoJSON
     *
     * @param string $icaoCode, the ICAO (International Civil Aviation Organization) code.
     * Example of call (it assumes the main set is already done).
     *     // Call it
     *     $geo->weatherIcao('EICK'); // Example for Cork
     *
     * @return object|array of the call.
    */
    public function weatherIcao($icaoCode) {
        return $this->exe->get([
            'cmd'=>'weatherIcao',
            'query'=>[
                'ICAO'=>$icaoCode
            ]
        ]);
    }

    /**
     * findNearByWeather from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/JSON-webservices.html#findNearByWeatherJSON
     *
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     // Call it
     *     $geo->findNearByWeather();
     *
     * @return object|array of the call.
    */
    public function findNearByWeather() {
        return $this->execByPosition('findNearByWeather');
    }


      /*************************/
     /* Altitude Webservices  */
    /*************************/
    /**
     * Altitude from Position call to geonames.org using srtm1 method.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#srtm1
     *
     *
     * Example of call
     *     POSITION parameters already set
     *     // Call it
     *     $geo->srtm1();
     *
     * @return object|array of the call.
    */
    public function srtm1() {
        return $this->execByPosition('srtm1');
    }

    /**
     * Altitude from Position call to geonames.org using srtm3 method.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#srtm3
     *
     *
     * Example of call
     *     POSITION parameters already set
     *     // Call it
     *     $geo->srtm3();
     *
     * @return object|array of the call.
    */
    public function srtm3() {
        return $this->execByPosition('srtm3');
    }

    /**
     * Altitude from Position call to geonames.org using astergdem method.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#astergdem
     *
     *
     * Example of call
     *     POSITION parameters already set
     *     // Call it
     *     $geo->astergdem();
     *
     * @return object|array of the call.
    */
    public function astergdem() {
        return $this->execByPosition('astergdem');
    }

    /**
     * Altitude from Position call to geonames.org using gtopo30 method.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#gtopo30
     *
     *
     * Example of call
     *     POSITION parameters already set
     *     // Call it
     *     $geo->gtopo30();
     *
     * @return object|array of the call.
    */
    public function gtopo30() {
        return $this->execByPosition('gtopo30');
    }


      /*************************/
     /* Wikipedia Webservices */
    /*************************/
    /**
     * Wikipedia itmes inside Geobox call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/wikipedia-webservice.html#wikipediaBoundingBox
     *
     *
     * Example of call (it assumes the main set is already done).
     *     GEOBOX parameters already set
     *     //Set the filter parameters
     *     $geo->set([
     *        'maxRows'=>10,   // (optional)
     *        'lang'=>'en',   // (optional)
     *     ]);
     *     // Call it
     *     $geo->wikipediaBoundingBox();
     *
     * @return object|array of the call.
    */
    public function wikipediaBoundingBox() {
        return $this->execByGeoBox('wikipediaBoundingBox',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }

    /**
     * Wikipedia items from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/wikipedia-webservice.html#findNearbyWikipedia
     *
     * @param string $cc, the country code.
     * @param string $zip, the postal code.
     *
     * If Country code and postal code are set,
     * then use them.
     * else use the position sets and country code if it is set.
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set (if needed)
     *
     *     //PRESET IN CASE OF POSTAL CODE
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *        'maxRows'=>10,  // (optional)
     *        'position'=>[
     *              'radius'=>1 // (optional)
     *         ]
     *     ]);
     *
     *     //PRESET IN CASE OF POSITION
     *     $geo->set([
     *        'lang'=>'en',   // (optional)
     *        'maxRows'=>10, // (optional)
     *     ]);
     *
     *     // Call it
     *     $geo->findNearbyWikipedia();
     *
     * @return object|array of the call.
    */
    public function findNearbyWikipedia($cc=false,$zip=false) {
        if($cc && $zip) {
            $query=[
                'country'=>$cc,
                'postalcode'=>$zip,
                'maxRows'=>$this->conn['settings']['maxRows'],
                'radius'=>$this->conn['settings']['position']['radius'],
            ];
        } else {
            $query=$this->conn['settings']['position'];
            $query['maxRows']=$this->conn['settings']['maxRows'];
            if($cc) {
                $query['country']=$cc;
            }
        }
        return $this->exe->get([
            'cmd'=>'findNearbyWikipedia',
            'query'=>$query
        ]);
    }

    /**
     * Search call to geonames.org for Wilipedia items.
     * Geonames.org documentation: https://www.geonames.org/export/wikipedia-webservice.html#wikipediaSearch
     *
     * The search parameters has to be set previusly using the "set" method inside the section array 'wikiSearch';
     * In the wikiSearch there are two properties that are each other alternative
     * -"title" search inside the title (preeminent)
     * -"q" search inside the body
     *
     *     //Set the wikiSearch parameters
     *     $geo->set([
     *        'wikiSearch'=>[
     *            'q'=>'london',
     *        ]
     *     ]);
     *     OR
     *     $geo->set([
     *        'wikiSearch'=>[
     *            'title'=>'london',
     *        ]
     *     ]);
     *     Other parameters
     *     $geo->set([
     *        'lang'=>'en' (optional)
     *        'maxRows'=>20 (optional)
     *     ]);
     *     // Call it
     *     $geo->search();
     *
     * @return object|array of the call.
    */
    public function wikipediaSearch() {
        $query=[
            'maxRows'=>$this->conn['settings']['maxRows']
        ];
        if(isSet($this->conn['settings']['wikiSearch']) && is_array($this->conn['settings']['wikiSearch'])) {
            $search=$this->conn['settings']['wikiSearch'];
            if(isSet($search['title']) && $search['title']) {
                $query['title']=$search['title'];
            } elseif (isSet($search['q']) && $search['q']) {
                $query['q']=rawurlencode(utf8_encode($search['q']));
            }
        }
        return $this->exe->get([
            'cmd'=>'wikipediaSearch',
            'query'=>$query
        ]);
    }


      /*****************************************/
     /* Postal code and countries Webservices */
    /*****************************************/
    /**
     * Country params or country list call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#countryInfo
     *
     * @param string|array $id, the iso  ISO-3166 country code (2 letter) (optional). By default it return the list of the countries.
     * If is present it can be a string (for a single country) or an array (for multiple countries).
     * Example of call
     *     //Set the optional parameters
     *     $geo->set([
     *        'lang'=> 'en' (optional)
     *     ]);
     *     // Call it
     *     $geo->countryInfo('ie');
     *     // Or
     *     $geo->countryInfo(['ie','it']);
     *
     * @return object|array of the call.
    */
    public function countryInfo($cc=false) {
        return $this->exe->get([
            'cmd'=>'countryInfo',
            'query'=>[
                'country'=>$cc
            ]
        ]);
    }
    public function postalCodeCountryInfo() {
        return $this->exe->get([
            'cmd'=>'postalCodeCountryInfo'
        ]);
    }

    /**
     * Postal Code lookup call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#postalCodeLookupJSON
     *
     * @param string $zip, The postal code
     * @param string $cc, The country code filter (optional)
     * Example of call (it assumes the main set is already done).
     *     //Set the optional parameters
     *     $geo->set([
     *        'maxRows'=> 20 (optional)
     *        'charset'=> 'UTF-8' (optional default 'UTF-8')
     *     ]);
     *     // Call it
     *     $geo->postalCodeLookup('T12');
     *
     * @return object|array of the call.
    */
    public function postalCodeLookup($zip,$cc=false) {
        return $this->exe->get([
            'cmd'=>'postalCodeLookup',
            'query'=>[
                'country'=>$cc,
                'postalcode'=>$zip,
                'maxRows'=>$this->conn['settings']['maxRows'],
                'charset'=>$this->conn['settings']['charset'],
            ]
        ]);
    }


    /**
     * Postal Code or Place search call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/export/web-services.html#postalCodeSearch
     *
     * @param string $req, The postal code or the place name
     *
     * The other search parameters has to be set previusly using the "set" method inside the section array 'postalplace';
     *
     * Differentemente da geonames.org la struttura della richiesta è formulata nel modo seguente:
     * Definition of the request. Mandatory. There should be only one parameter such as 'true'; if more parameters are present as 'true', the order of prevalence follows that shown in the example.
     *     //Set the main search parameters
     *     $geo->set([
     *        'postalplace' => [
     *            'postalcode' => true,
     *            'postalcode_startsWith' => false,
     *            'placename' => false,
     *            'placename_startsWith' => false,
     *        ]
     *     ]);
     * Definitions of specific options (always within the 'postalplace' section). Optional.
     *     //Set the option search parameters
     *     $geo->set([
     *        'postalplace' => [
     *            'country' => 'IT',
     *            'countryBias' => false,
     *            'style' => 'FULL',
     *            'operator' => 'AND',
     *            'charset' => 'UTF-8',
     *            'isReduced' => false,
     *        ]
     *     ]);
     * Definitions of the maxRow.  Optional.
     *     //Set the option search parameters
     *     $geo->set([
     *        'maxRows' => 10
     *     ]);
     * Definitions of the geobox.  Optional.
     *     //Set the option search parameters
     *     $geo->set([
     *          'geoBox'=>[
     *               'north'=>44.1,
     *               'south'=>-9.9,
     *               'east'=>22.4,
     *               'west'=>55.2,
     *          ]
     *     ]);
     *
     *     // Call it
     *     $geo->postalCodeSearch('05035');
     *
     * @return object|array of the call.
    */
    public function postalCodeSearch($req) {
        $req=rawurlencode($req);
        $query=$this->conn['settings']['geoBox'];
        $query['maxRows']=$this->conn['settings']['maxRows'];
        $pp=$this->conn['settings']['postalplace'];
        if(isSet($pp['postalcode']) && $pp['postalcode']) {
            $query['postalcode']=$req;
        } elseif(isSet($pp['postalcode_startsWith']) && $pp['postalcode_startsWith']) {
            $query['postalcode_startsWith']=$req;
        } elseif(isSet($pp['placename']) && $pp['placename']) {
            $query['placename']=$req;
        } elseif(isSet($pp['placename_startsWith']) && $pp['placename_startsWith']) {
            $query['placename_startsWith']=$req;
        }
        unset($pp['postalcode']);
        unset($pp['postalcode_startsWith']);
        unset($pp['placename']);
        unset($pp['placename_startsWith']);
        $query=array_merge($query,$pp);
        return $this->exe->get([
            'cmd'=>'postalCodeSearch',
            'query'=>$query
        ]);
    }

      /***********************/
     /* Address Webservices */
    /***********************/
    /**
     * Address from Position call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/maps/addresses.html#address
     *
     * RESTRICTION: service available only for some countries
     * (see the geonames documentation)
     *
     * Example of call (it assumes the main set is already done).
     *     POSITION parameters already set
     *     //Set the optional parameters
     *     $geo->set([
     *        'maxRows'=>20,   // (optional)
     *     ]);
     *     // Call it
     *     $geo->address();
     *
     * @return object|array of the call.
    */
    public function address() {
        return $this->execByPosition('address',[
            'maxRows'=>$this->conn['settings']['maxRows'],
        ]);
    }

    /**
     * Search address call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/maps/addresses.html#geoCodeAddress
     *
     * RESTRICTION: service available only for some countries
     * (see the geonames documentation)
     *
     * @param string $address, The address to search.
     * The address optional search parameters has to be set previusly using the "set" method inside the section array 'address';
     *
     * Example of call (it assumes the main set is already done).
     *     //Set the search parameters
     *     $geo->set([
     *        'address'=>[
     *            'country'=>'NL', (optional)
     *            'postalCode'=>'false, (optional)
     *        ]
     *     ]);
     *     // Call it
     *     $geo->geoCodeAddress('Museumplein 6');
     *
     * @return object|array of the call.
    */
    public function geoCodeAddress($address) {
        return $this->exe->get([
            'cmd'=>'geoCodeAddress',
            'query'=>[
                'q'=>rawurlencode($address),
                'country'=>$this->conn['settings']['address']['country'],
                'postalcode'=>$this->conn['settings']['address']['postalCode']
            ]
        ]);
    }

    /**
     * Search streetName call to geonames.org.
     * Geonames.org documentation: https://www.geonames.org/maps/addresses.html#streetNameLookup
     *
     * RESTRICTION: service available only for some countries
     * (see the geonames documentation)
     *
     * @param string $address, The address to search.
     * The address optional search parameters has to be set previusly using the "set" method inside the section array 'address';
     *
     * Example of call (it assumes the main set is already done).
     *     //Set the search parameters
     *     $geo->set([
     *        'address'=>[
     *            'country'=>'AU', (optional)
     *            'postalCode'=>''6530', (optional)
     *            'adminCode1'=>'false, (optional)
     *            'adminCode2'=>'false, (optional)
     *            'adminCode3'=>'false, (optional)
     *            'isUniqueStreetName'=>'false, (optional)
     *        ]
     *     ]);
     *     // Call it
     *     $geo->streetNameLookup('Museum');
     *
     * @return object|array of the call.
    */
    public function streetNameLookup($address) {
        return $this->exe->get([
            'cmd'=>'streetNameLookup',
            'query'=>[
                'q'=>rawurlencode($address),
                'country'=>$this->conn['settings']['address']['country'],
                'postalcode'=>$this->conn['settings']['address']['postalCode'],
                'adminCode1'=>$this->conn['settings']['address']['adminCode1'],
                'adminCode2'=>$this->conn['settings']['address']['adminCode2'],
                'adminCode3'=>$this->conn['settings']['address']['adminCode3'],
                'isUniqueStreetName'=>$this->conn['settings']['address']['isUniqueStreetName']
            ]
        ]);
    }

      /*************************/
     /* Execute by position   */
    /*************************/
    public function execByPosition($cmd, $ar=[]) {
        $query=array_merge($this->conn['settings']['position'],$ar);
        return $this->exe->get([
            'cmd'=>$cmd,
            'query'=>$query
        ]);
    }

      /***********************/
     /* Execute by geoBox   */
    /***********************/
    public function execByGeoBox($cmd, $ar=[]) {
        $box=$this->conn['settings']['geoBox'];
        $query=array_merge($box,$ar);
        return $this->exe->get([
            'cmd'=>$cmd,
            'query'=>$query
        ]);
    }


    /*Continents*/
    public function continensGetList() {
        return $this->children('6295630');
    }

    /*Countries*/
    public function countriesGetList() {
        return $this->countryInfo();
    }

    public function countryGet($cc) {
        return $this->countryInfo($cc);
    }

}
