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

    /*
    |--------------------------------------------------------------------------
    | Statement of result branding
    |--------------------------------------------------------------------------
    |
    | The background artwork and registrar sign-off shown on a student's
    | Statement of Result. The bundled defaults below ship with the app so
    | the document looks right out of the box; an admin can replace any of
    | them from the Institution settings page without a deploy.
    |
    */

    'statement_background' => 'images/statement-of-result/default-background.jpg',

    'registrar_signature' => 'images/statement-of-result/default-signature.png',

    /*
    |--------------------------------------------------------------------------
    | Transcript branding
    |--------------------------------------------------------------------------
    |
    | The background artwork shown on a student's Transcript. Kept as its own
    | setting (rather than reusing the Statement of Result background) so an
    | admin can use different artwork for each document; the registrar
    | sign-off above is shared between both documents.
    |
    */

    'transcript_background' => 'images/transcript/default-background.jpg',

    'registrar_name' => env('REGISTRAR_NAME', 'Citp Durosinmi A.E. MNCS, MSP'),

    'registrar_title' => env('REGISTRAR_TITLE', 'Registrar'),

];
