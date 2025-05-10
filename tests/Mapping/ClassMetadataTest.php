<?php

namespace Mapping;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use SoureCode\Bundle\DoctrineExtension\Attributes\Mode;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataFactory;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class ClassMetadataTest extends AbstractKernelTestCase
{
    public function testCreateMapping(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var ClassMetadataFactory $classMetadataFactory
         */
        $classMetadataFactory = $container->get('soure_code.doctrine_extension.class_metadata.factory');

        // Act
        $actual = $classMetadataFactory->create(Product::class);

        // Assert
        $this->assertEquals([
            'className' => Product::class,
            'translationClassName' => ProductTranslation::class,
            'translatableClassName' => Product::class,
            'persistProperties' => [
                'createdAt' => [
                    'name' => 'createdAt',
                    'provider' => 'DateTimeImmutable',
                    'propertyType' => 'DateTimeImmutable',
                    'mode' => Mode::ALWAYS,
                    'doctrineType' => 'datetime_immutable',
                    'nullable' => false,
                ],
            ],
            'updateProperties' => [
                'updatedAt' => [
                    'name' => 'updatedAt',
                    'provider' => 'DateTimeImmutable',
                    'propertyType' => 'DateTimeImmutable',
                    'mode' => Mode::ALWAYS,
                    'doctrineType' => 'datetime_immutable',
                    'nullable' => true,
                ],
            ],
        ], $actual->toArray());
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpDatabase([
            Product::class,
        ]);
    }
}
