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
Copy the bundle's templates from `src/Resources/templates` to your project `templates/` folder

## Customize

If you'd like to add custom actions or custom types your batches, follow instructions in the following links

[Custom batch type](src/Resources/doc/custom-batch-type.md)
/ [Custom batch action](src/Resources/doc/custom-batch-action.md)
