<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */
namespace BinarySpanner\QuickForms\Entity;

interface QuickFormsDataInterface
{
    /**
     * Get a field's data.
     *
     * @param   string  $fieldName  The name of the field.
     * @return  mixed   The field's data.
     */
    public function __get(string $fieldName);

    /**
     * Return a form's data.
     *
     * @return  array   The form data.
     */
    public function getFormData() : array;

    /**
     * Set a form's data.
     *
     * @param   string  $fieldName  The name of the field.
     * @param   mixed   $value      The field's value.
     */
    public function setFormData(string $fieldName, $value);
}
