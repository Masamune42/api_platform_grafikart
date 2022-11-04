# API Platform Grafikart
## Versions utilisées
- PHP 8.0.13
- MariaDB 10.4.10
- Symfony 5.4

## Premiers pas
```
composer create-project symfony/skeleton tuto-api
cd .\tuto-api\
composer req api
> Si la dernière ne marche pas faire la commande + vérifier la version de PHP utilisée
composer self-update
```

Configuration de la BDD dans le fichier .env
```
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
composer req symfony/maker-bundle --dev
php bin/console make:entity Post
```
On crée les attributs suivants :
- title : string : 255 no
- slug : string : 255 : no
- content : text : no
- createdAt : datetime_immutable : no
- updatedAt : datetime_immutable : no

### Option 1
Dans Post.php :
```php
// PHP 7
// Ajout de la bibliothèque
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

// Ajout de la ligne @ApiResource()
/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ApiResource()
 */
class Post
{
    
}

// PHP 8 (utilisé par la suite dans le cours)
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ApiResource]
class Post
{
    
}
```

On peut aller sur http://127.0.0.1:8000/api pour observer la pages avec les différentes méthodes :

__GET__
/api/posts
Récupérer la liste des articles

__POST__
/api/posts
Créer un article

__GET__
/api/posts/{id}
Récupérer un article.

__PUT__
/api/posts/{id}
Modifier un article.

__DELETE__
/api/posts/{id}
Supprimer un article.

__PATCH__
/api/posts/{id}
Modifier partiellement un article.

### Option 2
On crée un fichier config\api_platform\resources.yaml
```yaml
App\Entity\Post: ~
```
Dans config\packages\api_platform.yaml on ajoute un path
```yaml
paths: ['%kernel.project_dir%/src/Entity', '%kernel.project_dir%/config/api_platform']
```

On crée une migration et on l'envoie en BDD
```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Requête POST
- On déplie le menu POST sur http://127.0.0.1:8000/api
- On observe une requête autogénérée suivant les types indiquées dans l'entité avec les annotations
- On clique sur le bouton "try it out" afin de tester une requête POST
```json
{
  "title": "Mon premier article",
  "slug": "mon-premier-article",
  "content": "Bonjour les gens",
  "createdAt": "2022-10-31T13:57:47.479Z",
  "updatedAt": "2022-10-31T13:57:47.479Z"
}
```
- On observe la commande curl générée, la réponse donnée par le serveur (201) et le body
- le renvoie des données est configurable dans api_platform.yaml


### Requête GET
- On peut récupérer la liste des items créés (un seul pour le moment)
- On peut aussi récupérer l'article par ID (1)


### Création des catégories
```
php bin/console make:entity Category
```
On crée les attributs suivants :
- name : string : 255 : no

```
php bin/console make:entity Post
```
On crée les attributs suivants :
- category : relation : yes : yes

On ajout ApiResource comme dans Post
```php
#[ApiResource]
class Category
{

}
```
On crée 2 catégories via la commande POST
```json
{
  "name": "Catégorie #1"
}
{
  "name": "Catégorie #2"
}
```

### Attribution de catégorie
On récupère la liste des catégories
```json
{
  "@context": "/api/contexts/Category",
  "@id": "/api/categories",
  "@type": "hydra:Collection",
  "hydra:member": [
    {
      "@id": "/api/categories/1",
      "@type": "Category",
      "id": 1,
      "name": "Catégorie #1",
      "posts": []
    },
    {
      "@id": "/api/categories/2",
      "@type": "Category",
      "id": 2,
      "name": "Catégorie #2",
      "posts": []
    }
  ],
  "hydra:totalItems": 2
}
```
On récupère le champ @id pour l'utilsier dans une méthode PUT pour l'article 1
```json
{
  "category": "/api/categories/1"
}
```

## La sérialisation
- Quand on envoie des donneés, elles vont être transformées sous forme de tableau PHP
- Ce tableau sera ensuite encodé en JSON, XML ou autre...
- Par défaut, API Platform va prendre tous les champs de l'entité et va les convertir en tableau
- On peut piloter quels champs peuvent être convertis à travers des groupes de normalisation

```php
// Post.php
#[ORM\Entity(repositoryClass: PostRepository::class)]
// normalizationContext : Permet de choisir les groupes pour normaliser (GET)
// denormalizationContext : Permet de choisir les groupes pour dénormaliser (PUT, POST)
// itemOperations : Permet de choisir les méthodes spécifiques réalisables avec ce qui est modifiable par groupe
// Pour la méthode GET : On normalise les groupes read:collection, read:item et read:Post (ajotués dans Category)
// Pour la méthode PUT : denormalization_context => seulement les groupes appelés put:Post (ici le titre du Post)
#[ApiResource(
    normalizationContext: ['groups' => ['read:collection']],
    denormalizationContext: ['groups' => ['write:Post']],
    itemOperations: [
        // 'put' => [
        //     'denormalization_context' => ['groups' => ['write:Post']]
        // ],
        'put',
        'delete',
        'get' => [
            'normalization_context' => ['groups' => ['read:collection', 'read:item', 'read:Post']]
        ]
    ]
)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection', 'write:Post'])]
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

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['read:item', 'write:Post'])]
    private ?Category $category = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
}

