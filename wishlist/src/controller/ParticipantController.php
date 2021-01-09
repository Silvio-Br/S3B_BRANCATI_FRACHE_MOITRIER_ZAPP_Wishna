<?php

namespace wishlist\controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Slim\Container;
use Slim\Http\Response;
use Slim\Http\Request;
use wishlist\models\Item;
use wishlist\models\Liste;
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

    public function postAccederListe(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $token = filter_var($data['token'], FILTER_SANITIZE_STRING);
        header("Location: {$this->c->router->pathFor('detailListe', ['token_liste'=>$token])}");
        exit();
    }

    public function displayContentList(Request $rq, Response $rs, array $args): Response {

        try {
            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath()
            ];

            $liste = Liste::query()->where("token", '=', $args['token_liste'])
                ->firstOrFail();
            $items = $liste->items()->get();

            $tabItems = array();
            foreach ($items as $item) {
                $url = $this->c->router->pathFor('detailItem', ['id_item'=>$item->id,'token_liste'=>$args['token_liste']]);
                array_push($tabItems, [$item, $url]);
            }
            $htmlvars['objets'] = $tabItems;

            $v = new ParticipantVue([$liste]);
            $rs->getBody()->write($v->render($htmlvars, ParticipantVue::LISTE_CONTENT));
            return $rs;
        } catch (ModelNotFoundException $e) {
            $rs->getBody()->write("Liste inexistante");
            return $rs;
        }
    }

    public function displayItem(Request $rq, Response $rs, array $args):Response {

        try {
            $liste = Liste::query()->where('token', '=', $args['token_liste'])
                ->firstOrFail();
            $item = Item::query()->where([
                ['id', '=', $args['id_item']],
                ['liste_id', '=', $liste->no]
            ])->firstOrFail();

            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath()
            ];

            $v = new ParticipantVue([$item]);

            $rs->getBody()->write($v->render($htmlvars, ParticipantVue::ITEM_SEUL));
            return $rs;
        } catch (ModelNotFoundException $e) {
            $rs->getBody()->write("L'item n'est pas présent dans cette liste");
            return $rs;
        }
    }

    public function postReserverItem(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
        $message = filter_var($data['message'], FILTER_SANITIZE_STRING);

        $url = $this->c->router->pathFor('detailListe', ['token_liste'=>$args['token_liste']]);
        $item = Item::query()->where('id', '=', $args['id_item'])->update(array('reservation'=>1, 'nom_reservation'=>$data['nom'], 'message_reservation'=>$data['message']));

        $_SESSION['nom']=$data['nom'];

        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath(),
            'message' => "Votre réservation a été enregistrée avec succès !",
            'url' => $url
        ];

        $v = new ParticipantVue(null);
        $rs->getBody()->write($v->render($htmlvars, ParticipantVue::ALERT_BOX));
        return $rs;
    }
}