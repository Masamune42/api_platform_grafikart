<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;

class PostCountController
{
    public function __construct(private PostRepository $postRepo)
    {
        
    }

    public function __invoke(Request $request): int
    {
        // On récupère le paramètre "online" du GET
        $onlineQuery = $request->get('online');
        $conditions = [];
        // Si on a défini un paramètre online dans al requête
        if($onlineQuery !== null) {
            // 1 => on cherche les articles en ligne
            $conditions = ['online' => $onlineQuery === '1' ? true : false];
        }
        // On retourne le résultat où l'on compte avec la condition sur online
        return $this->postRepo->count($conditions);
    }
}