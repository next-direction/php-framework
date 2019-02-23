<?php

namespace NextDirection\Framework;

use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Di\ObjectFactory;
use NextDirection\Framework\Http\Response;
use NextDirection\Framework\Http\ResponseCodes;
use NextDirection\Framework\Routing\Dispatcher;
use NextDirection\Framework\Routing\Matcher;
use NextDirection\Framework\Routing\Router;

class Application {
    
    /**
     * @var array
     */
    protected $config;
    
    public function __construct() {
        $reader = new Reader(Types::APP);
        $this->config = $reader->get();
    }
    
    public function run(): void {
        
        if ($this->config['prettyExceptions']) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }
        
        $router = new Router();

        /** @var Matcher $matcher */
        $matcher = ObjectFactory::createInstance(Matcher::class);
        $match = $matcher->match($router);
        
        if ($match) {
            
            /** @var Dispatcher $dispatcher */
            $dispatcher = ObjectFactory::createInstance(Dispatcher::class);
            $dispatcher->dispatch($matcher);
        } else {
            /** @var Response $response */
            $response = ObjectFactory::createInstance(Response::class);
            $response
                ->setCode(ResponseCodes::HTTP_NOT_FOUND)
                ->send();
        }
    }
}