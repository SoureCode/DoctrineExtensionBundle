<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class EntityTranslator
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationMapping $translationMapping,
        private readonly string $defaultLocale,
    ) {
    }

    public function getTranslationValue(TranslatableInterface $translatable, string $fieldName, ?string $locale = null): mixed
    {
        $locale = $locale ?? $this->requestStack->getCurrentRequest()?->getLocale() ?? $this->defaultLocale;

        $translation = $this->getTranslation($translatable, $locale);

        if (!$translation) {
            return null;
        }

        $translationClassName = $this->translationMapping->getTranslationClass($translatable::class);
        $translationClassMetadata = $this->entityManager->getClassMetadata($translationClassName);

        return $translationClassMetadata->getFieldValue($translation, $fieldName);
    }

    public function getTranslation(TranslatableInterface $translatable, ?string $locale = null): ?TranslationInterface
    {
        $translationClassName = $this->translationMapping->getTranslationClass($translatable::class);

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
