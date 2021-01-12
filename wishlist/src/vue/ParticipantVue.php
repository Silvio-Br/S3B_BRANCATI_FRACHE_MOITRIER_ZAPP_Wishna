<?php
namespace wishlist\vue;
use wishlist\models\Item;
use wishlist\models\Liste;

class ParticipantVue
{

    private $data;

    /**
     * constante correspondante à l'affichage de la page home
     * @var int
     */
    const HOME = 1;

    /**
     * constante correspondante à l'affichage du contenu d'une liste non expirée
     * @var int
     */
    const LISTE_CONTENT_NON_EXPIRE = 2;

    /**
     * constante correspondante à l'affichage de la page d'un item
     * @var int
     */
    const ITEM_SEUL = 3;

    /**
     * constante correspondante à o'affichage d'un message et d'un bouton de redirection
     */
    const MESSAGE = 4;

    /**
     * constante correspondante à l'affichage du contenu d'une liste expirée
     * @var int
     */
    const LISTE_CONTENT_EXPIRE = 5;

    /**
     * ParticipantVue constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * methode retournant le code HTML de la page home
     * @return string
     */
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

    /**
     * methode retournant le code HTML du contenu d'une liste
     * @param Liste $liste
     * @param $vars
     * @return string
     */
    private function uneListeHtmlNonExpiree(Liste $liste, $vars): string {
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
                $html .= $this->unItem($vars['objets'][$i][0], $vars['basepath'], $vars['objets'][$i][1], $vars['etreCreateur']);
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

    private function uneListeHtmlExpiree(Liste $liste, $vars): string {
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
                    <th>Nom participant</th>
                    <th>Message</th>
                </tr>
END;
            for ($i = 0; $i < sizeOf($vars['objets']); $i++) {
                $html .= $this->unItemExpire($vars['objets'][$i][0], $vars['basepath'], $vars['objets'][$i][1]);
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

    /**
     * methode retournant le code HMTL de la page d'un item
     * @param Item $item
     * @param $v
     * @return string
     */
    private function unItemHtml(Item $item, $v): string {
        $reservation = " : Non";
        if ($item->reservation && !$v['etreCreateur']) {
            $reservation = " par $item->nom_reservation";
        } elseif ($item->reservation && $v['etreCreateur']) {
            $reservation = " : Oui";
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
        if (!$item->reservation && !$v['expire']) {
            $_GET['id']=$item->id;
            $html .= $this->unFormulaireReservation($v);
        }
        return $html;
    }

    /**
     * methode retournant le code HTML de la ligne d'un item dans le tableau d'affichage de la liste
     * @param Item $item
     * @param $basepath
     * @param $url
     * @return string
     */
    private function unItem(Item $item, $basepath, $url, $etreCreateur): string {
        $reservation = "Non";
        $img = null;
        if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
            $img = "{$basepath}/web/img/$item->img";
        } else {
            $img = $item->img;
        }

        if ($item->reservation && !$etreCreateur) {
            $reservation = "$item->nom_reservation";
        } elseif ($item->reservation && $etreCreateur) {
            $reservation = "Oui";
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

    private function unItemExpire(Item $item, $basepath, $url): string {
        $html = "";
        if ($item->reservation) {
            $reservation = "$item->nom_reservation";
            $img = null;
            if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
                $img = "{$basepath}/web/img/$item->img";
            } else {
                $img = $item->img;
            }

            $html = <<<END
            
                <tr>
                    <td><a href="$url">$item->nom</a></td>
                    <td><img class="imageItem" alt="image" src="$img"></td>
                    <td><p class="nom">$reservation</p></td>
                    <td><p class="nom">$item->message_reservation</p></td>
                </tr>
END;
        }

        return $html;
    }

    /**
     * methode retournant le code HTML du formulaire de réservation d'un item
     * @param $v
     * @return string
     */
    private function unFormulaireReservation($v): string {
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

    /**
     * methode retournant le code HTML d'un message avec un bouton de redirection
     * @param $vars
     * @return string
     */
    public function unMessage($vars): string
    {
        $html = <<<END
<p class="message">{$vars['message']}</p>
<button onclick="window.location.href='{$vars['url']}'">Ok</button>
END;
        return $html;

    }

    public function render(array $vars, int $typeAffichage): string {
        switch ($typeAffichage) {
            case ParticipantVue::HOME:
                $content = $this->pageHome();
                break;
            case ParticipantVue::LISTE_CONTENT_NON_EXPIRE:
                $content = $this->uneListeHtmlNonExpiree($this->data[0], $vars);
                break;
            case ParticipantVue::LISTE_CONTENT_EXPIRE:
                $content = $this->uneListeHtmlExpiree($this->data[0], $vars);
                break;
            case ParticipantVue::ITEM_SEUL:
                $content = $this->unItemHtml($this->data[0], $vars);
                break;
            case ParticipantVue::MESSAGE:
                $content = $this->unMessage($vars);
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