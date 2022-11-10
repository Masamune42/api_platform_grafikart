<?php

namespace App\Controller;

use App\Entity\Post;

class PostPublishController
{
    // Fonction à utiliser avec Post en paramètre
    public function __invoke(Post $data): Post
    {
        // On met le champ online à true dans BDD
        $data->setOnline(true);
        // Tout l'enregistrement se fait derrière, sauf si on a configuré autrement dans Post
        return $data;
    }
}