<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Traits;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractWebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class BlameableTraitTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        self::createClient();

        $this->setUpDatabase([
            Category::class,
            User::class,
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        /**
         * @var KernelBrowser $client
         */
        $client = self::getClient();
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User();
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $category = new Category();
        $category->setTitle('Test Title');

        // Act
        $entityManager->persist($category);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($category->getCreatedBy());
        $this->assertNull($category->getUpdatedBy());
    }

    public function testUpdate(): void
    {
        // Arrange
        /**
         * @var KernelBrowser $client
         */
        $client = self::getClient();
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User();
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $category = new Category();
        $category->setTitle('Test Title');

        $entityManager->persist($category);
        $entityManager->flush();

        $this->assertNotNull($category->getCreatedBy());
        $this->assertNull($category->getUpdatedBy());

        $entityManager->clear();
        // reattach the user to the uow
        $entityManager->persist($user);

        // Act
        /**
         * @var Category $category
         */
        $category = $entityManager->getRepository(Category::class)->find($category->getId()); // Fetch the article again
        $category->setTitle('Updated Title');

        $entityManager->persist($category);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($category->getCreatedBy());
        $this->assertNotNull($category->getUpdatedBy());
    }
}
