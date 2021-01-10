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

// page home permettant d'enter le token d'une liste
$app->get('/', function(Request $rq, Response $rs, array $args): Response {
    $c = new ParticipantController($this);
    return $c->displayHome($rq,$rs,$args);
})->setName('home');

// methode post permettant d'acceder à la liste à partir du formulaire
$app->post('/', function (Request $rq, Response $rs, array $args) {
    $c = new ParticipantController($this);
    $c->postAccederListe($rq,$rs,$args);
});

// page affichant le contenu d'une liste avec ses informations
$app->get('/liste/{token_liste}[/]', function(Request $rq, Response $rs, array $args): Response {
    $c = new ParticipantController($this);
    return $c->displayContentList($rq,$rs,$args);
})->setName('detailListe');

// page affichant les détails d'un item
$app->get('/liste/{token_liste}/item/{id_item}[/]', function(Request $rq, Response $rs, array $args): Response {
    $c = new ParticipantController($this);
    return $c->displayItem($rq,$rs,$args);
})->setName('detailItem');

// post permettant de réserver un item suite au remplissage du formulaire
$app->post("/liste/{token_liste}/item/{id_item}[/]", function (Request $rq, Response $rs, array $args) {
    $c = new ParticipantController($this);
    $c->postReserverItem($rq,$rs,$args);
});

$app->run();