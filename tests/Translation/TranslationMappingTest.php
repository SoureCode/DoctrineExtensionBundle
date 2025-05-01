<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Translation;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;
use SoureCode\Bundle\DoctrineExtension\Translation\TranslationMapping;

class TranslationMappingTest extends AbstractKernelTestCase
{

    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpDatabase([
            Product::class
        ]);
    }

    public function testGetMapping(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->getMapping();

        // Assert
        $this->assertSame([
            Product::class => ProductTranslation::class,
        ], $actual);
    }

    public function testGetReverseMapping(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->getReverseMapping();

        // Assert
        $this->assertSame([
            ProductTranslation::class => Product::class,
        ], $actual);
    }

    public function testHasTranslationClass(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->hasTranslationClass(ProductTranslation::class);

        // Assert
        $this->assertTrue($actual);
    }

    public function testHasTranslatableClass(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->hasTranslatableClass(Product::class);

        // Assert
        $this->assertTrue($actual);
    }

    public function testGetTranslationClassNames(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->getTranslationClassNames();

        // Assert
        $this->assertSame([
            ProductTranslation::class,
        ], $actual);
    }

    public function testGetTranslatableClassNames(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->getTranslatableClassNames();

        // Assert
        $this->assertSame([
            Product::class,
        ], $actual);
    }

    public function testGetTranslatableClass(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->getTranslatableClass(ProductTranslation::class);

        // Assert
        $this->assertSame(Product::class, $actual);
    }

    public function testGetTranslationClass(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var TranslationMapping $translationMapping
         */
        $translationMapping = $container->get('soure_code.doctrine_extension.mapping.translation');

        // Act
        $actual = $translationMapping->getTranslationClass(Product::class);

        // Assert
        $this->assertSame(ProductTranslation::class, $actual);
    }
}
