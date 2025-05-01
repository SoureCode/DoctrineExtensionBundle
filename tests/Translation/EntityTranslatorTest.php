<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Translation;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;
use SoureCode\Bundle\DoctrineExtension\Translation\EntityTranslatorInterface;

class EntityTranslatorTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $this->setUpDatabase([
            Product::class,
            ProductTranslation::class,
        ]);
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
        $entityManager->clear();

        $product = $entityManager->getRepository(Product::class)->find($product->getId());
        self::assertNotNull($product);

        /**
         * @var EntityTranslatorInterface $entityTranslator
         */
        $entityTranslator = $container->get(EntityTranslatorInterface::class);

        // Act
        $translation = $entityTranslator->getTranslation($product, 'en');

        // Assert
        $unitOfWork = $entityManager->getUnitOfWork();
        $identityMap = $unitOfWork->getIdentityMap();

        self::assertNotEmpty($identityMap);
        self::assertCount(2, $identityMap);
        self::assertCount(1, $identityMap[Product::class]);
        self::assertCount(1, $identityMap[ProductTranslation::class], 'Identity map should contain one ProductTranslation entity');

        self::assertNotNull($translation);
        self::assertEquals('Cheese', $translation->getName());
        self::assertEquals('en', $translation->getLocale());

        $entityManager->clear();
        $product = $entityManager->getRepository(Product::class)->find($product->getId());

        // Act
        $translations = $product->getTranslations();

        // Assert
        self::assertCount(2, $translations);

        $identityMap = $unitOfWork->getIdentityMap();
        self::assertNotEmpty($identityMap);
        self::assertCount(2, $identityMap);
        self::assertCount(1, $identityMap[Product::class]);
        self::assertCount(2, $identityMap[ProductTranslation::class], 'Identity map should contain two ProductTranslation entities');
    }

    public function testGetTranslationValue()
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

        $product = $entityManager->getRepository(Product::class)->find($product->getId());
        self::assertNotNull($product);

        /**
         * @var EntityTranslatorInterface $entityTranslator
         */
        $entityTranslator = $container->get(EntityTranslatorInterface::class);

        // Act
        $actualEn = $entityTranslator->getTranslationValue($product,  'name', 'en');
        $actualDe = $entityTranslator->getTranslationValue($product,  'name', 'de');

        // Assert
        self::assertEquals('Cheese', $actualEn);
        self::assertEquals('Käse', $actualDe);
    }
}
