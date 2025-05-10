<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests;

class BundleInitializationTest extends AbstractKernelTestCase
{
    public function testInitBundle(): void
    {
        // Arrange
        self::bootKernel();

        $container = self::getContainer();

        // Act and Assert
        $this->assertTrue($container->has('soure_code.doctrine_extension.listener.property'));
    }
}
