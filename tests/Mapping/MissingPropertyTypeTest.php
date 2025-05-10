<?php

namespace Mapping;

use App\Entity\MissingPropertyType;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Nyholm\BundleTest\TestKernel;
use SoureCode\Bundle\DoctrineExtension\SoureCodeDoctrineExtensionBundle;
use SoureCode\Bundle\Timezone\SoureCodeTimezoneBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\KernelInterface;

class MissingPropertyTypeTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->setTestProjectDir(__DIR__.'/../app_invalid');
        $kernel->addTestBundle(SecurityBundle::class);
        $kernel->addTestBundle(DoctrineBundle::class);
        $kernel->addTestBundle(SoureCodeTimezoneBundle::class);
        $kernel->addTestBundle(SoureCodeDoctrineExtensionBundle::class);
        $kernel->addTestConfig(__DIR__.'/../app/config/security.yaml');
        $kernel->addTestConfig(__DIR__.'/../app/config/soure_code_timezone.yaml');
        $kernel->addTestConfig(__DIR__.'/../app_invalid/config/services.yaml');
        $kernel->addTestConfig(__DIR__.'/../app_invalid/config/doctrine.yaml');
        $kernel->handleOptions($options);

        return $kernel;
    }

    protected function setUpDatabase(array $classNames): void
    {
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->createSchema(array_map(static function ($className) use ($entityManager) {
            return $entityManager->getClassMetadata($className);
        }, $classNames));
    }

    protected function tearDown(): void
    {
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->dropDatabase();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testGenerate(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Field without a type is not supported.');

        $this->setUpDatabase([
            MissingPropertyType::class,
        ]);
    }
}
