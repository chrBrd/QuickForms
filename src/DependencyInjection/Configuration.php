<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /** @var  bool  Symfony debug state. */
    protected $debug;

    /** @var  string  The name of the root node. */
    protected $rootName;

    /** @var  array  Bundle's default configuration settings.  */
    protected $defaults;

    public function __construct($debug, string $rootName, array $defaults)
    {
        $this->debug = (bool) $debug;
        $this->rootName = $rootName;
        $this->defaults = $defaults;
    }

    /**
     * Set the name of the tree's root node.
     *
     * @param   string  $rootName   The name of the root node.
     */
    public function setRootName(string $rootName)
    {
        $this->rootName = $rootName;
    }

    /**
     * Return the configuration's TreeBuilder object.
     *
     * @return  TreeBuilder     The configuration's TreeBuilder object.
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->rootName);

        if ($this->rootName !== 'quick_forms') {
            $this->buildFormsTree($rootNode);
            return $treeBuilder;
        }

        $this->addDirectoriesSection($rootNode);
        $this->addFilesSection($rootNode);
        $this->addFormClassPrefixSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Build the configuration tree for form layouts.
     *
     * @param   ArrayNodeDefinition     $rootNode   The tree's root node.
     */
    protected function buildFormsTree(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('id')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                // TODO Default theme and sort out default data class.
                ->scalarNode('theme')->end()
                ->scalarNode('data_class')
                    ->defaultValue('%quick_forms.default_data_classname%')
                ->end()
                ->arrayNode('fields')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                    ->children()
                        ->scalarNode('type')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('value')->end()
                        ->arrayNode('options')
                        ->children()
                            ->scalarNode('label')->end()
                            ->scalarNode('required')->end()
                        ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add the directories section to the tree.
     *
     * @param   ArrayNodeDefinition     $rootNode   The tree's root node.
     */
    protected function addDirectoriesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('root_paths')
                    ->info('Type setup root directory paths.')
                    ->defaultValue($this->defaults['root_paths'])
                    ->prototype('scalar')
                    ->cannotBeEmpty()
                ->end()
                // Convert root_directories value into an array if it's a string.
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return array($value);
                    })
                    ->end()
                // Add default values if root_directories value is null.
                ->beforeNormalization()
                    ->ifNull()
                    ->then(function () {
                        return $this->defaults['root_paths'];
                    })
                    ->end()
                ->end()
                ->arrayNode('directory_paths')
                    ->info('Type setup file directory paths.')
                    ->defaultValue($this->defaults['directory_paths'])
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->cannotBeEmpty()
                ->end()
                // Convert directories value into an array if it's a string.
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return array($value);
                    })
                    ->end()
                // Add default values if directories value is null.
                ->beforeNormalization()
                    ->ifNull()
                    ->then(function () {
                        return $this->defaults['directory_paths'];
                    })
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add the file names section to the tree.
     *
     * @param   ArrayNodeDefinition     $rootNode   The tree's root node.
     */
    protected function addFilesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('file_names')
                    ->info('Type setup files.')
                    ->defaultValue($this->defaults['file_names'])
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->cannotBeEmpty()
                ->end()
                // Convert directories value into an array if it's a string.
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return array($value);
                    })
                    ->end()
                // Add default values if directories value is null.
                ->beforeNormalization()
                    ->ifNull()
                    ->then(function () {
                        return $this->defaults['file_names'];
                    })
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add the form class prefix section to the tree.
     *
     * @param   ArrayNodeDefinition     $rootNode   The tree's root node.
     */
    protected function addFormClassPrefixSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('form_class_prefix')
                    ->defaultValue($this->defaults['form_class_prefix'])
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
    }
}
