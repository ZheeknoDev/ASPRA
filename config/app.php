<?php

return [

    /**
     * @author ZheeknoDev <million8.me@gmail.com>
     * @package config/app
     * @category config
     * @license https://opensource.org/licenses/MIT - MIT License 
     * @copyright 2019-2021 ZheeknoDev by Marry Go Round
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
