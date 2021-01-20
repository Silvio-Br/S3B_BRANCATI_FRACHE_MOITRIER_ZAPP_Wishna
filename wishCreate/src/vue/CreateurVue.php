<?php

namespace wishcreate\vue;
use wishcreate\models\Item;
use wishcreate\models\Liste;

class CreateurVue
{

    private $data;

    /**
     * constante correspondante à l'affichage de la page home
     */
    const HOME = 1;

    /**
     * constante correspondante à l'affichage d'une liste non expirée
     */
    const LISTE_NON_EXPIREE = 2;

    /**
     * constante correspondante à la modification d'un item
     */
    const MODIFIER_ITEM = 3;

    /**
     * constante correspondante à la modification d'une liste
     */
    const MODIFIER_LISTE = 4;

    /**
     * constante correspondante à la création d'une liste
     */
    const CREATE = 5;

    /**
     * constante correspondante à l'affichage d'un message avec un lien de retour
     */
    const MESSAGE = 6;

    /**
     * constante correspondante à l'ajout d'un item dans une liste
     */
    const AJOUTER_ITEM = 7;

    /**
     * constante correspondante à l'affichage d'une liste expirée
     */
    const LISTE_EXPIREE = 8;


    /**
     * CreateurVue constructor.
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
                 <p>Token de la liste : <input type="text" name="token" required/></p>
                 <p><input type="submit" value="OK" name="bouton"></p>
            </form>

        <form method="post">
                 <p><input type="submit" value="Créer une nouvelle liste" name="bouton"></p>
            </form>
END;
        return $html;
    }

    private function uneListeHtml(Liste $liste, $vars): string {
        $html = <<<END
<section class="titreListe">
            <h3 class="nom">{$liste->titre}</h3>
            <p class="desc">{$liste->description}</p>
            <p class="date">{$liste->expiration}</p>
        </section>
        <section class="modifier">
            <button onclick="location.href='{$vars['modifier']}'">Modifier</button>
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
            $html .= "<p>Aucuns items dans votre liste</p>";
        }

        $html .= <<<END

         <section class='ajouter'>
            <button onclick="location.href='{$vars['ajouter']}'">Ajouter un item</button>
         </section>
         <section class='partager'>
            <button onclick="window.location.href='{$vars['share']}'">Partager ma liste</button>
         </section>
END;
        return $html;
    }

    private function uneListeExpireeHtml(Liste $liste, $vars): string {
        $html = <<<END
<section class="titreListe">
            <h3 class="nom">{$liste->titre}</h3>
            <p class="desc">{$liste->description}</p>
            <p class="date">{$liste->expiration}</p>
        </section>
        <section class="modifier">
            <p>Vous ne pouvez plus modifier cette liste</p>
        </section>
END;
        if (sizeOf($vars['objets'])>0) {
            $html .= <<<END
                
        <section class="tableau">
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Message</th>
                </tr>
END;
            for ($i = 0; $i < sizeOf($vars['objets']); $i++) {
                $html .= $this->unItemExpire($vars['objets'][$i], $vars['basepath']);
            }
            $html .= <<<END
                
              </table>
          </section>
END;
        } else {
            $html .= "<p>Aucuns items réservés dans votre liste</p>";
        }

        $html .= <<<END

         <section class='ajouter'>
            <p>Vous ne pouvez plus ajouter d'items dans cette liste</p>
         </section>
END;
        return $html;
    }

    private function unItem(Item $item, $basepath, $url): string {
        $reservation = "Non";
        if ($item->reservation) {
            $reservation = "Oui";
            $titre = $item->nom;
        } else {
            $titre = "<a href='{$url}'>{$item->nom}</a>";
        }

        $img = null;
        if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
            $img = "{$basepath}/web/img/$item->img";
        } else {
            $img = $item->img;
        }
        $html = <<<END
            
                <tr>
                    <td>$titre</td>
                    <td><img class="imageItem" alt="image" src="{$img}"></td>
                    <td><p class="reservation">$reservation</p></td>
                </tr>
END;
        return $html;
    }

    private function unItemExpire(Item $item, $basepath): string {
        $img = null;
        if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
            $img = "{$basepath}/web/img/$item->img";
        } else {
            $img = $item->img;
        }
        $html = <<<END
            
                <tr>
                    <td>$item->nom</td>
                    <td><img class="imageItem" alt="image" src="{$img}"></td>
                    <td><p class="reservation">$item->nom_reservation</p></td>
                    <td><p class="reservation">$item->message_reservation</p></td>
                </tr>
END;
        return $html;
    }

    private function unFormulaireModifierItem($item): string
    {
        $prix = floatval($item->tarif);

        $img = null;
        if (!(substr($item->img, 0,4) == "http") && !(substr($item->img, 0,4) == "www") ) {
            $img = "web/img/$item->img";
        } else {
            $img = $item->img;
        }

        $html = <<<END
<form method="post" enctype="multipart/form-data">
            <p>Nom<span class="required">*</span> : <input type="text" name="nom" value="{$item->nom}" required/></p>
            <p>Description<span class="required">*</span> : <input type="text" name="desc" value="{$item->descr} " required/></p>
            <p>Prix<span class="required">*</span> : <input type="number" min="0" step="1" name="prix" value="{$prix}" required/></p>
            <p>Url : <input type="url" name="url" value="{$item->url}"/></p>
           <p>Image :
                <INPUT type= "radio" name="choix" value="lien-interne" checked> <input name="img-int" value="{$img}"/>
                <INPUT type= "radio" name="choix" value="lien-externe"> <input type="file" name="img-ext"/>
            </p>
            <p><input type="submit" value="OK" name="bouton"><input type="submit" value="Supprimer cet item" name="bouton"></p>
        </form>
END;
        return $html;
    }

    private function unFormulaireModifierListe(Liste $liste): string
    {
        $date = new \DateTime("tomorrow");
        $date = $date->format("Y-m-d");
        $html = <<<END
<form method="post">
            <p>Titre<span class="required">*</span> : <input type="text" name="titre" value="{$liste->titre}" required/></p>
            <p>Description<span class="required">*</span> : <input type="text" name="desc" value="{$liste->description}" required/></p>
            <p>Date d'expiration<span class="required">*</span> : <input type="date" name="date" value="{$liste->expiration}" min="{$date}" required/></p>
            <p><input type="submit" value="OK"></p>
        </form>
END;
        return $html;
    }

    private function unFormulaireCreerListe(): string {
        $date = new \DateTime("tomorrow");
        $date = $date->format("Y-m-d");
        $html = <<<END
<form method="post">
            <p>Titre<span class="required">*</span> : <input type="text" name="titre" required/></p>
            <p>Description<span class="required">*</span> : <input type="text" name="desc" required/></p>
            <p>Date d'expiration<span class="required">*</span> : <input type="date" name="date" min="{$date}" required/></p>
            <p>Mettre ma liste en publique <input type="checkbox" value="Liste publique" name="public"></p>
            <p><input type="submit" value="OK"></p>
        </form>
END;
        return $html;
    }

    private function unFormulaireAjouterItem(): string
    {
        $html = <<<END
        <form method="post" enctype="multipart/form-data">
            <p>Nom<span class="required">*</span> : <input type="text" name="nom" required/></p>
            <p>Description<span class="required">*</span> : <input type="text" name="desc" required/></p>
            <p>Prix<span class="required">*</span> : <input type="number" min="0" name="prix" required/></p>
            <p>Url : <input type="url" name="url"/></p>
            <p>Image :
                <INPUT type= "radio" name="choix" value="lien-interne" checked> <input name="img-int" value="web/img/"/>
                <INPUT type= "radio" name="choix" value="lien-externe"> <input type="file" name="img-ext"/>
            </p>
            <p><input type="submit" value="OK"></p>
        </form>
END;
        return $html;
    }

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
            case CreateurVue::HOME:
                $content = $this->pageHome();
                break;
            case CreateurVue::LISTE_NON_EXPIREE:
                $content = $this->uneListeHtml($this->data[0], $vars);
                break;
            case CreateurVue::MODIFIER_ITEM:
                $content = $this->unFormulaireModifierItem($this->data[0]);
                break;
            case CreateurVue::MODIFIER_LISTE:
                $content = $this->unFormulaireModifierListe($this->data[0]);
                break;
            case CreateurVue::CREATE:
                $content = $this->unFormulaireCreerListe();
                break;
            case CreateurVue::MESSAGE:
                $content = $this->unMessage($vars);
                break;
            case CreateurVue::AJOUTER_ITEM:
                $content = $this->unFormulaireAjouterItem();
                break;
            case CreateurVue::LISTE_EXPIREE:
                $content = $this->uneListeExpireeHtml($this->data[0], $vars);
                break;
        }
        $html = <<<END
<!DOCTYPE html>
<html>
    <head>
        <title>WishCreate</title>
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