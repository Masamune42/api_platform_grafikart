<?php

namespace App\Repository;

use Ramsey\Uuid\Uuid;
use App\Entity\Dependency;

class DependencyRepository
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


    /**
     * Undocumented function
     *
     * @return Dependency[]
     */
    public function findAll(): array
    {
        $items = [];
        // On récupère chaque dépendance
        foreach ($this->getDependencies() as $name => $version) {
            // On rempli les items avec des Dependency
            // Uuid : on crée un uuid unique via une librairie (ramsey/uuid)
            $items[] = new Dependency($name, $version);
        }
        return $items;
    }

    public function find(string $uuid): ?Dependency
    {
        // On récupère les dépendances
        $dependencies = $this->getDependencies();
        foreach ($this->findAll() as $dependency) {
            if ($dependency->getUuid() === $uuid) {
                return $dependency;
            }
        }
        return null;
    }

    public function persist(Dependency $dependency)
    {
        $path = $this->rootPath . '/composer.json';
        $json = json_decode(file_get_contents($path), true);
        $json['require'][$dependency->getName()] = $dependency->getVersion();
        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function remove(Dependency $dependency)
    {
        $path = $this->rootPath . '/composer.json';
        $json = json_decode(file_get_contents($path), true);
        unset($json['require'][$dependency->getName()]);
        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
