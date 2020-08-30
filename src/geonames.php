<?php

namespace Alibe\PhpGeonamesorg;

use Alibe\PhpGeonamesorg\Lib\Exec;

class geonames {

    protected $conn;
    protected $exe;

    public function __construct() {
        $this->conn=include('Config/basic.php');
    }

    public function set($arr=[]) {
        $this->conn['settings']=array_replace_recursive($this->conn['settings'],$arr);
        $this->exe=new Exec($this->conn);
        return $this;
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

  /***********************************/
 /* Geonames.org Original functions */
/***********************************/

    public function get($id) {
        return $this->exe->get([
            'cmd'=>'get',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

    public function countryInfo($cc=false) {
        return $this->exe->get([
            'cmd'=>'countryInfo',
            'query'=>[
                'country'=>$cc
            ]
        ]);
    }

      /*******************************/
     /* Place Hierarchy Webservices */
    /*******************************/
    public function children($id,$hrk=false) {
        return $this->exe->get([
            'cmd'=>'children',
            'query'=>[
                'geonameId'=>$id,
                'hierarchy'=>$hrk
            ]
        ]);
    }

    public function hierarchy($id) {
        return $this->exe->get([
            'cmd'=>'hierarchy',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

    public function siblings($id) {
        return $this->exe->get([
            'cmd'=>'siblings',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

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

    public function contains($id) {
        // [TODO] feature class and/or feature code
        return $this->exe->get([
            'cmd'=>'contains',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }



      /**********************/
     /* GeoBox Webservices */
    /**********************/
    public function cities() {
        return $this->execByGeoBox('cities',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }

    public function earthquakes() {
        return $this->execByGeoBox('earthquakes',[
            'maxRows'=>$this->conn['settings']['maxRows'],
            'date'=>date('Y-m-d',strtotime($this->conn['settings']['date'])),
            'minMagnitude'=>$this->conn['settings']['minMagnitude'],
        ]);
    }


      /***********************/
     /* Weather Webservices */
    /***********************/
    public function weather() {
        return $this->execByGeoBox('weather',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }
    public function weatherIcao($id) {
        return $this->exe->get([
            'cmd'=>'weatherIcao',
            'query'=>[
                'ICAO'=>$id
            ]
        ]);
    }

    public function findNearByWeather() {
        return $this->execByPosition('findNearByWeather');
    }

      /*************************/
     /* Altitude Webservices  */
    /*************************/
    public function srtm1() {
        return $this->execByPosition('srtm1');
    }
    public function srtm3() {
        return $this->execByPosition('srtm3');
    }
    public function astergdem() {
        return $this->execByPosition('astergdem');
    }
    public function gtopo30() {
        return $this->execByPosition('gtopo30');
    }

      /*************************/
     /* Position Webservices  */
    /*************************/
    public function countryCode() {
        return $this->execByPosition('countryCode');
    }
    public function ocean() {
        return $this->execByPosition('ocean');
    }
    public function timezone() {
        return $this->execByPosition('timezone',[
            'date'=>date('Y-m-d',strtotime($this->conn['settings']['date'])),
        ]);
    }
    public function neighbourhood() {
        return $this->execByPosition('neighbourhood');
    }
    public function countrySubdivision() {
        return $this->execByPosition('countrySubdivision');
    }
    public function findNearby() {
        return $this->execByPosition('findNearby',[
            'featureClass'=>$this->conn['settings']['featureClass'],
            'featureCode'=>$this->conn['settings']['featureCode'],
        ]);
    }
    public function extendedFindNearby() {
        return $this->execByPosition('extendedFindNearby');
    }

      /*************************/
     /* Wikipedia Webservices */
    /*************************/
    public function wikipediaBoundingBox() {
        return $this->execByGeoBox('wikipediaBoundingBox',[
            'maxRows'=>$this->conn['settings']['maxRows']
        ]);
    }
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
    public function wikipediaSearch() {
        $query=[
            'maxRows'=>$this->conn['settings']['maxRows']
        ];
        if(isSet($this->conn['settings']['wikiSearch']) && is_array($this->conn['settings']['wikiSearch'])) {
            $search=$this->conn['settings']['wikiSearch'];
            if(isSet($search['title']) && $search['title']) {
                $query['title']=$search['title'];
            } elseif (isSet($search['query']) && $search['query']) {
                $query['q']=rawurlencode(utf8_encode($search['query']));
            }
        }
        return $this->exe->get([
            'cmd'=>'wikipediaSearch',
            'query'=>$query
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
        $box=[
            'north'=>$box['N'],
            'south'=>$box['S'],
            'east'=>$box['E'],
            'west'=>$box['W'],
        ];
        $query=array_merge($box,$ar);
        return $this->exe->get([
            'cmd'=>$cmd,
            'query'=>$query
        ]);
    }

}
