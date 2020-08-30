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
            'lat'=>false,       // Latitude
            'lng'=>false,       // Longitude
            'radius'=>false,    // Radius in Km
        ],
        'featureClass'=>false,   // Class for filters
        'featureCode'=>false,    // Code for filters
        'geoBox'=>[
            'N'=>false,          // North
            'S'=>false,          // South
            'E'=>false,          // East
            'W'=>false,          // West
        ],
        'maxRows'=>false,       // Max number of rows
        'date'=>'today',        // date parameter
        'minMagnitude'=>0       // Min mag. for earthquakes

        'wikiSearch'=>[         // Wiki Search
            'title'=>false,     // Title has priority
            'query'=>false,     // General search
        ]
    ]

];
