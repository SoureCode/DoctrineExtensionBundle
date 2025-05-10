<?php

namespace EventListener;

use App\Entity\AutoDetectDate;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class AutoDetectPropertyListenerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        self::createKernel();

        $this->setUpDatabase([
            AutoDetectDate::class,
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $entity = new AutoDetectDate();
        $entity->setTitle('yeet');

        // Act
        $entityManager->persist($entity);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($entity->getCreatedAt());
        $this->assertNull($entity->getUpdatedAt());
    }

    public function testUpdate(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);

        $entity = new AutoDetectDate();
        $entity->setTitle('yeet');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNotNull($entity->getCreatedAt());
        $this->assertNull($entity->getUpdatedAt());

        // reset
        $entityManager->clear();

        // Act
        /**
         * @var AutoDetectDate $entity
         */
        $entity = $entityManager->getRepository(AutoDetectDate::class)->find($entity->getId()); // Fetch the entity again
        $entity->setTitle('barnacles');

        $entityManager->persist($entity);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($entity->getCreatedAt());
        $this->assertNotNull($entity->getUpdatedAt());
    }
}
