<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Traits;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class TranslatableTraitTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpDatabase([
            Product::class,
            ProductTranslation::class,
        ]);
    }

    public function testAddTranslation(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);
        $product = new Product();

        $entityManager->persist($product);
        $entityManager->flush();

        $productTranslation = new ProductTranslation();
        $productTranslation->setLocale('en');
        $productTranslation->setName('Test Product');

        // Act
        $product->addTranslation($productTranslation);
        $entityManager->persist($productTranslation);
        $entityManager->flush();

        // Assert
        $entityManager->clear();
        $product = $entityManager->find(Product::class, $product->getId());

        $this->assertCount(1, $product->getTranslations());
        $this->assertEquals('en', $product->getTranslations()->first()->getLocale());
        $this->assertEquals('Test Product', $product->getTranslations()->first()->getName());
    }

    public function testGetTranslations(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);
        $product = new Product();

        $productTranslation1 = new ProductTranslation();
        $productTranslation1->setLocale('en');
        $productTranslation1->setName('Cheese');

        $productTranslation2 = new ProductTranslation();
        $productTranslation2->setLocale('de');
        $productTranslation2->setName('Käse');

        $product->addTranslation($productTranslation1);
        $product->addTranslation($productTranslation2);

        $entityManager->persist($product);
        $entityManager->persist($productTranslation1);
        $entityManager->persist($productTranslation2);

        $entityManager->flush();

        $entityManager->clear();
        $product = $entityManager->find(Product::class, $product->getId());

        // Act
        $actual = $product->getTranslations();

        // Assert
        $this->assertCount(2, $actual);
        $this->assertEquals('en', $actual['en']->getLocale());
        $this->assertEquals('Cheese', $actual['en']->getName());
        $this->assertEquals('de', $actual['de']->getLocale());
        $this->assertEquals('Käse', $actual['de']->getName());
    }

    public function testRemoveTranslation(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);
        $product = new Product();

        $productTranslation1 = new ProductTranslation();
        $productTranslation1->setLocale('en');
        $productTranslation1->setName('Cheese');

        $productTranslation2 = new ProductTranslation();
        $productTranslation2->setLocale('de');
        $productTranslation2->setName('Käse');

        $product->addTranslation($productTranslation1);
        $product->addTranslation($productTranslation2);

        $entityManager->persist($product);
        $entityManager->persist($productTranslation1);
        $entityManager->persist($productTranslation2);

        $entityManager->flush();

        // Act
        $product->removeTranslation($productTranslation1);
        $entityManager->flush();

        // Assert
        $entityManager->clear();
        $product = $entityManager->find(Product::class, $product->getId());

        $this->assertCount(1, $product->getTranslations());
        $this->assertEquals('de', $product->getTranslations()->first()->getLocale());
    }

    public function testGetTranslation(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);
        $product = new Product();

        $productTranslation1 = new ProductTranslation();
        $productTranslation1->setLocale('en');
        $productTranslation1->setName('Cheese');

        $productTranslation2 = new ProductTranslation();
        $productTranslation2->setLocale('de');
        $productTranslation2->setName('Käse');

        $product->addTranslation($productTranslation1);
        $product->addTranslation($productTranslation2);

        $entityManager->persist($product);
        $entityManager->persist($productTranslation1);
        $entityManager->persist($productTranslation2);

        $entityManager->flush();

        // Act
        $actual = $product->getTranslation('en');

        // Assert
        $this->assertEquals('en', $actual->getLocale());
        $this->assertEquals('Cheese', $actual->getName());

        $entityManager->clear();
        $product = $entityManager->find(Product::class, $product->getId());

        // Act
        $actual = $product->getTranslation('en');

        // Assert
        $this->assertEquals('en', $actual->getLocale());
        $this->assertEquals('Cheese', $actual->getName());
    }
}
