<?php
namespace wishlist\vue;
class ParticipantVue
{

    private $data;

    /**
     * Constante correspondant à l'affichage d'un lien vers un item
     * @var int
     */
    const HOME = 1;

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

    public function render(array $vars, int $typeAffichage): string {
        switch ($typeAffichage) {
            case ParticipantVue::HOME:
                $content = $this->pageHome();
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