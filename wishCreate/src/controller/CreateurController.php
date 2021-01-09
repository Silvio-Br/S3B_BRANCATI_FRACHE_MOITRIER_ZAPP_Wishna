<?php

namespace wishcreate\controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use wishcreate\models\Liste;
use wishcreate\vue\CreateurVue;
class CreateurController
{

    private $c = null;

    /**
     * CreateurController constructor.
     * @param null $c
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    public function displayHome(Request $rq, Response $rs, array $args): Response
    {
        $v = new CreateurVue(null);

        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath()
        ];

        $rs->getBody()->write($v->render($htmlvars, CreateurVue::HOME));
        return $rs;
    }

    public function postAccederListe(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $token = filter_var($data['token'], FILTER_SANITIZE_STRING);
        header("Location: {$this->c->router->pathFor('detailListe', ['token_admin'=>$token])}");
        exit();
    }

    public function displayListe(Request $rq, Response $rs, array $args): Response {
        try {
            $liste = Liste::query()->where('tokenAdmin', '=', $args['token_admin'])->firstOrFail();
            $items = $liste->items()->get();

            $v = new CreateurVue([$liste]);
            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'share' => "http://$_SERVER[HTTP_HOST]/Wishna/wishlist/liste/{$liste->token}"
            ];

            $tabItems = array();
            foreach ($items as $item) {
                if ($item->reservation == 0) {
                    $urlModifierListe = $this->c->router->pathFor('modifierItem', ['id_item'=>$item->id,'token_admin'=>$args['token_admin']]);
                    array_push($tabItems, [$item, $urlModifierListe]);
                } else {
                    array_push($tabItems, [$item, null]);
                }

            }
            $htmlvars['objets']=$tabItems;

            $htmlModifier = null;
            if (!($liste->expiration >= new \DateTime("now") )) {
                $urlModifierListe = $this->c->router->pathFor('modifierListe', ['token_admin'=>$liste->tokenAdmin]);
                $urlAjouterItem = $this->c->router->pathFor('ajouterItem', ['token_admin'=>$liste->tokenAdmin]);
                $htmlModifier = <<<END
<button onclick="location.href='$urlModifierListe'">Modifier</button>
END;
                $htmlAjouter = <<<END
<button onclick="location.href='$urlAjouterItem'">Ajouter un item</button>
END;
            } else {
                $htmlModifier = <<<END
<p>Vous ne pouvez plus modifier cette liste</p>
END;
                $htmlAjouter = <<<END
<p>Vous ne pouvez plus ajouter d'items à cette liste</p>
END;

            }
            $htmlvars['modifier']=$htmlModifier;
            $htmlvars['ajouter']=$htmlAjouter;

            $rs->getBody()->write($v->render($htmlvars, CreateurVue::LISTE_AVEC_ITEMS));
            return $rs;
        } catch (ModelNotFoundException $e) {
            echo "Liste inexistante";
            return $rs;
        }

    }
}