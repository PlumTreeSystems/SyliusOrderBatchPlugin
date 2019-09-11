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
Copy the bundle's templates from `` to your project templates folder

## Create a batch action

Extend your controller you want your action to be executed in and add your method
```php
class OrderController extends Controller
{
    /* @param $id
    // The id of your batch 
    */
    public function yourAction($id)
    {
        //Implement your own logic here
    }
}
```

Bind your method to a route in your configuration file

```yaml
your_action_route:
  path: /admin/batch/your-action/{id}
  defaults:
    _controller: sylius.controller.order:yourAction

```

Register your action to your resources.yaml configuration file

```yaml
pts_sylius_order_batch:
  actions:
    your_action:
      label: 'Your action label'
      route: 'your_action_route'
  ```

  