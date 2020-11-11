<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Drive For Cache
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "drivers" configuration array.
    |
    */
    'drive' => typerocket_env('TYPEROCKET_STATIC_DRIVE', 'root'),

    /*
    |--------------------------------------------------------------------------
    | Models To Cache
    |--------------------------------------------------------------------------
    */
    'models' => [
        \App\Models\Post::class,
        \App\Models\Page::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Generator Keys To Ignore
    |--------------------------------------------------------------------------
    |
    | A generator key uses the class name followed by the record ID with a :
    | between the to strings. For example: \App\Models\Post:1
    |
    */
    'ignore' => [
    ],

];