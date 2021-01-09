<?php

namespace wishlist\controller;
use Slim\Container;
use Slim\Http\Response;
use Slim\Http\Request;
use wishlist\vue\ParticipantVue;
class ParticipantController
{

    private $c = null;

    /**
     * ParticipantController constructor.
     * @param null $c
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    public function displayHome(Request $rq, Response $rs, array $args): Response
    {
        $v = new ParticipantVue(null);

        $htmlvars = [
            'basepath' => $rq->getUri()->getBasePath()
        ];

        $rs->getBody()->write($v->render($htmlvars, ParticipantVue::HOME));
        return $rs;
    }
}