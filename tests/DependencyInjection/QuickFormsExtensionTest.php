<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Yaml;
use BinarySpanner\QuickForms\DependencyInjection\Configuration;
use BinarySpanner\QuickForms\DependencyInjection\QuickFormsExtension;

/**
 * Class QuickFormsExtensionTest
 * @package BinarySpanner\QuickForms\Tests
 * @coversDefaultClass BinarySpanner\QuickForms\DependencyInjection\QuickFormsExtension
 */
class QuickFormsExtensionTest extends TestCase
{
    /** @var  Yaml  An instance of the Symfony Yaml component. */
    private $yaml;
    
    /** @var  string  Path to the default config values YAML file. */
    private $defaultsPath;

    /** @var  string  Path to a form setup info file to test with. */
    private $pathToTestFormSetupInfoFile;

    public function setUp()
    {
        $this->yaml = new Yaml();
        $this->defaultsPath = __DIR__ . '/Fixtures/defaults/defaults.yml';
        $this->pathToTestFormSetupInfoFile = __DIR__ . '/Fixtures/forms/validForm.yml';

        parent::setUp();
    }

    /**
     * Test that form setup info files are correctly loaded and set as container parameters.
     *
     * @covers ::load
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     * @covers ::loadFormSetupInfo
     * @covers ::validateForms
     *
     * @uses \BinarySpanner\QuickForms\DependencyInjection\Configuration
     * @uses \BinarySpanner\QuickForms\DependencyInjection\PathBuilder
     */
    public function testFormSetupInfoIsCorrectlySetAsAContainerParameter()
    {
        $container = $this->buildContainer();
        $this->compileContainer($container);

        $extensionSetupInfo = $container->getParameter('quick_forms.forms_setup_info');

        $testFormSetupInfo = $this->yaml->parse(file_get_contents($this->pathToTestFormSetupInfoFile));
        $failMessage = 'Form setup info has not been configured as expected by the extension.';
        $this->assertSame($testFormSetupInfo, $extensionSetupInfo, $failMessage);
    }

    /**
     * Test that ::getConfiguration returns an instance of the Configuration class.
     *
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     *
     * @uses \BinarySpanner\QuickForms\DependencyInjection\Configuration
     */
    public function testGetConfigurationReturnsInstanceOfConfigurationClass()
    {
        $container = $this->buildContainer();

        $extension = new QuickFormsExtension();
        $configuration = $extension->getConfiguration(array(), $container);

        $failMessage = '::getConfiguration does not return an instance of the Configuration class.';
        $this->assertInstanceOf(Configuration::class, $configuration, $failMessage);
    }

    /**
     * Test that ::getConfiguration returns an instance containing the correct default values.
     *
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     *
     * @uses \BinarySpanner\QuickForms\DependencyInjection\Configuration
     */
    public function testGetConfigurationReturnsInstanceContainingCorrectDefaultValues()
    {
        $container = $this->buildContainer();

        $extension = new QuickFormsExtension();
        $configuration = $extension->getConfiguration(array(), $container);

        $parsedConfigValues = $this->yaml->parse(file_get_contents($this->defaultsPath));
        $parsedConfigValues = $container->getParameterBag()->resolveValue($parsedConfigValues);
        $failMessage = '::getConfiguration does not return an instance containing the correct default values.';
        $this->assertAttributeEquals($parsedConfigValues, 'defaults', $configuration, $failMessage);
    }

    /**
     * Test that ::getConfiguration returns an instance containing the correct root name.
     *
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     *
     * @uses \BinarySpanner\QuickForms\DependencyInjection\Configuration
     */
    public function testGetConfigurationReturnsInstanceContainingCorrectRootName()
    {
        $container = $this->buildContainer();

        $extension = new QuickFormsExtension();
        $configuration = $extension->getConfiguration(array(), $container);

        $failMessage = '::getConfiguration does not return an instance containing the correct root name.';
        $this->assertAttributeEquals('quick_forms', 'rootName', $configuration, $failMessage);
    }

    /**
     * Test that ::getConfiguration returns an instance when the default paths parameter is unset.
     *
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     *
     * @uses \BinarySpanner\QuickForms\DependencyInjection\Configuration
     */
    public function testGetConfigurationReturnsAConfigurationInstanceWhenDefaultsPathParameterIsUnset()
    {
        $container = $this->buildContainer();
        $container->getParameterBag()->remove('quick_forms.config_defaults_path');

        $extension = new QuickFormsExtension();
        $configuration = $extension->getConfiguration(array(), $container);

        $failMessage = '::getConfiguration does not return a Configuration instance when the default paths
        parameter is unset.';
        $this->assertAttributeEquals('quick_forms', 'rootName', $configuration, $failMessage);
    }

    /**
     * Test that ::getConfiguration renames paths prepended with '__BUNDLE_DIR__'.
     *
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     */
    public function testGetConfigurationRenamesPathsPrependedWithBundleDir()
    {
        $container = $this->buildContainer();
        $container->setParameter('quick_forms.config_defaults_path', '__BUNDLE_DIR__/foo');

        $bundleDir = dirname(dirname(__DIR__));

        $message = $bundleDir .'/src/foo';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $extension = new QuickFormsExtension();
        $extension->getConfiguration(array(), $container);
    }

    /**
     * Test an exception is thrown if the form setup info file contains an invalid key.
     *
     * @covers ::load
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     * @covers ::loadFormSetupInfo
     * @covers ::validateForms
     *
     * @uses \BinarySpanner\QuickForms\DependencyInjection\Configuration
     * @uses \BinarySpanner\QuickForms\DependencyInjection\PathBuilder
     */
    public function testExceptionIsThrownIfFormSetupInfoHasInvalidKey()
    {
        $invalidDefaultsPath = __DIR__ . '/Fixtures/defaults/invalidDefaults.yml';

        $container = $this->buildContainer();
        $container->setParameter('quick_forms.config_defaults_path', $invalidDefaultsPath);

        $this->expectException(InvalidConfigurationException::class);
        $this->compileContainer($container);
    }

    /**
     * Ensure ::getConfiguration throws an exception if the defaults file is not found.
     *
     * @covers ::getConfiguration
     * @covers ::loadDefaultValues
     * @covers ::resolveBundleDirectory
     */
    public function testGetConfigurationThrowsAnExceptionIfTheConfigDefaultsFileDoesNotExist()
    {
        $pathToDefaultsFile = 'does/not/exist';

        $exceptionMessage = sprintf('Default config file not found at "%s"', $pathToDefaultsFile);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $container = $this->buildContainer($pathToDefaultsFile);
        $this->compileContainer($container);
    }

    /**
     * Create a ContainerBuilder instance for use in testing.
     *
     * @param   string  $defaultsPath  Path to the default config values YAML file.
     * @return  ContainerBuilder    A container builder instance.
     */
    private function buildContainer(string $defaultsPath = null)
    {
        if (!$defaultsPath) {
            $defaultsPath = $this->defaultsPath;
        }

        $container = new ContainerBuilder(new ParameterBag([
                'kernel.root_dir' => __DIR__ . '/Fixtures',
                'kernel.debug' => false
        ]));

        $container->registerExtension(new QuickFormsExtension());

        $locator = new FileLocator(__DIR__ . '/Fixtures');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yml');

        $container->setParameter('quick_forms.config_defaults_path', $defaultsPath);
        $container->loadFromExtension('quick_forms', []);

        return $container;
    }

    /**
     * Compile the ContainerBuilder instance.
     *
     * @param   ContainerBuilder    $container      A container builder instance.
     */
    private function compileContainer(ContainerBuilder $container)
    {
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();
    }
}
