<?php

/*
 * Defines which class to injected for given interface
 */
return [
    \Psr\SimpleCche\CacheInterface::class => \NextDirection\Framework\Cache\Adapter\File::class,
    \NextDirection\Framework\Security\ProviderInterface::class => \NextDirection\Framework\Security\Adapter\Jwt::class,
    \NextDirection\Framework\Security\EntityInterface::class => \NextDirection\Application\Model\User::class
];