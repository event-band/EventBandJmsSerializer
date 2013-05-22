<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Serializer\Jms\Tests;

use EventBand\Serializer\Jms\EventContainer;
use EventBand\Serializer\Jms\JmsEventContainerSerializer;
use EventBand\Serializer\SerializerException;
use EventBand\Serializer\UnexpectedResultException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class JmsEventContainerSerializerTest
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class JmsEventContainerSerializerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;
    /**
     * @var JmsEventContainerSerializer
     */
    private $eventSerializer;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->serializer = $this->getMock('Jms\Serializer\SerializerInterface');
        $this->eventSerializer = new JmsEventContainerSerializer($this->serializer, 'format');
    }

    /**
     * @test event is put inside container and than serialized
     */
    public function containerSerialization()
    {
        $event = $this->getMock('EventBand\Event');
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->callback(function (EventContainer $container) use ($event) {
                    $this->assertSame($event, $container->getEvent());

                    return true;
                }),
                'format'
            )
            ->will($this->returnValue('serialized'))
        ;


        $this->assertEquals('serialized', $this->eventSerializer->serializeEvent($event));
    }

    /**
     * @test deserialize event from serialized container
     */
    public function containerDeserialization()
    {
        $event = $this->getMock('EventBand\Event');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('serialized', 'EventBand\Serializer\Jms\EventContainer', 'format')
            ->will($this->returnValue(new EventContainer($event)))
        ;

        $this->assertSame($event, $this->eventSerializer->deserializeEvent('serialized'));
    }

    /**
     * @test if serializer throws an exception on serialize it is wrapped in a proper exception
     */
    public function serializeException()
    {
        $event = $this->getMock('EventBand\Event');
        $exception = new \Exception('Error');

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->throwException($exception))
        ;

        try {
            $this->eventSerializer->serializeEvent($event);
        } catch (SerializerException $e) {
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test if serializer throws an exception on deserialize it is wrapped in a proper exception
     */
    public function deserializeException()
    {
        $exception = new \Exception('Error');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException($exception))
        ;

        try {
            $this->eventSerializer->deserializeEvent('data');
        } catch (SerializerException $e) {
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test An exception is thrown if deserialized object is not a container
     */
    public function unexpectedResult()
    {
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->returnValue('foo'))
        ;

        try {
            $this->eventSerializer->deserializeEvent('data');
        } catch (UnexpectedResultException $e) {
            $this->assertEquals($e->getResult(), 'foo');

            return;
        }

        $this->fail('Exception was not thrown');
    }
}
