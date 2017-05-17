<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use BinarySpanner\QuickForms\Entity\QuickFormsData;
use BinarySpanner\QuickForms\Service\QuickForms;

/**
 * Class QuickFormsExtension
 * @package BinarySpanner\QuickForms\DependencyInjection
 */
class QuickFormsExtension extends Extension
{
    /** @var  array  Configuration defaults. */
    protected $defaults;

    /**
     * Load the extension into a temporary container instance before it gets merged with the main container.
     * @param   array               $configs        The bundle's config settings.
     * @param   ContainerBuilder    $container      A container builder instance.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadFormSetupInfo($config, $configuration, $container);

        // TODO This should use the classes from the config files.
        $this->addClassesToCompile([
            QuickFormsData::class,
            QuickForms::class
        ]);
    }

    /**
     * Load form setup info from form setup files and set it as a container parameter.
     *
     * @param   array               $config         The bundle's config settings.
     * @param   Configuration       $configuration  An instance of this bundle's Configuration class.
     * @param   ContainerBuilder    $container      A container builder instance.
     */
    protected function loadFormSetupInfo(array $config, Configuration $configuration, ContainerBuilder $container)
    {
        foreach ($config['root_paths'] as $index => $rootPath) {
            $config['root_paths'][$index] = $this->resolveBundleDirectory($rootPath);
        }

        // todo separate configuration class for forms? Probably a better idea...
        $pathComponents = [
            'rootPaths' =>  $config['root_paths'],
            'directoryPaths' => $config['directory_paths'],
            'fileNames' => $config['file_names']
        ];

        $pathBuilder = new PathBuilder(new Filesystem(), $pathComponents);

        $paths = $pathBuilder->buildPaths();

        $yaml = new Yaml();
        $forms = [];

        foreach ($paths as $path) {
            $forms = array_merge($forms, $yaml->parse(file_get_contents($path)));
            $container->addResource(new FileResource($path));
        }

        $forms = $this->validateForms($forms, $configuration);

        $container->setParameter($this->getAlias() . '.forms_setup_info', $forms);
    }

    /**
     * Validate the loaded forms using the Configuration class instance.
     *
     * @param   array               $forms          The forms to validate.
     * @param   Configuration       $configuration  An instance of this bundle's Configuration class.
     * @return  array   An array of the valid forms.
     */
    protected function validateForms(array $forms, Configuration $configuration) : array
    {
        $validForms = [];

        foreach ($forms as $name => $form) {
            $configuration->setRootName($name);
            $validForms[$name] = $this->processConfiguration($configuration, [$form]);
        }

        return $validForms;
    }

    /**
     * Get an instance of this bundle's Configuration class.
     *
     * @param   array               $configs        The bundle's config settings from files in the app/config directory.
     * @param   ContainerBuilder    $container      A container builder instance.
     * @return  Configuration       An instance of this bundle's Configuration class.
     */
    public function getConfiguration(array $configs, ContainerBuilder $container) : Configuration
    {
        if (!$this->defaults) {
            $this->defaults = $this->loadDefaultValues($container);
            if (!empty($configs) && !empty($this->defaults)) {
                //$this->defaults = array_merge($this->loadDefaultValues($container), $configs);
            }
        }

        return new Configuration($container->getParameter('kernel.debug'), 'quick_forms', $this->defaults);
    }

    /**
     * Load the default config values from a YAML file so they can be used in a Configuration instance.
     *
     * @param   ContainerBuilder    $container      A container builder instance.
     * @return  array   Array containing the bundle's default parameters.
     */
    protected function loadDefaultValues(ContainerBuilder $container) : array
    {
        if (!$container->hasParameter('quick_forms.config_defaults_path')) {
            return array();
        }

        $path = $container->getParameter('quick_forms.config_defaults_path');

        $path = $this->resolveBundleDirectory($path);

        $filesystem = new Filesystem();
        if (!$filesystem->exists($path)) {
            $msg = sprintf('Default config file not found at "%s"', $path);
            throw new \InvalidArgumentException(sprintf($msg, $path));
        }

        $yaml = new Yaml();
        return $container->getParameterBag()->resolveValue($yaml->parse(file_get_contents($path)));
    }

    /**
     * Resolve instances of '__BUNDLE_DIR__' in config files into the bundle directory's path.
     *
     * @param   string  $path   The path to resolve
     * @return  string  The resolved path.
     */
    protected function resolveBundleDirectory($path)
    {
        if (strpos($path, '__BUNDLE_DIR__') !== false) {
            $path = str_replace('__BUNDLE_DIR__', dirname(__DIR__), $path);
        }

        return $path;
    }
}
