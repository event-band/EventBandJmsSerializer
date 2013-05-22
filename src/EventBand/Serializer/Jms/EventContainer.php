<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Serializer\Jms;

use EventBand\Event;

/**
 * Class EventContainer
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class EventContainer
{
    private $event;

    /**
     * Constructor
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }
}