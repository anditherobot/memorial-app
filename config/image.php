<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images.
    | You may choose one of them according to your PHP configuration.
    |
    | Imagick is recommended for production environments, especially when
    | handling HEIC/HEIF images. Set IMAGE_DRIVER=imagick in your .env file.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => env('IMAGE_DRIVER', 'gd'),

];