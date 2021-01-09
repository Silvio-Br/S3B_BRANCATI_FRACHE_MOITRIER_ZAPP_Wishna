<?php
session_start();
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use wishcreate\controller\CreateurController;
use wishcreate\database\Eloquent;

require_once __DIR__ . '/vendor/autoload.php';
$c = new Container(['settings'=>['displayErrorDetails'=>true]]);
$app = new App($c);

Eloquent::start(__DIR__ . '/src/conf/conf.ini');

$app->get('/', function(Request $rq, Response $rs, array $args): Response {
    $c = new CreateurController($this);
    return $c->displayHome($rq,$rs,$args);
})->setName('home');

$app->post('/', function (Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    $c->postAccederListe($rq,$rs,$args);
});

$app->get('/meslistes/{token_admin}[/]', function(Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    return $c->displayListe($rq,$rs,$args);
})->setName('detailListe');

$app->get('/meslistes/{token_admin}/modifier/item/{id_item}[/]', function(Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    return $c->displayModifierItem($rq,$rs,$args);
})->setName('modifierItem');

$app->post('/meslistes/{token_admin}/modifier/item/{id_item}/', function(Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    $c->postModifierItem($rq,$rs,$args);
});

$app->get('/meslistes/{token_admin}/modifier[/]', function(Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    return $c->displayModifierListe($rq,$rs,$args);
})->setName('modifierListe');

$app->post('/meslistes/{token_admin}/modifier[/]', function(Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    $c->postModifierListe($rq,$rs,$args);
});

$app->get('/create[/]', function(Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    return $c->displayFormulaire($rq, $rs, $args);
})->setName('create');

$app->post("/create[/]", function (Request $rq, Response $rs, array $args) {
    $c = new CreateurController($this);
    $c->postCreate($rq,$rs,$args);
});

$app->run();