# Sylius Order Batch Plugin 

A plugin that lets you customize how you filter your orders and save them into batches.

## Installation

Run `composer require plumtreesystems/sylius-order-batch`

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

Copy the bundle's templates from `src/Resources/templates` to your project `templates/` folder

## Customize

If you'd like to add custom actions or custom types your batches, follow instructions in the following links

[Custom batch type](src/Resources/doc/custom-batch-type.md)
/ [Custom batch action](src/Resources/doc/custom-batch-action.md)
