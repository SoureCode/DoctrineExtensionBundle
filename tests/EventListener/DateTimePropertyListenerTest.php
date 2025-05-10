<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\EventListener;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class DateTimePropertyListenerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpDatabase([
            Post::class,
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $post = new Post();
        $post->setTitle('Test Title');

        // Act
        $entityManager->persist($post);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($post->getCreatedAt());
        $this->assertNull($post->getUpdatedAt());
    }

    public function testUpdate(): void
    {
        // Arrange
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $post = new Post();
        $post->setTitle('Test Title');

        $entityManager->persist($post);
        $entityManager->flush();

        $this->assertNotNull($post->getCreatedAt());
        $this->assertNull($post->getUpdatedAt());

        // reset
        $entityManager->clear();

        // Act
        $post = $entityManager->getRepository(Post::class)->find($post->getId()); // Fetch the post again
        $post->setTitle('Updated Title');

        $entityManager->persist($post);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($post->getCreatedAt());
        $this->assertNotNull($post->getUpdatedAt());
    }
}
