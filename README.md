# Sylius Order Batch Plugin [![Build Status](https://travis-ci.org/PlumTreeSystems/SyliusOrderBatchPlugin.svg?branch=master)](https://travis-ci.org/PlumTreeSystems/SyliusOrderBatchPlugin)

A plugin that lets you customize how you filter your orders and save them into batches.

## Installation

Install the package with this command: `composer require plumtreesystems/sylius-order-batch`

Add plugin dependencies to your bundles.php file:

```php
return [
    PTS\SyliusOrderBatchPlugin\PTSSyliusOrderBatchPlugin::class => ['all' => true],
];
```

Import the bundle's configuration to your `_sylius.yaml` file

```yaml
imports:
    - { resource: "@PTSSyliusOrderBatchPlugin/Resources/config/config.yml" }
```

Import the bundle's routes to your `routes.yaml` file

```yaml
pts_sylius_order_batch_plugin:
  resource: "@PTSSyliusOrderBatchPlugin/Resources/config/routing.yml"
```

Execute migrations `php bin/console doctrine:migrations:migrate`

Copy the bundle's templates from `src/Resources/templates` to your project `templates/` folder

Install the assets of the bundle by executing this command: `php bin/console assets:install public`

## Customize

If you'd like to add custom actions or custom types your batches, follow instructions in the following links

[Custom batch type](src/Resources/doc/custom-batch-type.md)
/ [Custom batch action](src/Resources/doc/custom-batch-action.md)
