<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use App\Entity\Dependency;
use App\Repository\DependencyRepository;
use Ramsey\Uuid\Uuid;

class DependencyDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, ItemDataProviderInterface
{

    public function __construct(private DependencyRepository $repository)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // Si la classe qui renvoyée et une Dependency
        return $resourceClass === Dependency::class;
    }

    // Fonction qui permet de récupérer un item
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        return $this->repository->find($id);
    }
    
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        return $this->repository->findAll();
    }
}
