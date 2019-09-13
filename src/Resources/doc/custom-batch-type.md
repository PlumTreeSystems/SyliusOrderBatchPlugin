## Create your custom batch type

Create a grid for your batch type and register it to your config file, customize it any way you want. More in-depth documentation on grids can be found [here](https://docs.sylius.com/en/1.5/components_and_bundles/bundles/SyliusGridBundle/)

```yaml
sylius_grid:
  grids:
    your_grid_name_show:
      driver:
        name: doctrine/orm
        options:
          class: "%sylius.model.order.class%"
          repository:
            method: createListQueryBuilder
      sorting:
        number: desc
      fields:
        channel:
          type: twig
          label: sylius.ui.channel
          sortable: channel.code
          options:
            template: "@SyliusAdmin/Order/Grid/Field/channel.html.twig"
        number:
          type: twig
          label: sylius.ui.number
          path: .
          sortable: ~
          options:
            template: "@SyliusAdmin/Order/Grid/Field/number.html.twig"
        date:
          type: datetime
          label: sylius.ui.date
          path: checkoutCompletedAt
          sortable: checkoutCompletedAt
          options:
            format: d-m-Y H:i:s
        customer:
          type: twig
          label: sylius.ui.customer
          sortable: customer.lastName
          options:
            template: "@SyliusAdmin/Order/Grid/Field/customer.html.twig"
        state:
          type: twig
          label: sylius.ui.state
          sortable: ~
          options:
            template: "@SyliusUi/Grid/Field/state.html.twig"
            vars:
              labels: "@SyliusAdmin/Order/Label/State"
        paymentState:
          type: twig
          label: sylius.ui.payment_state
          sortable: ~
          options:
            template: "@SyliusUi/Grid/Field/state.html.twig"
            vars:
              labels: "@SyliusAdmin/Order/Label/PaymentState"
        shippingState:
          type: twig
          label: sylius.ui.shipping_state
          sortable: ~
          options:
            template: "@SyliusUi/Grid/Field/state.html.twig"
            vars:
              labels: "@SyliusAdmin/Order/Label/ShippingState"
        total:
          type: twig
          label: sylius.ui.total
          path: .
          sortable: total
          options:
            template: "@SyliusAdmin/Order/Grid/Field/total.html.twig"
        currencyCode:
          type: string
          label: sylius.ui.currency
          sortable: ~
      filters:
        number:
          type: string
          label: sylius.ui.number
        customer:
          type: string
          label: sylius.ui.customer
          options:
            fields: [customer.email, customer.firstName, customer.lastName]
        date:
          type: date
          label: sylius.ui.date
          options:
            field: checkoutCompletedAt
            inclusive_to: true
        channel:
          type: entity
          label: sylius.ui.channel
          form_options:
            class: "%sylius.model.channel.class%"
        total:
          type: money
          label: sylius.ui.total
          options:
            currency_field: currencyCode
        state:
          type: order_state
        payment_state:
          type: order_payment_state
        shipping_state:
          type: order_shipping_state
        shipping_country:
          type: order_shipping_country
        batch:
          type: exists_in_array
      actions:
        item:
          show:
            type: show
          removeOrderFromBatch:
            type: removeOrderFromBatch
    your_grid_name_index:
          driver:
            name: doctrine/orm
            options:
              class: PTS\SyliusOrderBatchPlugin\Entity\Batch
              repository:
                method: createShippingBatchListQueryBuilder
          sorting:
            name: desc
          fields:
            name:
              type: string
              label: app.filters.filterName
              sortable: name
          filters:
            name:
              type: custom_string_filter
          actions:
            item:
              show:
                type: show
      
```
Bind your type name to your grid name in parameters config
```yaml
parameters:
  grids:
    your_grid_name:
      name: 'your_type_name'
```

Create two routes to display all your batches and display a single one
```yaml
your_type_batch_show:
  path: /admin/shippingBatch/{id}
  methods: [GET, POST, PUT]
  defaults:
    _controller: sylius.controller.order:batchAction
    _sylius:
      template: your_template.html.twig # You can use this package's default batch display template as a skeleton
      section: admin
      grid: your_grid_name_show
      permission: true
      type: sylius.resource

your_type_batch_index:
  resource: |
    except: ['show']
    alias: app.yourTypeBatch
    path: /admin/your-path
    templates: Admin/BatchShipping/Crud
    permission: true
    grid: your_grid_name_index
    vars:
        index:
            subheader: app.batch.subheader
            icon: archive
  type: sylius.resource
```

To add a menu link to your new type batches route, add this to your AdminMenuListener
```php
/**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $subMenu = $menu->getChild('batch');

        $subMenu
            ->addChild('yourTypeBatch', ['route' => 'your_type_batch_index'])
            ->setLabel('app.batchType.yourType')
            ->setLabelAttribute('icon', 'archive')
        ;

    }
```