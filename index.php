<?php

include_once './php-router/Request.php';
include_once './php-router/Router.php';
include_once './classes/Roster.php';

require_once('include/utils.php');

$router = new Router(new Request);


$router->get('/search-roster', function($request){
  $roster = new Roster();
  return $roster->index($request);
});

