<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
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
        $openApi = $this->decorated->__invoke($context);
        // On récupères l'ensemble des chemins dans un tableau et on boucle
        /**
         * @var PathItem $path
         */
        foreach ($openApi->getPaths()->getPaths() as $key => $path) {
            // Si on a la méthode GET ET que le chemin du GET avec summary en paramètre = 'hidden'
            if ($path->getGet() && $path->getGet()->getSummary() === 'hidden') {
                // On ajoute un chemin sur ce que j'ai déjà => Pour la clé $key, on utilise le chemin à null
                $openApi->getPaths()->addPath($key, $path->withGet(null));
            }
        }
        // EXEMPLE :
        // On ajoute un chemin accessible via /ping et on explique en 2e paramètre ce qu'il doit faire / réponse
        // $openApi->getPaths()->addPath('/ping', new PathItem(null, 'Ping', null, new Operation('ping-id', [], [], 'Répond')));

        // On récupère les schémas de sécurité et on y crée un nouveau
        $schemas = $openApi->getComponents()->getSecuritySchemes();
        $schemas['cookieAuth'] = new \ArrayObject([
            'type' => 'apiKey',
            'in' => 'cookie',
            'name' => 'PHPSESSID',
        ]);

        // On crée un schéma Credentials
        $schemas = $openApi->getComponents()->getSchemas();
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'john@doe.fr'
                ],
                'password' => [
                    'type' => 'string',
                    'example' => '0000',
                ]
            ],
        ]);
        // EXEMPLE : Il faut être indentifié pour accéder à toutes les routes de l'API
        // $openApi = $openApi->withSecurity(['cookieAuth' => []]);

        // On veut modifier l'opération de /api/me
        // On récupère l'opération qu'on a transformée
        $meOperation = $openApi->getPaths()->getPath('/api/me')->getGet()->withParameters([]);
        // On passe l'opération modifiée
        $mePathItem = $openApi->getPaths()->getPath('/api/me')->withGet($meOperation);
        // On ajoute le chemin modifié
        $openApi->getPaths()->addPath('/api/me', $mePathItem);

        // On ajoute notre chemin pour se connecter
        $pathItem = new PathItem(
            // Création d'une opération quand on post
            post: new Operation(
                // Nom de l'operationId (unique)
                operationId: 'postApiLogin',
                // Catégorie de tag où l'on affiche le chemin à utiliser
                tags: ['Auth'],
                // Cas où les informations ne sont pas valides
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            // On utilise le schéma créé avant
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                ),
                // Réponse de type 200 quand l'utilisateur s'est bien connecté
                responses: [
                    '200' => [
                        'description' => 'Utilisateur connecté',
                        'content' => [
                            // On fait référence au schéma de l'utilisateur
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/User-read.User'
                                ]
                            ]
                        ]
                    ]
                ]
            )
        );

        // On ajoute le nouveau chemin
        $openApi->getPaths()->addPath('/api/login', $pathItem);

        // On ajoute notre chemin pour se déconnecter
        $pathItem = new PathItem(
            // Création d'une opération quand on post
            post: new Operation(
                // Nom de l'operationId (unique)
                operationId: 'postApiLogout',
                // Catégorie de tag où l'on affiche le chemin à utiliser
                tags: ['Auth'],
                // Réponse de type 204 sans contenu
                responses: [
                    '204' => []
                ]
            )
        );

        // On ajoute le nouveau chemin
        $openApi->getPaths()->addPath('/api/logout', $pathItem);
        return $openApi;
    }
}
