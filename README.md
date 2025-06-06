
# sourecode/doctrine-extension-bundle

[![Packagist Version](https://img.shields.io/packagist/v/sourecode/doctrine-extension-bundle.svg)](https://packagist.org/packages/sourecode/doctrine-extension-bundle)
[![Downloads](https://img.shields.io/packagist/dt/sourecode/doctrine-extension-bundle.svg)](https://packagist.org/packages/sourecode/doctrine-extension-bundle)
[![CI](https://github.com/SoureCode/DoctrineExtensionBundle/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/SoureCode/DoctrineExtensionBundle/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/SoureCode/DoctrineExtensionBundle/branch/master/graph/badge.svg?token=GBFBVXQYK4)](https://codecov.io/gh/SoureCode/DoctrineExtensionBundle)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSoureCode%2FDoctrineExtensionBundle%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/SoureCode/DoctrineExtensionBundle/master)

- [Documentation](./docs/index.md)
- [License](./LICENSE)

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
