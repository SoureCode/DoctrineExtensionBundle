<?php

namespace EventListener;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class CustomPropertyListenerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        self::createKernel();

        $this->setUpDatabase([
            Movie::class,
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $movie = new Movie();

        // Act
        $entityManager->persist($movie);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($movie->getName());
        $this->assertNull($movie->getEtag());
    }

    public function testUpdate(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);

        $movie = new Movie();

        $entityManager->persist($movie);
        $entityManager->flush();

        $this->assertNotNull($movie->getName());
        $this->assertNull($movie->getEtag());

        // reset
        $entityManager->clear();

        // Act
        /**
         * @var Movie $movie
         */
        $movie = $entityManager->getRepository(Movie::class)->find($movie->getId()); // Fetch the movie again
        $movie->setName('yeet');

        $entityManager->persist($movie);
        $entityManager->flush();

        // Assert
        $this->assertSame('yeet', $movie->getName());
        $this->assertNotNull($movie->getEtag());
    }
}
