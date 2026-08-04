<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Institution details
    |--------------------------------------------------------------------------
    |
    | Fallback values used until an admin sets these from the Institution
    | settings page (/admin/settings/institution), which is the primary
    | source of truth once set — see the `Setting` model.
    |
    */

    'name' => env('INSTITUTION_NAME', 'Diploma Result Portal'),

    'address' => env('INSTITUTION_ADDRESS', ''),

];
