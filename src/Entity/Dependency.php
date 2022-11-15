<?php

namespace App\Entity;

use Ramsey\Uuid\Uuid;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource(
    // On ne récupère que les méthodes GET pour les items et collections
    itemOperations: ['get', 'post'],
    collectionOperations: [
        'get',
        'delete',
        'put' => [
            'denormalization_context' => [
                'groups' => ['put:Dependency']
            ]
        ]
    ],
    // On désactive la pagination
    paginationEnabled: false
)]
class Dependency
{
    #[ApiProperty(
        identifier: true
    )]
    private string $uuid;

    #[
        ApiProperty(
            description: 'Nom de la dépendance'
        ),
        Length(min: 2),
        NotBlank(),
        Groups(['put:Dependency'])
    ]
    private string $name;

    #[ApiProperty(
        description: 'Version de la dépendance',
        openapiContext: [
            'example' => '5.4.*'
        ]
    )]
    private string $version;

    public function __construct(
        string $name,
        string $version
    ) {
        $this->uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Get the value of uuid
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Get the value of name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @return  self
     */ 
    public function setVersion($version): Dependency
    {
        $this->version = $version;

        return $this;
    }
}
