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
        $this->conn['settings']=array_merge($this->conn['settings'],$arr);
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
        $box=$this->conn['settings']['geoBox'];
        return $this->exe->get([
            'cmd'=>'cities',
            'query'=>[
              'north'=>$box['N'],
              'south'=>$box['S'],
              'east'=>$box['E'],
              'west'=>$box['W'],
              'maxRows'=>$this->conn['settings']['maxRows']
          ]
        ]);
    }

}
