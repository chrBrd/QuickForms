<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Tests\Entity;

use PHPUnit\Framework\TestCase;
use BinarySpanner\QuickForms\Type\QuickFormsType;

/**
 * Class QuickFormsTypeTest
 * @package BinarySpanner\QuickForms\Tests\Entity
 * @coversDefaultClass BinarySpanner\QuickForms\Type\QuickFormsType
 */
class QuickFormsTypeTest extends TestCase
{
    /**
     * Test that the namespacePrefix attribute is set when a class instance is constructed.
     *
     * @covers ::__construct
     * @covers ::determineNamespacePrefix
     */
    public function testNamespacePrefixIsSetWhenAClassInstanceIsConstructed()
    {
        $type = new QuickFormsType();
        $failMessage = 'NamespacePrefix has not been set during QuickFormsType construction.';
        $this->assertObjectHasAttribute('namespacePrefix', $type, $failMessage);
    }

    /**
     * Test the namespacePrefix attribute is set properly.
     *
     * @covers ::__construct
     * @covers ::determineNamespacePrefix
     * @covers ::setNamespacePrefix
     */
    public function testSetNamespacePrefix()
    {
        $type = new QuickFormsType();
        $type->setNamespacePrefix('foo');

        $failMessage = 'Failed to set namespacePrefix attribute in QuickFormsType instance.';
        $this->assertAttributeEquals('foo', 'namespacePrefix', $type, $failMessage);
    }

    /**
     * Test the namespacePrefix attribute is retrieved properly.
     *
     * @covers ::__construct
     * @covers ::determineNamespacePrefix
     * @covers ::setNamespacePrefix
     * @covers ::getNamespacePrefix
     */
    public function testGetNamespacePrefix()
    {
        $type = new QuickFormsType();
        $type->setNamespacePrefix('foo');

        $result = $type->getNamespacePrefix();
        $failMessage = 'Failed to get namespacePrefix from QuickFormsType instance.';
        $this->assertSame('foo', $result, $failMessage);
    }
}
