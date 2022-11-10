<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\PostCountController;
use App\Controller\PostPublishController;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[
    ApiResource(
        normalizationContext: [
            'groups' => ['read:collection'],
            'openapi_definition_name' => 'Collection',
        ],
        denormalizationContext: ['groups' => ['write:Post']],
        paginationItemsPerPage: 2,
        paginationMaximumItemsPerPage: 2,
        paginationClientItemsPerPage: true,
        collectionOperations: [
            'get',
            'post',
            'count' => [
                'method' => 'GET',
                'path' => '/posts/count',
                'controller' => PostCountController::class,
                'read' => false,
                'pagination_enabled' => false,
                'filters' => [],
                'openapi_context' => [
                    'summary' => 'Retourne le nombre total d\'article',
                    'parameters' => [
                        [
                            'in' => 'query',
                            'name' => 'online',
                            'schema' => [
                                'type' => 'integer',
                                'maximum' => 1,
                                'minimum' => 0,
                            ],
                            'description' => 'Filtre les articles en ligne'
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'OK',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'integer',
                                        'example' => 3
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
            // 'post' => [
            //     'validation_groups' => [Post::class, 'validationGroups']
            // ]
        ],
        itemOperations: [
            // 'put' => [
            //     'denormalization_context' => ['groups' => ['write:Post']]
            // ],
            'put',
            'delete',
            'get' => [
                'normalization_context' => [
                    'groups' => ['read:collection', 'read:item', 'read:Post'],
                    'openapi_definition_name' => 'Detail',
                    ]
            ],
            'publish' => [
                'method' => 'POST',
                'path' => '/posts/{id}/publish',
                'controller' => PostPublishController::class,
                'openapi_context' => [
                    'summary' => 'Permet de publier un article',
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => []
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ),
    ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'title' => 'partial'])
]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[
        Groups(['read:collection', 'write:Post']),
        Length(min: 5, groups: ['create:Post'])
    ]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection', 'write:Post'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['read:item', 'write:Post'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['read:item'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts', cascade: ['persist'])]
    #[
        Groups(['read:item', 'write:Post']),
        Valid()
    ]
    private ?Category $category = null;

    #[ORM\Column(options: [
        "default" => "0"
    ])]
    #[
        Groups(['read:collection']),
        ApiProperty(openapiContext: ['type' => 'boolean', 'description' => 'En ligne ou pas ?'])
    ]
    private ?bool $online = false;

    // public static function validationGroups(self $post)
    // {
    //     return ['create:Post'];
    // }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function isOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }
}
