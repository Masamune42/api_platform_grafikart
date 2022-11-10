<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
        
    }
    
    public function __invoke(array $context = []): OpenApi
    {
        $openAPi = $this->decorated->__invoke($context);
        /**
         * @var PathItem $path
         */
        foreach($openAPi->getPaths()->getPaths() as $key => $path)
        {
            if($path->getGet() && $path->getGet()->getSummary() === 'hidden') {
                $openAPi->getPaths()->addPath($key, $path->withGet(null));
            }
        }
        ;
        $openAPi->getPaths()->addPath('/ping', new PathItem(null, 'Ping', null, new Operation('ping-id', [], [], 'Répond')));
        return $openAPi;
    }
}