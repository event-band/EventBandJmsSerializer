<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace EventBand\Serializer\Jms;

use JMS\Serializer\Exception\RuntimeException;

/**
 * Description of ObjectTypeMapNamingStrategy
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class ClassMapNamingStrategy implements ClassTypeNamingStrategy
{
    private $map = array();

    public function __construct(array $map = array())
    {
        $this->map = $map;
    }

    /**
     * {@inheritDoc}
     */
    public function classToType($class)
    {
        if (isset($this->map[$class])) {
            return $this->map[$class];
        }

        throw new RuntimeException(sprintf('Type is not mapped for class "%s"', $class));
    }

    /**
     * {@inheritDoc}
     */
    public function typeToClass($type)
    {
        if ($class = array_search($type, $this->map)) {
            return $class;
        }

        throw new RuntimeException(sprintf('Class is not mapped for type "%s"', $type));
    }
}
