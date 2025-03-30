<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\EventListener;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractWebTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

class BlameableListenerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        self::createClient();

        $this->setUpDatabase([
            Article::class
        ]);
    }

    public function testPersist(): void
    {
        // Arrange
        $client = self::getClient();
        $client->loginUser(new InMemoryUser('test', 'test'));
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

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
        $client = self::getClient();
        $client->loginUser(new InMemoryUser('test', 'test'));
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $article = new Article();
        $article->setTitle('Test Title');

        $entityManager->persist($article);
        $entityManager->flush();

        $this->assertNotNull($article->getCreatedBy());
        $this->assertNull($article->getUpdatedBy());

        $this->resetKernel();

        // re-login as reset kernel clears the security token
        $client->loginUser(new InMemoryUser('test', 'test'));

        // Act
        /**
         * @var Article $article
         */
        $article = $entityManager->getRepository(Article::class)->find($article->getId()); // Fetch the article again
        $article->setTitle('Updated Title');

        $entityManager->persist($article);
        $entityManager->flush();

        // Assert
        $this->assertNotNull($article->getCreatedBy());
        $this->assertNotNull($article->getUpdatedBy());
    }
}