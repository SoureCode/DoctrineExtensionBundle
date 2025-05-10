<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\EventListener;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractWebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class UserPropertyListenerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        self::createClient();

        $this->setUpDatabase([
            Article::class,
            User::class,
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        /**
         * @var KernelBrowser $client
         */
        $client = self::getClient();
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User();
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $article = new Article();
        $article->setTitle('Test Title');

        // Act
        $entityManager->persist($article);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($article->getCreatedBy());
        $this->assertNull($article->getUpdatedBy());
    }

    public function testUpdate(): void
    {
        // Arrange
        /**
         * @var KernelBrowser $client
         */
        $client = self::getClient();
        $container = self::getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User();
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $article = new Article();
        $article->setTitle('Test Title');

        $entityManager->persist($article);
        $entityManager->flush();

        $this->assertNotNull($article->getCreatedBy());
        $this->assertNull($article->getUpdatedBy());

        // reset and re-login
        $entityManager->clear(); // clear the entity manager to avoid caching issues
        $container->get('security.token_storage')->setToken(null); // clear the token storage
        $container->get('soure_code.doctrine_extension.value_provider.user')->reset(); // caches the user value for faster access
        $user = $entityManager->getRepository(User::class)->find($user->getId()); // Fetch the user again
        $client->loginUser($user); // Re-login the user

        // Act
        /**
         * @var Article $article
         */
        $article = $entityManager->getRepository(Article::class)->find($article->getId()); // Fetch the article again
        $article->setTitle('Updated Title');

        $entityManager->persist($article);
        $entityManager->flush();

        // Assert
        $this->assertSame($article->getCreatedBy(), $user, 'CreatedBy should be the same user');
        $this->assertNotNull($article->getUpdatedBy());
    }
}
