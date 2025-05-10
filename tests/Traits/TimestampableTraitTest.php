<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Traits;

use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class TimestampableTraitTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpDatabase([
            Department::class,
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $department = new Department();
        $department->setTitle('Test Title');

        // Act
        $entityManager->persist($department);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($department->getCreatedAt());
        $this->assertNull($department->getUpdatedAt());
    }

    public function testUpdate(): void
    {
        // Arrange
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $department = new Department();
        $department->setTitle('Test Title');

        $entityManager->persist($department);
        $entityManager->flush();

        $this->assertNotNull($department->getCreatedAt());
        $this->assertNull($department->getUpdatedAt());

        // reset
        $entityManager->clear();

        // Act
        $department = $entityManager->getRepository(Department::class)->find($department->getId()); // Fetch the post again
        $department->setTitle('Updated Title');

        $entityManager->persist($department);
        $entityManager->flush();

        // Assert
        $updatedAt = $department->getUpdatedAt();

        $this->assertNotNull($department->getCreatedAt());
        $this->assertNotNull($updatedAt);

        // reset
        $entityManager->clear();
        $container->get('soure_code.doctrine_extension.value_provider.datetime')->reset(); // reset value_provider for date time

        // Act
        $department = $entityManager->getRepository(Department::class)->find($department->getId()); // Fetch the post again
        $department->setTitle('Updated Title 2');

        $entityManager->persist($department);
        $entityManager->flush();

        // Assert
        $updatedAt2 = $department->getUpdatedAt();

        $this->assertNotNull($department->getCreatedAt());
        $this->assertNotNull($updatedAt2);
        $this->assertNotEquals($updatedAt, $updatedAt2);
    }
}
