<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Serializer\Jms;

use EventBand\Event;
use EventBand\Serializer\EventSerializer;
use EventBand\Serializer\SerializerException;
use EventBand\Serializer\UnexpectedResultException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\SerializerInterface;

/**
 * Class JmsEventSerializer
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class JmsEventContainerSerializer implements EventSerializer
{
    private $serializer;
    private $format;

    public function __construct(SerializerInterface $serializer, $format = 'json')
    {
        $this->serializer = $serializer;
        $this->format = $format;
    }

    /**
     * {@inheritDoc}
     */
    public function serializeEvent(Event $event)
    {
        try {
            return $this->serializer->serialize(new EventContainer($event), $this->format);
        } catch (\Exception $e) {
            // Can not fully detect an error type, so just throw a generic exception
            throw new SerializerException('Error while serializing event', $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deserializeEvent($data)
    {
        try {
            /** @var $container EventContainer */
            $container = $this->serializer->deserialize($data, __NAMESPACE__ . '\EventContainer', $this->format);
        } catch (UnsupportedFormatException $e) {
            throw new SerializerException(sprintf('Wrong serializer format "%s". Check your configuration', $this->format), $e);
        } catch (\Exception $e) {
            throw new SerializerException(sprintf('Exception in JMS deserialize: %s', $e->getMessage()), $e);
        }

        if (!$container instanceof EventContainer) {
            throw new UnexpectedResultException($container, 'EventContainer');
        }

        return $container->getEvent();
    }
}