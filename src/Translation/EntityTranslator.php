<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataFactory;
use Symfony\Component\HttpFoundation\RequestStack;

final class EntityTranslator
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClassMetadataFactory $classMetadataFactory,
        private readonly string $defaultLocale,
    ) {
    }

    public function getTranslationValue(object $translatable, string $fieldName, ?string $locale = null): mixed
    {
        $locale = $locale ?? $this->requestStack->getCurrentRequest()?->getLocale() ?? $this->defaultLocale;
        $translation = $this->getTranslation($translatable, $locale);

        if (!$translation) {
            return null;
        }

        $classMetadata = $this->classMetadataFactory->create($translatable::class);
        $translationClassName = $classMetadata->translationClassName;

        if (!$translationClassName) {
            return null;
        }

        $translationClassMetadata = $this->entityManager->getClassMetadata($translationClassName);

        return $translationClassMetadata->getFieldValue($translation, $fieldName);
    }

    public function getTranslation(object $translatable, ?string $locale = null): ?TranslationInterface
    {
        $classMetadata = $this->classMetadataFactory->create($translatable::class);
        $translationClassName = $classMetadata->translationClassName;

        if (!$translationClassName) {
            return null;
        }

        /**
         * @var EntityRepository<TranslationInterface> $translationRepository
         */
        $translationRepository = $this->entityManager->getRepository($translationClassName);

        $locale = $locale ?? $this->requestStack->getCurrentRequest()?->getLocale() ?? $this->defaultLocale;

        return $translationRepository->findOneBy([
            'translatable' => $translatable,
            'locale' => $locale,
        ]);
    }
}
