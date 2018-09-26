<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'NodeLink/GitDeploy',
    ['path' => '/git-deploy'],
    function (RouteBuilder $routes) {
        $routes->post('/', ['controller' => 'App', 'action' => 'deploy']);
    }
);
