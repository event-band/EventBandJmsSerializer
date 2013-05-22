<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace EventBand\Serializer\Jms;

/**
 * Description of ObjectClassNamingStrategy
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class ClassNamingStrategy implements ClassTypeNamingStrategy
{
    private $separator;

    public function __construct($separator = null)
    {
        $this->separator = $separator;
    }

    /**
     * {@inheritDoc}
     */
    public function classToType($class)
    {
        return $this->separator ? str_replace('\\', $this->separator, $class) : $class;
    }

    /**
     * {@inheritDoc}
     */
    public function typeToClass($type)
    {
        return $this->separator ? str_replace($this->separator, '\\', $type) : $type;
    }
}
