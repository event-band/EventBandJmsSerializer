<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Serializer\Jms\Tests;

use EventBand\Event;
use JMS\Serializer\Annotation\Type;

/**
 * Class SerializableEventStub
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class SerializableEventStub implements Event
{
    /**
     * @Type("string")
     */
    private $name;
    /**
     * @Type("string")
     */
    private $foo;

    public function __construct($name, $foo)
    {
        $this->name = $name;
        $this->foo = $foo;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}