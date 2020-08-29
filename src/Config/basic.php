<?php

return [

    /*
    |------------------------------------------------------
    | Config basic parameters
    |------------------------------------------------------
    |
    | Params for the connection to GeoNames
    |
    */

    'baseHost'=>'http://api.geonames.org/',
    'settings'=> [
        'clID'=>'GeoNames.OrgID',
        'lang'=>'en',
        'format'=>'array',
        'position'=>[
            'lat'=>0,           // Latitude
            'lng'=>0,           // Longitude
            'radius'=>0,        // Radius in Km
        ],
        'geoBox'=>[
            'N'=>0,             // North
            'S'=>0,             // South
            'E'=>0,             // East
            'W'=>0,             // West
        ],
        'maxRows'=>200,         // Max number of rows
        'date'=>'today',        // date parameter
        'minMagnitude'=>0       // Min mag. for earthquakes
    ]

];
