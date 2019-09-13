## Create your custom batch action

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
      enabled: true    # optional
  ```

After this, your custom action should appear under operations dropdown