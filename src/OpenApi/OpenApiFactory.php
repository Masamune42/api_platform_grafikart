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

        $schemas = $openApi->getComponents()->getSecuritySchemes();
        $schemas['cookieAuth'] = new \ArrayObject([
            'type' => 'apiKey',
            'in' => 'cookie',
            'name' => 'PHPSESSID',
        ]);

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
        // $openApi = $openApi->withSecurity(['cookieAuth' => []]);
        $pathItem = new PathItem(
            post: new Operation(
                operationId: 'postApiLogin',
                tags: ['Auth'],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Utilisateur connecté',
                        'content' => [
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

        $openApi->getPaths()->addPath('/api/login', $pathItem);
        return $openApi;
    }
}
