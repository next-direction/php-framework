<?php

namespace NextDirection\Application\Controller;

use NextDirection\Framework\Http\Response;

/**
 * Class Index
 *
 * @Roles=user
 *
 * @package NextDirection\Application\Controller
 */
class Index {
    
    /**
     * @Route=/
     *
     * @Roles=admin
     *
     * @param Response $response
     *
     * @return Response
     */
    public function index(Response $response): Response {
        /*$response->setJson([
            'index' => true
        ]);*/
        
        echo 'test';
        
        return $response;
    }
}