// Category.php
// On ajoute les champs id et name au groupe Post que l'on récupère dans Post.php
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ApiResource()]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('read:Post')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('read:Post')]
    private ?string $name = null;
}
```
On récupère bien les valeurs sélectionnés dans l'entité Category
```json
{
  "@context": "/api/contexts/Post",
  "@id": "/api/posts/1",
  "@type": "Post",
  "id": 1,
  "title": "Mon premier article",
  "slug": "mon-premier-article",
  "content": "Bonjour les gens",
  "createdAt": "2022-10-31T13:57:47+00:00",
  "category": {
    "@id": "/api/categories/1",
    "@type": "Category",
    "id": 1,
    "name": "Catégorie #1"
  }
}
```

Ecriture des noms de groupes
- read:Post:item
- Partie lecture:type de l'entité:pour la collection ou l'item

## La validation
Pour controller ce qui rentre dans notre structure via des règles.

### Longueur du champ
```php
#[ORM\Column(length: 255)]
#[
    Groups(['read:Post', 'write:Post']),
    Length(min: 3)
]
// Taille min: 3
private ?string $name = null;
```

### Groupe de validation
On va mettre en place la validation uniquement sur la méthode POST
```php
// Option 1
// Dans ApiResource :
collectionOperations: [
      'get',
      'post' => [
          'validation_groups' => ['create:Post']
      ]
  ]

// Uniquement les groupes create:Post
#[ORM\Column(length: 255)]
#[
    Groups(['read:collection', 'write:Post']),
    Length(min: 5, groups: ['create:Post'])
]
private ?string $title = null;
```
On peut aussi utiliser une fonction static
```php
// Option 2 
// Dans ApiResource :
collectionOperations: [
      'get',
      'post' => [
        'validation_groups' => [Post::class, 'validationGroups']
      ]
  ]

// Fonction qui retourne les groupes de validations à utiliser
public static function validationGroups(self $post)
{
    return ['create:Post'];
}
```

### Création de la catégorie en même temps que l'article
Via la commande suivante de création de Post
```json
{
  "title": "Article avec catégorie",
  "slug": "article_avec_categorie",
  "category": {
    "name": "Catégorie de l'article"
  }
}
```
```php
// Dans ApiResource
collectionOperations: [
    'get',
    'post'
],

// On ajoute le grupe write:Post pour préciser qu'il appartient au groupe denormalizationContext déclaré dans APiResource
#[ORM\Column(length: 255)]
#[
    Groups(['read:Post', 'write:Post']),
    Length(min: 3)
]
private ?string $name = null;

// On autorise la création en cascade de la catégorie
// On ajoute Valid() pour vérifier que la règle de création en cascade est valide (min: 3)
#[ORM\ManyToOne(inversedBy: 'posts', cascade: ['persist'])]
#[
    Groups(['read:item', 'write:Post']),
    Valid()
]
private ?Category $category = null;
```