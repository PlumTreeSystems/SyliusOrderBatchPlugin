<?php

namespace PTS\SyliusOrderBatchPlugin\Service;

use PTS\SyliusOrderBatchPlugin\Fixtures\Factory\OrderFactory;
use Sylius\Component\Core\Model\Order;

class DocumentsDataProvider
{
    private $orderFactory;

    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    public function getTemplate($template, $projectDir) {
        $url = $projectDir . $template['template'];
        $code = file_get_contents($url);

        $htmlBlocks = explode('</style>', $code);
        $html = $htmlBlocks[1];

        $styleBlocks = explode('<style>', $htmlBlocks[0]);
        $style = $styleBlocks[1];

        $data = $this->generateTemplateData($template);
        return [
            'style' => $style,
            'content' => $html,
            'data' => $data
        ];
    }

    public function generateTemplateData($template) {
        $data = json_encode($template['exampleData'], true);
        return $data;
    }
    public function getTestOrder() {
        return $this->orderFactory->create();
    }
}