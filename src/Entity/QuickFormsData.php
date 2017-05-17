<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */
namespace BinarySpanner\QuickForms\Entity;

class QuickFormsData implements QuickFormsDataInterface
{
    /** @var  array  A form's data. */
    protected $formData = [];

    /**
     * Get a field's data.
     *
     * @param   string  $fieldName  The name of the field.
     * @return  mixed   The field's data.
     */
    public function __get(string $fieldName)
    {
        if (!array_key_exists($fieldName, $this->formData)) {
            return null;
        }

        return $this->formData[$fieldName];
    }

    /**
     * Return a form's data.
     *
     * @return  array   The form data.
     */
    public function getFormData() : array
    {
        return $this->formData;
    }

    /**
     * Set a form's data.
     *
     * @param   string  $fieldName  The name of the field.
     * @param   mixed   $value      The field's value.
     */
    public function setFormData(string $fieldName, $value)
    {
        $this->formData[$fieldName] = $value;
    }
}
