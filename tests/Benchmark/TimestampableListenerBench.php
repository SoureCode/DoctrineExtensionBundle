<?php

namespace SoureCode\Bundle\DoctrineExtension\Tests\Benchmark;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Tests\AbstractKernelTestCase;
use PhpBench\Attributes as Bench;

class TimestampableListenerBench extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $_ENV['DATABASE_URL'] = "sqlite:///:memory:";

        self::bootKernel();

        $this->setUpDatabase([
            Post::class
        ]);

        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(Post::class);
    }

    public function prepare(): void
    {
        $this->setUp();
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(1)]
    #[Bench\BeforeMethods(['prepare'])]
    public function benchPersist(): void
    {
        $post = new Post();
        $post->setTitle('Test Title');
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        assert($post->getCreatedAt() !== null);
        assert($post->getUpdatedAt() === null);
        $this->entityManager->clear();
    }

    public function preloadData(): void
    {
        for ($i = 0; $i < 1000; $i++) {
            $post = new Post();
            $post->setTitle('Test Title');
            $this->entityManager->persist($post);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(1)]
    #[Bench\BeforeMethods(['prepare', 'preloadData'])]
    public function benchUpdate(): void
    {
        /**
         * @var Post $post
         */
        $post = $this->repository->findOneBy(['title' => 'Test Title']);
        assert($post !== null);
        assert($post->getId() !== null);
        $post->setTitle('Updated Title');
        $this->entityManager->flush();
        assert($post->getCreatedAt() !== null);
        assert($post->getUpdatedAt() !== null);
        $this->entityManager->clear();
    }
}