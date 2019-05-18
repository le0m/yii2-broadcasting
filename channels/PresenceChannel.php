<?php


namespace le0m\broadcasting\channels;


/**
 * Presence Channel class.
 *
 * @author Maksim Kiselev <maks280795@yandex.ru>
 */
class PresenceChannel extends Channel
{
    /**
     * Create a new channel instance
     *
     * @param  string $name
     */
    public function __construct($name)
    {
        parent::__construct('presence-' . $name);
    }
}
