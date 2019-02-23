<?php

namespace NextDirection\Framework\Config;

use NextDirection\Framework\Common\EnumBase;

abstract class Types extends EnumBase {
    
    /**
     * Application configuration
     *
     * @var string
     */
    public const APP = __DIR__ . '/../../config/app.php';
    
    /**
     * DI Mapping
     *
     * @var string
     */
    public const DI = __DIR__ . '/../../config/di.php';
    
    /**
     * File cache config
     *
     * @var string
     */
    public const FILE_CACHE = __DIR__ . '/../../config/fileCache.php';
}