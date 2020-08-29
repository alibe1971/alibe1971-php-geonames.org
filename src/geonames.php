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



    /* Geonames Original functions */
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


    public function children($id) {
        return $this->exe->get([
            'cmd'=>'children',
            'query'=>[
                'geonameId'=>$id
            ]
        ]);
    }

}
