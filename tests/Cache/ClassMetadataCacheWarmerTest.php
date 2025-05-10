<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Cache;

use SoureCode\Bundle\DoctrineExtension\Cache\ClassMetadataCacheWarmer;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;

class ClassMetadataCacheWarmerTest extends AbstractKernelTestCase
{
    public function testWarmUp(): void
    {
        // Arrange
        $container = self::getContainer();
        /**
         * @var ClassMetadataCacheWarmer $cacheWarmer
         */
        $cacheWarmer = $container->get('soure_code.doctrine_extension.class_metadata.cache_warmer');

        // Act
        $cacheDir = sys_get_temp_dir();
        $buildDir = null;
        $result = $cacheWarmer->warmUp($cacheDir, $buildDir);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
