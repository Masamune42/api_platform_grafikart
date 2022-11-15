<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use App\Entity\Dependency;
use Ramsey\Uuid\Uuid;

class DependencyDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, ItemDataProviderInterface
{
    // On récupère le chemin racine dans le constructeur (+ On ajoute dans services.yaml la configuration)
    public function __construct(private string $rootPath)
    {
    }

    /**
     * Récupère les dépendances dans le fichier json du projet
     *
     * @return array Tableau des dépendances
     */
    private function getDependencies(): array
    {
        // On récupère le chemin vers composer.json
        $path = $this->rootPath . '/composer.json';
        // On récupère le json et on le transpore en tableau PHP
        $json = json_decode(file_get_contents($path), true);
        // On renvoie les dépendances
        return $json['require'];
    }

    // Fonction qui retourne une collection d'items
    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $items = [];
        // On récupère chaque dépendance
        foreach ($this->getDependencies() as $name => $version) {
            // On rempli les items avec des Dependency
            // Uuid : on crée un uuid unique via une librairie (ramsey/uuid)
            $items[] = new Dependency(Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString(), $name, $version);
        }
        return $items;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // Si la classe qui renvoyée et une Dependency
        return $resourceClass === Dependency::class;
    }

    // Fonction qui permet de récupérer un item
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // On récupère les dépendances
        $dependencies = $this->getDependencies();
        // Pour chaque dépendance, on vérifier si l'uuid recherché correspond à un qui appartient aux dépendances on retourne la dépendance
        foreach ($dependencies as $name => $version) {
            $uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();
            if ($uuid === $id) {
                return new Dependency($uuid, $name, $version);
            }
        }
        return null;
    }
}
