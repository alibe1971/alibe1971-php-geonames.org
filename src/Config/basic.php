<?php

return [

    /* test
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
        'startRow'=>false,    // Rows number to jump
        'date'=>false,       // date parameter
        'level' => false,    // Administr. level
        'minMagnitude'=>false,  // Min mag. for earthquakes
        'cities'=>false,        // cities
        'style'=>false,         // The verbosity
        'isReduced'=>false,     // Is reduced var
        'charset'=>false,       // The charset
        'filter'=>false,        // Filter for the searches
        'includeGeoName'=>false,    // For OpenStreetMap
        'search'=>[
            'type' => false, // removed because the type json is sufficient
            'isNameRequired' => false,
            'tag' => false,
            'fuzzy' => false,
            'searchlang' => false,
            'orderby' => false,
            'inclBbox' => false,
        ],
        'rssToGeo'=>[
            'feedLanguage' => false,
            'type' => false,
            'geoRSS' => false,
            'addUngeocodedItems' => false
        ],
    ]

];
