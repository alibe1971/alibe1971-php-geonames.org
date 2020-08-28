<?php

namespace Alibe\PhpGeonamesorg;

use Alibe\PhpGeonamesorg\Lib\Exec;

class geonames {

    protected $conn;
    protected $exe;

    public function __construct() {
        $this->conn=include('Config/basic.php');
    }

    public function connect($arr=[]) {
        $this->conn['connection']=array_merge($this->conn['connection'],$arr);
        $this->exe=new Exec($this->conn);
        return $this;
    }


    /*Continents*/
    public function continensGetList($final=true) {
        return $this->childrenGet('6295630', 200, $final=true);
    }

    /*Countries*/
    public function countriesGetList($final=true) {
        return $this->exe->get([
            'cmd'=>'countryInfo',
            'final'=>$final
        ]);
    }

    public function countryGet($cc,$final=true) {
        return $this->exe->get([
            'cmd'=>'countryInfo',
            'query'=>[
                'country'=>$cc
            ],
            'final'=>$final
        ]);
    }


    public function childrenGet($id,$maxRows=200,$final=true) {
        return $this->exe->get([
            'cmd'=>'children',
            'query'=>[
                'geonameId'=>$id
            ],
            'final'=>$final
        ]);
    }

}
