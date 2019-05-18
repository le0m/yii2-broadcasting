<?php


namespace le0m\broadcasting\channels;


/**
 * Base Channel class.
 *
 * @author Maksim Kiselev <maks280795@yandex.ru>
 */
class Channel
{
    /**
     * The channel's name
     *
     * @var string
     */
    public $name;


    /**
     * Create a new channel instance
     *
     * @param  string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Convert the channel instance to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
