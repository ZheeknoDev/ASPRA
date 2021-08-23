<?php

return [

    /**
     * @category File
     * @package  App/Config
     * @author   Marry Go Round <million8.me@gmail.com>
     * @license  https://opensource.org/licenses/MIT - MIT License 
     * @link     https://github.com/ZheeknoDev/aspra
     */

    /* ==============================================
     * Do not edit below this line, 
     * unless you fully understand the implications.
     * ============================================== 
     */
    'APP_KEY' => "hoNS45tulSq6Kqt{WEnojP878sOxaZYH",

    /**
     * ==============================================
     * Application configuration
     * ==============================================
     */
    'APP_NAME' => 'ASPRA',
    'APP_VERSION' => '1.0.1',

    /**
     * If the application is the production service, 
     * you can disable debugging mode by change the 
     * value from true become to false
     */
    'APP_DEBUG' => true,

    /**
     * Application's timezone
     * You can change the timezone 
     * follow your location of the server
     */
    'APP_TIMEZONE' => 'Asia/Bangkok',

    /**
     * ===============================================
     * Database configuration
     * ===============================================
     * !! Important - Available with MySQL & MariaDB only !!
     */

    // For MySQL or MariaDB have the database's drive as 'mysql' 
    'DATABASE' => [
        'CONNECTION' => 'mysql',
        'HOST' => '127.0.0.1',
        'PORT' => '3306',
        'USERNAME' => 'root',
        'PASSWORD' => 'p@ssw0rdSQL',
        'DATABASE' => 'aspra_v1_2019',
        'CHARSET' => 'utf8'
    ],

];
