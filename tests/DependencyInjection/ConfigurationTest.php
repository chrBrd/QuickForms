<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */
namespace BinarySpanner\QuickForms\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use BinarySpanner\QuickForms\DependencyInjection\Configuration;

/**
 * Class ConfigurationTest
 * @package BinarySpanner\QuickForms\Tests
 * @coversDefaultClass BinarySpanner\QuickForms\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    /**
     * Test that the configuration tree for default values is correct.
     *
     * @covers ::__construct
     * @covers ::getConfigTreeBuilder
     * @covers ::addDirectoriesSection
     * @covers ::addFilesSection
     * @covers ::addFormClassPrefixSection
     */
    public function testDefaultsConfigurationTreeIsBuiltCorrectly()
    {
        $defaults = [
            'root_paths' => '/var/www',
            'directory_paths' => 'foo',
            'file_names' => 'file',
            'form_class_prefix' => 'prefix'
        ];

        $configuration = new Configuration(false, 'quick_forms', $defaults);
        $treeBuilder = $configuration->getConfigTreeBuilder();

        $rootNode = $this->getInvisibleProperties($treeBuilder, 'root');
        $defaultSettingsNodes = $this->getInvisibleProperties($rootNode, 'children');

        $configDefaults = [];
        foreach ($defaultSettingsNodes as $nodeName => $defaultSettingsNode) {
            $configDefaults[$nodeName] = $this->getInvisibleProperties($defaultSettingsNode, 'defaultValue');
        }

        $failMessage = 'Configuration tree for bundle setup is incorrect.';
        $this->assertSame($defaults, $configDefaults, $failMessage);
    }

    /**
     * Test that the configuration tree for form layouts is correct.
     *
     * @covers ::__construct
     * @covers ::getConfigTreeBuilder
     * @covers ::buildFormsTree
     */
    public function testFormLayoutConfigurationTreeIsBuiltCorrectly()
    {
        $configuration = new Configuration(false, 'testForm', []);
        $treeBuilder = $configuration->getConfigTreeBuilder();

        $rootProperty = $this->getInvisibleProperties($treeBuilder, 'root');
        $rootChildren = $this->getInvisibleProperties($rootProperty, 'children');

        $rootChildrenKeys = array_keys($rootChildren);
        $formKeys = ['id', 'theme', 'data_class', 'fields'];
        $failMessage = 'Configuration tree for form layouts is incorrect.';
        $this->assertSame($formKeys, $rootChildrenKeys, $failMessage);
    }

    /**
     * Test that ::setRootName sets the rootName property.
     *
     * @covers ::__construct
     * @covers ::setRootName
     */
    public function testSetRootName()
    {
        $configuration = new Configuration(false, '', []);
        $configuration->setRootName('root_name');

        $failMessage = '::setRootName fails to set the rootName attribute.';
        $this->assertAttributeEquals('root_name', 'rootName', $configuration, $failMessage);
    }

    /**
     * Get invisible object properties.
     * @param   object  $object     The object to search in.
     * @param   string  $property   The property to retrieve.
     * @return  mixed   The property's value.
     */
    private function getInvisibleProperties($object, $property)
    {
        $reflection = new \ReflectionObject($object);
        $rootProperty = $reflection->getProperty($property);
        $rootProperty->setAccessible(true);
        return $rootProperty->getValue($object);
    }
}
