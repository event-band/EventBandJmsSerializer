<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Serializer\Jms;

use EventBand\Event;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GenericDeserializationVisitor;
use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

/**
 * Class EventContainerHandler
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class EventContainerHandler implements SubscribingHandlerInterface
{
    private $namingStrategy;

    public function __construct(ClassTypeNamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        $formats = array('json', 'xml');
        $type = __NAMESPACE__ . '\EventContainer';

        $methods = array();
        foreach ($formats as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => $type,
                'method' => 'serializeContainer' . ucfirst($format),
                'format' => $format
            );
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => $type,
                'method' => 'deserializeContainer' . ucfirst($format),
                'format' => $format
            );
        }

        return $methods;
    }

    /**
     * Normalize event container to serialize with json
     * Event class name is added
     *
     * @param JsonSerializationVisitor $visitor
     * @param EventContainer           $container
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array
     */
    public function serializeContainerJson(JsonSerializationVisitor $visitor, EventContainer $container, array $type, Context $context)
    {
        return $this->serializeContainer($visitor, $container, $context);
    }

    /**
     * Create container from decoded json
     *
     * @param JsonDeserializationVisitor $visitor
     * @param mixed                      $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return EventContainer
     * @throws RuntimeException
     */
    public function deserializeContainerJson(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!is_array($data)) {
            throw new RuntimeException(sprintf('Deserialize data array expected, got "%s"', gettype($data)));
        }

        return $this->deserializeContainer($visitor, $data, $context);
    }

    /**
     * Normalize wrapper to serialize with xml
     * Event class name is added
     *
     * @param XmlSerializationVisitor $visitor
     * @param EventContainer          $container
     * @param array                   $type
     * @param Context                 $context
     */
    public function serializeContainerXml(XmlSerializationVisitor $visitor, EventContainer $container, array $type, Context $context)
    {
        if (!$visitor->getDocument()) {
            /** @var ClassMetadata $metadata */
            $metadata = $context->getMetadataFactory()->getMetadataForClass(__NAMESPACE__ . '\EventContainer');
            $metadata->xmlRootName = 'event';
            $visitor->startVisitingObject($metadata, $container, [], $context);
//            $visitor->visitArray([], ['name' => 'array'], $context);
        }

        $event = $container->getEvent();
        $node = $visitor->getCurrentNode();

        $node->setAttribute('type', $this->namingStrategy->classToType(get_class($event)));
        $data = $visitor->getDocument()->createElement('data');

        $visitor->getCurrentNode()->appendChild($data);
        $visitor->setCurrentNode($data);
        $visitor->getNavigator()->accept($event, null, $context);
        $visitor->revertCurrentNode();
    }

    /**
     * Create wrapper from decoded xml
     *
     * @param XmlDeserializationVisitor $visitor
     * @param mixed                     $data
     * @param array                     $type
     * @param Context                   $context
     *
     * @return EventContainer
     * @throws RuntimeException
     */
    public function deserializeContainerXml(XmlDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!is_object($data)) {
            throw new RuntimeException(sprintf('Deserialized SimpleXMLElement data expected, got "%s"', gettype($data)));
        }
        if (!$data instanceof \SimpleXMLElement) {
            throw new RuntimeException(sprintf('Deserialized SimpleXMLElement data expected, got "%s"', get_class($data)));
        }
        if (!isset($data->data) || !$data->data instanceof \SimpleXMLElement) {
            throw new RuntimeException(sprintf('Deserialized data child node "event" missed'));
        }
        if (!isset($data['type'])) {
            throw new RuntimeException(sprintf('Deserialized "type" attribute for d"event" node missed'));
        }

        $container = $this->createEventContainer();
        $visitor->startVisitingObject($context->getMetadataFactory()->getMetadataForClass(get_class($container)), $container, [], $context);

        $event = $visitor->getNavigator()->accept($data->data, array('name' => $this->namingStrategy->typeToClass((string) $data['type'])), $context);
        $this->setContainerEvent($container, $event);

        return $container;
    }

    /**
     * Normalize event container to serialize with generic serialization
     * Event class name is added
     *
     * @param GenericSerializationVisitor $visitor
     * @param EventContainer              $container
     * @param Context                     $context
     *
     * @return array
     */
    protected function serializeContainer(GenericSerializationVisitor $visitor, EventContainer $container, Context $context)
    {
        $setRoot = $visitor->getRoot() === null;
        $data = array(
            'type' => $this->namingStrategy->classToType(get_class($container->getEvent())),
            'data' => $visitor->getNavigator()->accept($container->getEvent(), null, $context)
        );

        if ($setRoot) {
            $visitor->setRoot($data);
        }

        return $data;
    }

    /**
     * Create container from generic decoded array
     *
     * @param GenericDeserializationVisitor $visitor
     * @param array $data
     * @param \JMS\Serializer\Context $context
     *
     * @return EventContainer
     * @throws RuntimeException
     */
    protected function deserializeContainer(GenericDeserializationVisitor $visitor, array $data, Context $context)
    {
        if (!isset($data['data']) || !isset($data['type'])) {
            throw new RuntimeException('Missed properties in the deserialized data array: "event", "type"');
        }

        $container = $this->createEventContainer();
        $visitor->startVisitingObject($context->getMetadataFactory()->getMetadataForClass(get_class($container)), $container, [], $context);

        $event = $visitor->getNavigator()->accept($data['data'], array('name' => $this->namingStrategy->typeToClass($data['type'])), $context);

        if (!is_object($event)) {
            throw new RuntimeException(sprintf('Deserialized Event object expected, got "%s"', gettype($event)));
        }
        if (!$event instanceof Event) {
            throw new RuntimeException(sprintf('Deserialized Event object expected, got "%s"', get_class($event)));
        }

        $this->setContainerEvent($container, $event);

        return $visitor;
    }

    protected function createEventContainer()
    {
        $ref = new \ReflectionClass(__NAMESPACE__ . '\EventContainer');

        return $ref->newInstanceWithoutConstructor();
    }

    protected function setContainerEvent(EventContainer $container, Event $event)
    {
        $ref = new \ReflectionClass(__NAMESPACE__ . '\EventContainer');
        $property = $ref->getProperty('event');
        $property->setAccessible(true);
        $property->setValue($container, $event);
    }
}