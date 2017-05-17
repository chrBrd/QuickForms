<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Type;

use Symfony\Component\Form\AbstractType;

class QuickFormsType extends AbstractType implements QuickFormsTypeInterface
{
    /** @var  string  The class' namespace prefix. */
    protected $namespacePrefix;

    /**
     * QuickFormsType constructor.
     */
    public function __construct()
    {
        $this->namespacePrefix = $this->determineNamespacePrefix();
    }

    /**
     * Get the class' namespace prefix.
     *
     * @return  string  The class' namespace prefix.
     */
    public function getNamespacePrefix() : string
    {
        return $this->namespacePrefix;
    }

    /**
     * Set the class' namespace prefix.
     *
     * @param   string  $namespacePrefix    The namespace prefix to set.
     */
    public function setNamespacePrefix(string $namespacePrefix)
    {
        $this->namespacePrefix = $namespacePrefix;
    }

    /**
     * Determine the namespace prefix of this class based on its parent's FQCN.
     *
     * @return  string  The class' namespace prefix.
     */
    protected function determineNamespacePrefix() : string
    {
        $parentFqcn = $this->getParent();
        return preg_replace('/\\\[^\\\]*$/', '', $parentFqcn);
    }
}
