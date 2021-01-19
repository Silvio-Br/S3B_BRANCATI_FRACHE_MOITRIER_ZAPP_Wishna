<?php

namespace wishcreate\controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use wishcreate\models\Item;
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

    public function postHome(Request $rq, Response $rs, array $args)
    {
        if ($_POST['bouton'] == "OK") {
            $data = $rq->getParsedBody();
            $token = filter_var($data['token'], FILTER_SANITIZE_STRING);
            header("Location: {$this->c->router->pathFor('detailListe', ['token_admin'=>$token])}");
            exit();
        }
        else if ($_POST['bouton'] == "Créer une nouvelle liste"){
            header("Location: {$this->c->router->pathFor('create')}");
            exit();
        }
    }

    public function displayListe(Request $rq, Response $rs, array $args): Response {
        try {
            $liste = Liste::query()->where('tokenAdmin', '=', $args['token_admin'])->firstOrFail();
            $items = $liste->items()->get();

            $v = new CreateurVue([$liste]);
            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'share' => $this->c->router->pathFor('partagerListe', ['token_admin'=>$liste->tokenAdmin])
            ];

            $expiration = $liste->expiration;
            $origin = new \DateTime('now');
            $target = new \DateTime("{$expiration}");
            $interval = $origin->diff($target);

            if (!(intval($interval->format('%R%a')) < 0)) {
                $tabItems = array();
                foreach ($items as $item) {
                    if ($item->reservation == 0) {
                        $urlModifierItem = $this->c->router->pathFor('modifierItem', ['id_item'=>$item->id,'token_admin'=>$args['token_admin']]);
                        array_push($tabItems, [$item, $urlModifierItem]);
                    } else {
                        array_push($tabItems, [$item, null]);
                    }

                }
                $htmlvars['objets']=$tabItems;

                $urlModifierItem = $this->c->router->pathFor('modifierListe', ['token_admin'=>$liste->tokenAdmin]);
                $urlAjouterItem = $this->c->router->pathFor('ajouterItem', ['token_admin'=>$liste->tokenAdmin]);
                $htmlvars['modifier']=$urlModifierItem;
                $htmlvars['ajouter']=$urlAjouterItem;
                $rs->getBody()->write($v->render($htmlvars, CreateurVue::LISTE_NON_EXPIREE));
            } else {
                $tabItems = array();
                foreach ($items as $item) {
                    if ($item->reservation == 1) {
                        array_push($tabItems, $item);
                    }
                }
                $htmlvars['objets']=$tabItems;

                $rs->getBody()->write($v->render($htmlvars, CreateurVue::LISTE_EXPIREE));
            }


            return $rs;
        } catch (ModelNotFoundException $e) {
            $v = new CreateurVue(null);
            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'message' => "Cette liste est inexistante",
                'url' => $this->c->router->pathFor('home', [])
            ];
            $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
            return $rs;
        }

    }

    public function displayModifierItem(Request $rq, Response $rs, array $args): Response
    {
        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath()
        ];

        $item = Item::query()->where('id', '=', $args['id_item'])->firstOrFail();
        if ($item->reservation == 0) {
            $v = new CreateurVue([$item]);
            $rs->getBody()->write($v->render($htmlvars, CreateurVue::MODIFIER_ITEM));
        } else {
            $v = new CreateurVue(null);
            $htmlvars['message'] = "Vous ne pouvez plus modifier cet item";
            $htmlvars['url'] = $this->c->router->pathFor('detailListe', ['token_admin'=>$args['token_admin']]);

            $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
        }

        return $rs;
    }

    public function postModifierItem(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $item = Item::query()->where('id', '=', $args['id_item'])->firstOrFail();
        $urlRedirection = $this->c->router->pathFor('detailListe', ['token_admin'=>$args["token_admin"]]);

        if ($_POST['bouton'] == "OK") {
            $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
            $description = filter_var($data['desc'], FILTER_SANITIZE_STRING);
            $prix = filter_var($data['prix'], FILTER_SANITIZE_NUMBER_FLOAT);
            $url = filter_var($data['url'], FILTER_SANITIZE_URL);
            $imgInt = filter_var($data['img-int'], FILTER_SANITIZE_URL);

            $img = null;

            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath()
            ];

            switch($_POST['choix']){
                case "lien-interne":
                    $racineImg = substr($imgInt, 0,8);
                    if ($racineImg == "web/img/") {
                        $img = substr($imgInt, 8);
                    }
                    if (strlen($img) == 0) {
                        $img = "noImage.png";
                    }
                    break;
                case "lien-externe":

                    if(!empty($_FILES)){
                        $file_name = $_FILES['img-ext']['name'];
                        $file_extension = strrchr($file_name,".");

                        $file_tmp_name = $_FILES['img-ext']['tmp_name'];
                        $file_dest = 'web/img/'.$file_name;

                        $extension_autorise= array('.jpg', '.png', '.JPG', '.PNG');

                        if(in_array($file_extension, $extension_autorise)){
                            if(move_uploaded_file($file_tmp_name, $file_dest)){
                                $img=$file_name;
                            } else {
                                $v = new CreateurVue(null);
                                $htmlvars['message'] = "Une erreur est survenue";
                                $htmlvars['url']= $this->c->router->pathFor('detailListe', ['token_admin'=>$args['token_admin']]);;
                                $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
                                return $rs;
                            }
                        } else {
                            $v = new CreateurVue(null);
                            $htmlvars['message'] = "Seuls les images en PNG ou JPG sont acceptées";
                            $htmlvars['url']= $this->c->router->pathFor('detailListe', ['token_admin'=>$args['token_admin']]);;
                            $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
                            return $rs;
                        }
                        copy($file_dest, "./../wishlist/web/img/{$file_name}");
                    }
                    break;
            }


            $item->nom = $nom;
            $item->descr = $description;
            $item->url = $url;
            $item->tarif = $prix;
            $item->img = $img;
            $item->save();

            $htmlvars ['message'] = "Item modifié avec succès !";
            $htmlvars['url'] = $urlRedirection;

        } elseif ($_POST['bouton'] == "Supprimer cet item") {
            $item->delete();

            $htmlvars = [
                'basepath'=> $rq->getUri()->getBasePath(),
                'message' => "Item supprimé avec succès !",
                'url' => $urlRedirection
            ];
        }
        $v = new CreateurVue(null);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
        return $rs;
    }

    public function displayAjouterItem(Request $rq, Response $rs, array $args): Response
    {
        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath()
        ];

        $liste = Liste::query()->where('tokenAdmin','=',$args['token_admin'])->firstOrFail();

        $v = new CreateurVue($liste->no);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::AJOUTER_ITEM));
        return $rs;
    }

    public function displayModifierListe(Request $rq, Response $rs, array $args): Response
    {
        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath()
        ];

        $liste = Liste::query()->where('tokenAdmin','=',$args['token_admin'])->firstOrFail();

        $v = new CreateurVue([$liste]);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MODIFIER_LISTE));
        return $rs;
    }

    public function postCreate(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $titre = filter_var($data['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($data['desc'], FILTER_SANITIZE_STRING);

        $publique = '0';
        if (isset($data['public'])) {
            $publique = '1';
        }

        $token = bin2hex(random_bytes(8));
        $tokenAdmin = bin2hex(random_bytes(8));

        $liste = new Liste();
        $liste->titre = $titre;
        $liste->description = $description;
        $liste->expiration = $data['date'];
        $liste->token = $token;
        $liste->tokenAdmin = $tokenAdmin;
        $liste->etrePublique = $publique;

        $liste->save();

        if (!isset($_COOKIE['createur'])) {
            setcookie("createur", $token, strtotime($data['date']), "/S3B_BRANCATI_FRACHE_MOITRIER_ZAPP_Wishna/");
        } else {
            setcookie("createur", $_COOKIE['createur']."-{$token}", strtotime($data['date']), "/S3B_BRANCATI_FRACHE_MOITRIER_ZAPP_Wishna/");
        }

        $url = $this->c->router->pathFor('detailListe', ['token_admin'=>$tokenAdmin]);
        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath(),
            'message' => "Utilisez ce token pour modifier votre liste : {$tokenAdmin}",
            'url' => $url
        ];

        $v = new CreateurVue(null);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
        return $rs;
    }

    public function postModifierListe(Request $rq, Response $rs, array $args)
    {
        $data = $rq->getParsedBody();
        $titre = filter_var($data['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($data['desc'], FILTER_SANITIZE_STRING);

        $tokenAdmin = $args['token_admin'];

        $liste = Liste::query()->where('tokenAdmin','=',$tokenAdmin)->firstOrFail();
        $liste->titre = $titre;
        $liste->description = $description;
        $liste->expiration = $data['date'];

        $liste->save();

        $url = $this->c->router->pathFor('detailListe', ['token_admin'=>$liste->tokenAdmin]);
        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath(),
            'message' => "Liste modifiée avec succès !",
            'url' => $url
        ];

        $v = new CreateurVue(null);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
        return $rs;
    }

    public function postAjouterItem(Request $rq, Response $rs, array $args)
    {
        $v = new CreateurVue(null);

        $data = $rq->getParsedBody();
        $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
        $description = filter_var($data['desc'], FILTER_SANITIZE_STRING);
        $prix = filter_var($data['prix'], FILTER_SANITIZE_NUMBER_FLOAT);
        $url = filter_var($data['url'], FILTER_SANITIZE_URL);
        $imgInt = filter_var($data['img-int'], FILTER_SANITIZE_URL);

        $img = null;

        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath()
        ];

        switch($_POST['choix']){
            case "lien-interne":
                $racineImg = substr($imgInt, 0,8);
                if ($racineImg == "web/img/") {
                    $img = substr($imgInt, 8);
                }
                if (strlen($img) == 0) {
                    $img = "noImage.png";
                }
                break;
            case "lien-externe":

                if(!empty($_FILES)){
                    $file_name = $_FILES['img-ext']['name'];
                    $file_extension = strrchr($file_name,".");

                    $file_tmp_name = $_FILES['img-ext']['tmp_name'];
                    $file_dest = 'web/img/'.$file_name;

                    $extension_autorise= array('.jpg', '.png', '.JPG', '.PNG');

                    if(in_array($file_extension, $extension_autorise)){
                        if(move_uploaded_file($file_tmp_name, $file_dest)){
                            $img=$file_name;
                        } else {
                            $htmlvars['message'] = "Une erreur est survenue";
                            $htmlvars['url']= $this->c->router->pathFor('detailListe', ['token_admin'=>$args['token_admin']]);;
                            $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
                            return $rs;
                        }
                    } else {
                        $htmlvars['message'] = "Seuls les images en PNG ou JPG sont acceptées";
                        $htmlvars['url']= $this->c->router->pathFor('detailListe', ['token_admin'=>$args['token_admin']]);;
                        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
                        return $rs;
                    }
                    copy($file_dest, "./../wishlist/web/img/{$file_name}");
                }
                break;
        }

        $tokenAdmin = $args['token_admin'];

        $liste = Liste::query()->where('tokenAdmin','=',$tokenAdmin)->firstOrFail();
        $item = new Item();
        $item->liste_id = $liste->no;
        $item->nom = $nom;
        $item->descr = $description;
        $item->url = $url;
        $item->tarif = $prix;
        $item->img = $img;

        $item->save();

        $url = $this->c->router->pathFor('detailListe', ['token_admin'=>$liste->tokenAdmin]);
        $htmlvars['message'] = "Item ajouté avec succès !";
        $htmlvars['url']= $url;

        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
        return $rs;
    }

    public function displayFormulaire(Request $rq, Response $rs, array $args): Response
    {
        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath()
        ];

        $v = new CreateurVue(null);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::CREATE));
        return $rs;
    }

    public function displayPartager(Request $rq, Response $rs, array $args): Response
    {
        $liste = Liste::query()->where('tokenAdmin', '=', $args['token_admin'])->firstOrFail();

        $urlDetailListe = $this->c->router->pathFor('detailListe', ['token_admin'=>$liste->tokenAdmin]);

        $htmlvars = [
            'basepath'=> $rq->getUri()->getBasePath(),
            'message' => "Voici le token de votre liste à partager : {$liste->token}",
            'url' => $urlDetailListe
        ];

        $v = new CreateurVue(null);
        $rs->getBody()->write($v->render($htmlvars, CreateurVue::MESSAGE));
        return $rs;
    }

}