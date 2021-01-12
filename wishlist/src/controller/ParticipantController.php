<?php

namespace wishlist\controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Date;
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

    /**
     * methode permettant l'affichage de la page home
     * @param Request $rq
     * @param Response $rs
     * @param array $args
     * @return Response
     */
    public function displayHome(Request $rq, Response $rs, array $args): Response
    {
        $v = new ParticipantVue(null);

        $htmlvars = [
            'basepath' => $rq->getUri()->getBasePath()
        ];

        $rs->getBody()->write($v->render($htmlvars, ParticipantVue::HOME));
        return $rs;
    }

    /**
     * suite à la validation du formulaire de la page home on redirige vers la page de la liste si existe
     * @param Request $rq
     * @param Response $rs
     * @param array $args
     */
    public function postAccederListe(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $token = filter_var($data['token'], FILTER_SANITIZE_STRING);
        header("Location: {$this->c->router->pathFor('detailListe', ['token_liste'=>$token])}");
        exit();
    }

    /**
     * methode affichante le contenu d'une liste
     * @param Request $rq
     * @param Response $rs
     * @param array $args
     * @return Response
     */
    public function displayContentList(Request $rq, Response $rs, array $args): Response {

        try {

            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'etreCreateur' => false
            ];

            $liste = Liste::liste($args['token_liste'])->firstOrFail();
            $items = $liste->items()->get();

            $tabItems = array();
            foreach ($items as $item) {
                $url = $this->c->router->pathFor('detailItem', ['id_item'=>$item->id,'token_liste'=>$args['token_liste']]);
                array_push($tabItems, [$item, $url]);
            }
            $htmlvars['objets'] = $tabItems;

            $expiration = $liste->expiration;
            $origin = new \DateTime('now');
            $target = new \DateTime("{$expiration}");
            $interval = $origin->diff($target);

            if (intval($interval->format('%R%a')) < 0) {
                $v = new ParticipantVue([$liste]);
                $rs->getBody()->write($v->render($htmlvars, ParticipantVue::LISTE_CONTENT_EXPIRE));
            } else {
                if (isset($_COOKIE['createur'])) {
                    $arrayTokenCreateur = explode("-", $_COOKIE['createur']);
                    if (in_array($args['token_liste'], $arrayTokenCreateur)) {
                        $htmlvars['etreCreateur'] = true;
                    }
                }

                $v = new ParticipantVue([$liste]);
                $rs->getBody()->write($v->render($htmlvars, ParticipantVue::LISTE_CONTENT_NON_EXPIRE));

            }
            return $rs;
        } catch (ModelNotFoundException $e) {
            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'url' => $this->c->router->pathFor('home'),
                'message' => "Liste inexistante, retournez à l'accueil pour réessayer",
            ];
            $v = new ParticipantVue(null);
            $rs->getBody()->write($v->render($htmlvars, ParticipantVue::MESSAGE));
            return $rs;
        }
    }

    /**
     * methode affichant les détails d'un item
     * @param Request $rq
     * @param Response $rs
     * @param array $args
     * @return Response
     */
    public function displayItem(Request $rq, Response $rs, array $args):Response {
        
        try {
            $liste = Liste::liste($args['token_liste'])->firstOrFail();
            $item = Item::query()->where([
                ['id', '=', $args['id_item']],
                ['liste_id', '=', $liste->no]
            ])->firstOrFail();

            $etreCreateur = false;
            if (isset($_COOKIE['createur'])) {
                $arrayTokenCreateur = explode("-", $_COOKIE['createur']);
                if (in_array($args['token_liste'], $arrayTokenCreateur)) {
                    $etreCreateur = true;
                }
            }

            $expiration = $liste->expiration;
            $origin = new \DateTime('now');
            $target = new \DateTime("{$expiration}");
            $interval = $origin->diff($target);

            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'etreCreateur' => $etreCreateur,
                'expire' => intval($interval->format('%R%a')) < 0
            ];

            $v = new ParticipantVue([$item]);

            $rs->getBody()->write($v->render($htmlvars, ParticipantVue::ITEM_SEUL));
            return $rs;
        } catch (ModelNotFoundException $e) {
            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'url' => $this->c->router->pathFor('detailListe', ['token_liste'=>$args['token_liste']]),
                'message' => "Cet item n'est pas présent dans cette liste"
            ];
            $v = new ParticipantVue(null);
            $rs->getBody()->write($v->render($htmlvars, ParticipantVue::MESSAGE));
            return $rs;
        }
    }

    /**
     * methode post enregistrant la reservation de l'item suite au remplissage du formulaire
     * @param Request $rq
     * @param Response $rs
     * @param array $args
     * @return Response
     */
    public function postReserverItem(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
        $message = filter_var($data['message'], FILTER_SANITIZE_STRING);

        $url = $this->c->router->pathFor('detailListe', ['token_liste'=>$args['token_liste']]);
        $item = Item::item($args['id_item'])->update(array('reservation'=>1, 'nom_reservation'=>$data['nom'], 'message_reservation'=>$data['message']));

        $_SESSION['nom']=$data['nom'];

        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath(),
            'message' => "Votre réservation a été enregistrée avec succès !",
            'url' => $url
        ];

        $v = new ParticipantVue(null);
        $rs->getBody()->write($v->render($htmlvars, ParticipantVue::MESSAGE));
        return $rs;
    }
}