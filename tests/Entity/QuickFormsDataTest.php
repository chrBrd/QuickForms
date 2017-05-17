<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Tests\Entity;

use PHPUnit\Framework\TestCase;
use BinarySpanner\QuickForms\Entity\QuickFormsData;

/**
 * Class QuickFormsDataTest
 * @package tests\QuickForms\FormLoader
 * @coversDefaultClass \BinarySpanner\QuickForms\Entity\QuickFormsData
 */
class QuickFormsDataTest extends TestCase
{
    /**
     * Test that form data is set correctly.
     *
     * @covers ::setFormData
     */
    public function testFormDataIsSetCorrectly()
    {
        $dataClass = new QuickFormsData();
        $dataClass->setFormData('foo', 'bar');

        $failMessage = 'Form data is not set correctly.';
        $this->assertAttributeEquals(['foo' => 'bar'], 'formData', $dataClass, $failMessage);
    }

    /**
     * Test that form data is retrieved correctly.
     *
     * @covers ::getFormData
     * @covers ::setFormData
     */
    public function testGetFormData()
    {
        $dataClass = new QuickFormsData();
        $dataClass->setFormData('foo', 'bar');

        $formData = $dataClass->getFormData();
        $failMessage = 'Form data is not retrieved correctly.';
        $this->assertSame(['foo' => 'bar'], $formData, $failMessage);
    }

    /**
     * Test that __get retrieves a field's data correctly.
     *
     * @covers ::__get
     * @covers ::setFormData
     */
    public function testGetReturnsAFieldsData()
    {
        $dataClass = new QuickFormsData();
        $dataClass->setFormData('foo', 'bar');

        $result = $dataClass->__get('foo');
        $failMessage = '::__get does not return a field\'s data.';
        $this->assertSame('bar', $result, $failMessage);
    }

    /**
     * Test that __get returns null if no field data is set.
     *
     * @covers ::__get
     * @covers ::setFormData
     */
    public function testGetReturnsNullIfNoFieldDataIsSet()
    {
        $dataClass = new QuickFormsData();

        $result = $dataClass->__get('foo');
        $this->assertSame(null, $result);
    }
}
