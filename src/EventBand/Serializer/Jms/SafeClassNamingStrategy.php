<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace EventBand\Serializer\Jms;

use EventBand\Utils\ClassUtils;

/**
 * Convert class name to safe name and vice versa
 * Replace \\ with nsSeparator, brake words with wordSeparator and convert to lower case
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class SafeClassNamingStrategy implements ClassTypeNamingStrategy
{
    private $nsSeparator;
    private $wordSeparator;

    public function __construct($nsSeparator = '.', $wordSeparator = '_')
    {
        $this->nsSeparator = $nsSeparator;
        $this->wordSeparator = $wordSeparator;
    }

    /**
     * {@inheritDoc}
     */
    public function classToType($class)
    {
        return ClassUtils::classToName($class, $this->nsSeparator, $this->wordSeparator);
    }

    /**
     * {@inheritDoc}
     */
    public function typeToClass($type)
    {
        return ClassUtils::nameToClass($type, $this->nsSeparator, $this->wordSeparator);
    }
}
