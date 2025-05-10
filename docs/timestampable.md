
# Timestampable

The **Timestampable** extension automatically manages the creation and update timestamps for your entities.
It adds two fields to your entity: `createdAt` and `updatedAt`.
These fields are automatically populated when the entity is created or updated, respectively.

## Usage

To use the **Timestampable** extension, your target entity class must implement the `TimestampableInterface` and optionally use the `TimestampableTrait`.

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Contracts\TimestampableInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\TimestampAwareTrait;

#[ORM\Entity]
class User implements TimestampableInterface {
    use TimestampAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

After implementing the interface and using the trait do not forget to generate migrations and update the database schema.

```bash
symfony console doctrine:migrations:diff
```

or if you are using the maker bundle

```bash
symfony console make:migration
```
