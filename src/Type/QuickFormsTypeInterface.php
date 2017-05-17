<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Type;

interface QuickFormsTypeInterface
{
    /**
     * Get the class' namespace prefix.
     *
     * @return  string  The class' namespace prefix.
     */
    public function getNamespacePrefix() : string;

    /**
     * Set the class' namespace prefix.
     *
     * @param   string  $namespacePrefix    The namespace prefix to set.
     */
    public function setNamespacePrefix(string $namespacePrefix);
}
