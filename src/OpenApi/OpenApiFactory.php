<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    // On reçoit OpenApiFactoryInterface (obje par défaut d'API Platform) 
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
        
    }
    
    public function __invoke(array $context = []): OpenApi
    {
        // On appelle la méthode parente en passant le paramètre du contexte
        $openAPi = $this->decorated->__invoke($context);
        // On récupères l'ensemble des chemins dans un tableau et on boucle
        /**
         * @var PathItem $path
         */
        foreach($openAPi->getPaths()->getPaths() as $key => $path)
        {
            // Si on a la méthode GET ET que le chemin du GET avec summary en paramètre = 'hidden'
            if($path->getGet() && $path->getGet()->getSummary() === 'hidden') {
                // On ajoute un chemin sur ce que j'ai déjà => Pour la clé $key, on utilise le chemin à null
                $openAPi->getPaths()->addPath($key, $path->withGet(null));
            }
        }
        // EXEMPLE :
        // On ajoute un chemin accessible via /ping et on explique en 2e paramètre ce qu'il doit faire / réponse
        $openAPi->getPaths()->addPath('/ping', new PathItem(null, 'Ping', null, new Operation('ping-id', [], [], 'Répond')));
        return $openAPi;
    }
}