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