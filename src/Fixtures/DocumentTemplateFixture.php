<?php

namespace PTS\SyliusOrderBatchPlugin\Fixtures;

use PTS\SyliusOrderBatchPlugin\Fixtures\Factory\DocumentTemplateFactory;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class DocumentTemplateFixture implements FixtureInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $documentTemplateManager;

    /**
     * @var DocumentTemplateFactory
     */
    private $documentTemplateFactory;

    /**
     * DocumentTemplateFixture constructor.
     * @param $documentTemplateManager
     * @param DocumentTemplateFactory $documentTemplateFactory
     */
    public function __construct($documentTemplateManager, DocumentTemplateFactory $documentTemplateFactory)
    {
        $this->documentTemplateManager = $documentTemplateManager;
        $this->documentTemplateFactory = $documentTemplateFactory;
    }


    /**
     * @param array $options
     */
    public function load(array $options): void
    {

        foreach($options['templates'] as $template) {
            $actualTemplate = $this->documentTemplateFactory->create($template);
            $this->documentTemplateManager->persist($actualTemplate);
        }

        $this->documentTemplateManager->flush();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'simply_document_template';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->arrayNode('templates')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('code')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('title')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('locale')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $optionNode = $treeBuilder->root($this->getName());

        $this->configureOptionsNode($optionNode);

        return $treeBuilder;
    }
}
