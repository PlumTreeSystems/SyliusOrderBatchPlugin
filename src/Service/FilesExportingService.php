<?php

namespace PTS\SyliusOrderBatchPlugin\Service;

use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;

class FilesExportingService
{
    /**
     * Files export service constructor.
     */
    public function __construct(
    ) {
    }

    public function addOrdersTableHeaderToCSV($file)
    {
        $headers = [
            'Number',
            'Full name',
            'Email',
            'Date',
            'State',
            'Payment state',
            'Shipping state',
            'Shipping address',
            'Total',
            'Currency',
            'Channel',
        ];

        fputcsv($file, $headers);
    }

    public function addOrderRowToCSV($file, Order $data)
    {
        $resultData = [];

        array_push($resultData, $data->getNumber());

        /** @var Customer $customer */
        $customer = $data->getCustomer();
        if (!is_null($customer)) {
            array_push($resultData, $customer->getFullName());
            array_push($resultData, $customer->getEmail());
        } else {
            array_push($resultData, "");
            array_push($resultData, "");
        }

        array_push($resultData, date_format($data->getCreatedAt(), 'Y/m/d H:i:s'));
        array_push($resultData, $data->getState());
        array_push($resultData, $data->getPaymentState());
        array_push($resultData, $data->getShippingState());

        $shippingAddress = $data->getShippingAddress();
        if (!is_null($shippingAddress)) {
            $address = $shippingAddress->getCity() . ', ' . $shippingAddress->getStreet() . ' ' . $shippingAddress->getPostcode();
            if (!is_null($shippingAddress->getProvinceName())){
                $address = $address . ' ' .$shippingAddress->getProvinceName() . '.';
            }

            $address = $address . ' ' . $shippingAddress->getCountryCode();


            array_push($resultData, $address);

        } else {
            array_push($resultData, "");
        }

        array_push($resultData, $data->getTotal());
        array_push($resultData, $data->getCurrencyCode());

        /** @var Channel $channel */
        $channel = $data->getChannel();
        if (!is_null($channel)) {
            array_push($resultData, $channel->getName());
        } else {
            array_push($resultData, "");
        }

        fputcsv($file, $resultData);
    }
}
