<?php

return [
    'cacheDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . hash('sha256', getcwd()),
    'ttl'      => 3600
];