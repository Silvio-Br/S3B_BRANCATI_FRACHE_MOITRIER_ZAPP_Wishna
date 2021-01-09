<?php
session_start();
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use wishlist\database\Eloquent;
use wishlist\controller\ParticipantController;

require_once __DIR__ . '/vendor/autoload.php';
$c = new Container(['settings'=>['displayErrorDetails'=>true]]);
$app = new App($c);

Eloquent::start(__DIR__ . '/src/conf/conf.ini');

$app->get('/', function(Request $rq, Response $rs, array $args): Response {
    $c = new ParticipantController($this);
    return $c->displayHome($rq,$rs,$args);
})->setName('home');

$app->run();