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
        'lang'=>'en',
        'format'=>'object',
        'position'=>[
            'lat'=>false,       // Latitude
            'lng'=>false,       // Longitude
            'radius'=>false,    // Radius in Km
        ],
        'featureClass'=>false,   // Class for filters
        'featureCode'=>false,    // Code for filters
        'EXCLUDEfeatureCode'=>false,
        'localCountry'=>false,  // Bool for local country
        'geoBox'=>[              // the box
            'north'=>false,
            'south'=>false,
            'east'=>false,
            'west'=>false,
        ],
        'maxRows'=>false,    // Max number of rows
        'date'=>false,       // date parameter
        'level' => false,    // Administr. level
        'minMagnitude'=>false,  // Min mag. for earthquakes
        'cities'=>false,        // cities
        'style'=>false,         // The verbosity
        'isReduced'=>false,     // Is reduced var
        'charset'=>false,       // The charset
        'filter'=>false,        // Filter for the searches
        'includeGeoName'=>false,    // For OpenStreetMap

        'wikiSearch'=>[         // Wiki Search
            'title'=>false,     // Title has priority
            'q'=>false,         // General search
        ],

        'search'=>[
            'q' => false,
            'name' => false,
            'name_equals' => false,
            'name_startsWith' => false,
            'maxRows' => false,
            'startRow' => false,
            'country' => false,
            'countryBias' => false,
            'continentCode' => false,
            'adminCode1' => false,
            'adminCode2' => false,
            'adminCode3' => false,
            'adminCode4' => false,
            'adminCode5' => false,
            'featureClass' => false,
            'featureCode' => false,
            'cities' => false,
            'lang' => false,
            'type' => false,
            'style' => false,
            'isNameRequired' => false,
            'tag' => false,
            'operator' => false,
            'charset' => false,
            'fuzzy' => false,
            'east' => false,
            'west' => false,
            'north' => false,
            'south' => false,
            'searchlang' => false,
            'orderby' => false,
        ],
        'rssToGeo'=>[
            'feedLanguage' => false,
            'type' => false,
            'geoRSS' => false,
            'addUngeocodedItems' => false,
            'country' => false,
        ],
        'postalplace' => [
            'postalcode' => false,
            'postalcode_startsWith' => false,
            'placename' => false,
            'placename_startsWith' => false,

            'country' => false,
            'countryBias' => false,
            'style' => false,
            'operator' => false,
            'charset' => false,
            'isReduced' => false,
        ]
    ]

];
