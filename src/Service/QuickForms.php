<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Service;

use BinarySpanner\QuickForms\Entity\QuickFormsDataInterface;
use BinarySpanner\QuickForms\Type\QuickFormsTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class QuickForms
{
    /** @var  FormFactoryInterface  A FormFactory instance. */
    protected $formFactory;

    /** @var  string   An instance implementing QuickFormsExtension to use as the default data class. */
    protected $defaultDataClass;

    /** @var  string  An instance implementing QuickFormsTypeInterface
     *                  - used as a shell to get the field types' parent namespace. */
    protected $formType;

    /** @var  array  Form setup info from the form setup files. */
    protected $formSetupInfo;

    /**
     * QuickForms constructor.
     *
     * @param   array                   $formSetupInfo          Form setup info from the form setup files.
     * @param   QuickFormsDataInterface $defaultDataClass       An instance implementing QuickFormsExtension
     *                                                          to use as the default data class.
     * @param   QuickFormsTypeInterface $formType               An instance implementing QuickFormsTypeInterface
     *                                                          - used as a shell to get the field types' parent
     *                                                          namespace.
     * @param   FormFactoryInterface    $formFactory            A Symfony FormFactory instance.
     * @param   ContainerInterface      $container              A Symfony container instance.
     */
    public function __construct(
        array $formSetupInfo,
        QuickFormsDataInterface $defaultDataClass,
        QuickFormsTypeInterface $formType,
        FormFactoryInterface $formFactory,
        ContainerInterface $container
    ) {
        $this->container = $container;
        // todo Possibly move this in to the extension configuration and add to form setup info?
        $this->defaultDataClass = $defaultDataClass;
        // todo Inject this as a service or better instantiating per form?
        $this->formType = $formType;
        $this->formSetupInfo = $formSetupInfo;
        $this->formFactory = $formFactory;
    }

    /**
     * Load the forms for insertion into a template.
     *
     * @return  array   An array containing the form views.
     */
    public function loadForms() : array
    {
        $forms = [];

        foreach ($this->formSetupInfo as $formName => $formSettings) {
            $data = $this->loadDataClass($formSettings);

            $formTypeClassName = get_class($this->formType);

            $formType = new $formTypeClassName;
            $formBuilder = $this->formFactory->createNamedBuilder($formName, $formTypeClassName, $data);

            $this->buildFormFields($formSettings['fields'], $formType, $data, $formBuilder);

            $forms[$formName]['view'] = $formBuilder->getForm()->createView();
            $forms[$formName]['theme'] = $formSettings['theme'];
        }

        return $forms;
    }

    /**
     * Load an instance of the form's data class.
     *
     * @param   array   $formSettings   The settings for a form.
     * @return  QuickFormsDataInterface   An instantiated data class object.
     */
    protected function loadDataClass(array $formSettings) : QuickFormsDataInterface
    {
        if (isset($formSettings['data_class'])) {
            return new $formSettings['data_class'];
            // todo Check class exists and implement custom exception?
        }

        $className = get_class($this->defaultDataClass);

        return new $className;
    }

    /**
     * Build the individual form fields.
     *
     * @param   array                   $formFieldsInfo     Form fields info from form setup file.
     * @param   QuickFormsTypeInterface $formType           The form field type
     *                                                      - used as a shell to get the field types' parent namespace.
     * @param   QuickFormsDataInterface $data               The form data.
     * @param   FormBuilderInterface    $formBuilder        A form builder instance.
     */
    protected function buildFormFields(
        array $formFieldsInfo,
        QuickFormsTypeInterface $formType,
        QuickFormsDataInterface $data,
        FormBuilderInterface $formBuilder
    ) {
        foreach ($formFieldsInfo as $fieldName => $field) {
            array_key_exists('value', $field)
                ? $data->setFormData($fieldName, $field['value'])
                : $data->setFormData($fieldName, null)
            ;

            array_key_exists('options', $field)
                ? $options = $field['options']
                : $options = []
            ;

            $className =  ucfirst($field['type']) . 'Type';
            $type = $formType->getNamespacePrefix() . '\\' . $className;
            $formBuilder->add($fieldName, $type, $options);
        }
    }
}
