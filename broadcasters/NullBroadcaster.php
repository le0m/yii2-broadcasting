<?php


namespace le0m\broadcasting\broadcasters;


/**
 * Broadcaster implementation that sends messages to /dev/null.
 *
 * @author Maksim Kiselev <maks280795@yandex.ru>
 */
class NullBroadcaster
{
    /**
     * {@inheritdoc}
     */
    public function auth($user, $channelName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validAuthenticationResponse($user, $result)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
    }
}
