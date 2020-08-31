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
        'minMagnitude'=>false,  // Min mag. for earthquakes
        'localCountry'=>false,  // Bool for local country
        'cities'=>false,        // cities
        'style'=>false,         // The verbosity
        'isReduced'=>false,     // Is reduced var
        'charset'=>false,       // The charset
        'filter'=>false,        // Filter for the searches
        'includeGeoName'=>false,    // For OpenStreetMap

        'wikiSearch'=>[         // Wiki Search
            'title'=>false,     // Title has priority
            'query'=>false,     // General search
        ],

        'address'=>[            // Address Search
            'query'=>false,     // General search
            'country'=>false,   // The country code
            'postalCode'=>false,
            'adminCode1'=>false,
            'adminCode2'=>false,
            'adminCode3'=>false,
            'isUniqueStreetName'=>false
        ]
    ]

];
