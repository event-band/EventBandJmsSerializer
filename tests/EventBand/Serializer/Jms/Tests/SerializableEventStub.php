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
    private $data;

    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }
}