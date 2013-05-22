<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Serializer\Jms\Tests;

use EventBand\Serializer\Jms\EventContainer;
use EventBand\Serializer\Jms\EventContainerHandler;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class EventContainerHandlerTest
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class EventContainerHandlerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $namingStrategy;

    protected function setUp()
    {
        $this->namingStrategy = $this->getMock('EventBand\Serializer\Jms\ClassTypeNamingStrategy');

        $this->serializer = SerializerBuilder::create()
            ->configureHandlers(function (HandlerRegistryInterface $registry) {
                $registry->registerSubscribingHandler(new EventContainerHandler($this->namingStrategy));
            })
            ->build();
    }

    /**
     * @test serialized json contains class and serialized event
     */
    public function jsonSerialization()
    {
        $container = new EventContainer(new SerializableEventStub('event.name', 'some data'));

        $this->namingStrategy
            ->expects($this->once())
            ->method('classToType')
            ->with(get_class($container->getEvent()))
            ->will($this->returnValue('stub'))
        ;

        $serialized = $this->serializer->serialize($container, 'json');

        $this->assertEquals(
            json_encode([
                'type' => 'stub',
                'data' => [
                    'name' => $container->getEvent()->getName(),
                    'foo' => $container->getEvent()->getFoo()
                ]
            ]),
            $serialized
        );
    }

    /**
     * @test deserialize container data in proper event from json
     */
    public function jsonDeserialization()
    {
        $eventClass = __NAMESPACE__ . '\SerializableEventStub';
        $data = [
            'type' => 'stub',
            'data' => [
                'name' => 'event.name',
                'foo' => 'some data'
            ]
        ];

        $this->namingStrategy
            ->expects($this->once())
            ->method('typeToClass')
            ->with('stub')
            ->will($this->returnValue($eventClass))
        ;

        $containerClass = 'EventBand\Serializer\Jms\EventContainer';
        /** @var EventContainer $container */
        $container = $this->serializer->deserialize(json_encode($data), $containerClass, 'json');

        $this->assertInstanceOf($containerClass, $container);
        $this->assertInstanceOf($eventClass, $container->getEvent());
        $this->assertEquals($data['data']['name'], $container->getEvent()->getName());
        $this->assertEquals($data['data']['foo'], $container->getEvent()->getFoo());
    }

    /**
     * @test serialized xml has type attribute
     */
    public function xmlSerialization()
    {
        $container = new EventContainer(new SerializableEventStub('event.name', 'some data'));

        $this->namingStrategy
            ->expects($this->once())
            ->method('classToType')
            ->with(get_class($container->getEvent()))
            ->will($this->returnValue('stub'))
        ;

        $serialized = $this->serializer->serialize($container, 'xml');

        $this->assertEquals(file_get_contents(__DIR__. '/Fixtures/container.xml'), $serialized);
    }

    /**
     * @test deserialize container data in proper event from xml
     */
    public function xmlDeserialization()
    {
        $eventClass = __NAMESPACE__ . '\SerializableEventStub';
        $data = [
            'type' => $eventClass,
            'data' => [
                'name' => 'event.name',
                'foo' => 'some data'
            ]
        ];

        $this->namingStrategy
            ->expects($this->once())
            ->method('typeToClass')
            ->with('stub')
            ->will($this->returnValue($eventClass))
        ;

        $containerClass = 'EventBand\Serializer\Jms\EventContainer';
        /** @var EventContainer $container */
        $container = $this->serializer->deserialize(file_get_contents(__DIR__. '/Fixtures/container.xml'), $containerClass, 'xml');

        $this->assertInstanceOf($containerClass, $container);
        $this->assertInstanceOf($eventClass, $container->getEvent());
        $this->assertEquals($data['data']['name'], $container->getEvent()->getName());
        $this->assertEquals($data['data']['foo'], $container->getEvent()->getFoo());
    }
}
