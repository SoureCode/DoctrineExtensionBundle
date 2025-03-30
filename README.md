
# sourecode/doctrine-extension-bundle

Enhances Doctrine with:

- **Timestampable**: Automatically manage creation/update timestamps. (`createdAt`, `updatedAt`)
- **Blameable**: Track the user responsible for changes. (`createdBy`, `updatedBy`)
- **UTC Date/Time**: Replaces all Doctrine datetime types with UTC-based types to ensure all date-related values are stored in UTC in the database.

>
> Why another implementation???
>

It provides clean, focused support for UTC date handling and entity auditing without bloated overhead or complex computation.
I focused on performance and simplicity, ensuring that the bundle is easy to use and understand.

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
composer require sourecode/doctrine-extension-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require sourecode/doctrine-extension-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \SoureCode\Bundle\DoctrineExtension\SoureCodeDoctrineExtensionBundle::class => ['all' => true],
];
```
