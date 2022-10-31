# API Platform Grafikart

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