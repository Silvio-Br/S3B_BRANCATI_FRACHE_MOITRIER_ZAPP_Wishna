<?php
namespace wishlist\vue;
use wishlist\models\Item;
use wishlist\models\Liste;

class ParticipantVue
{

    private $data;

    /**
     * Constante correspondant à l'affichage d'un lien vers un item
     * @var int
     */
    const HOME = 1;

    /**
     * Constante correspondant à l'affichage de la liste
     * @var int
     */
    const LISTE_CONTENT = 2;

    /**
     * Constante correspondante à l'affichage de la page d'un item
     * @var int
     */
    const ITEM_SEUL = 3;

    /**
     * ParticipantVue constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    private function pageHome(): string
    {
        $html = <<<END
        <form method="post">
                 <p>Token de la liste<span class="required">*</span> : <input type="text" name="token" required/></p>
                 <p><input class="bouton" type="submit" value="OK"></p>
            </form>
END;
        return $html;
    }

    private function uneListeHtml(Liste $liste, $vars): string {
        $html = <<<END
<section class="titreListe">
            <h3 class="nom">{$liste->titre}</h3>
            <p class="desc">{$liste->description}</p>
        </section>
END;
        if (sizeOf($vars['objets'])>0) {
            $html .= <<<END
                
        <section class="tableau">
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Image</th>
                    <th>Réservé</th>
                </tr>
END;
            for ($i = 0; $i < sizeOf($vars['objets']); $i++) {
                $html .= $this->unItem($vars['objets'][$i][0], $vars['basepath'], $vars['objets'][$i][1]);
            }
            $html .= <<<END
                
              </table>
          </section>
END;

        } else {
            $html .= "<p>Aucuns items dans cette liste</p>";
        }
        return $html;
    }

    private function unItemHtml(Item $item, $v): string {
        $reservation = " : Non";
        if ($item->reservation) {
            $reservation = " par $item->nom_reservation";
        }

        $img = null;
        if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
            $img = "{$v['basepath']}/web/img/$item->img";
        } else {
            $img = $item->img;
        }

        $html = <<<END
        <section class="content">
            <h3 class="nom">{$item->nom}</h3>
            <p class="desc">{$item->descr}</p>
            <img class="imageItem" alt="image" src="$img">
            <h4 class="prix">tarif : {$item->tarif}</h4>
            <h4 class="reservation">Réservé$reservation</h4>
        </section>
END;
        if (!$item->reservation) {
            $_GET['id']=$item->id;
            $html .= $this->insererFormulaire($v);
        }
        return $html;
    }

    private function unItem(Item $item, $basepath, $url): string {
        $reservation = "Non";
        $img = null;
        if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
            $img = "{$basepath}/web/img/$item->img";
        } else {
            $img = $item->img;
        }

        if ($item->reservation) {
            $reservation = "$item->nom_reservation";
        }
        $html = <<<END
            
                <tr>
                    <td><a href="$url">$item->nom</a></td>
                    <td><img class="imageItem" alt="image" src="$img"></td>
                    <td><p class="reservation">$reservation</p></td>
                </tr>
END;
        return $html;
    }

    private function insererFormulaire($v): string {
        $nom="";
        if (isset($_SESSION['nom'])) {
            $nom = $_SESSION['nom'];
        }
        $html = <<<END

            <form method="post">
                 <p>Votre nom<span class="required">*</span> : <input type="text" name="nom" value="{$nom}" required/></p>
                 <p>Votre message : <input type="text" name="message" /></p>
                 <p><input class="bouton" type="submit" value="OK"></p>
            </form>
END;
        return $html;
    }

    public function render(array $vars, int $typeAffichage): string {
        switch ($typeAffichage) {
            case ParticipantVue::HOME:
                $content = $this->pageHome();
                break;
            case ParticipantVue::LISTE_CONTENT:
                $content = $this->uneListeHtml($this->data[0], $vars);
                break;
            case ParticipantVue::ITEM_SEUL:
                $content = $this->unItemHtml($this->data[0], $vars);
                break;
        }

        $html = <<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Wishlist</title>
        <link rel="stylesheet" href="{$vars['basepath']}/web/css/index.css">
    </head>
    <body>
        $content
    </body>
</html>
END;

        return $html;
    }
}