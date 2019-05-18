<?php

namespace le0m\broadcasting\broadcasters;

use Yii;


/**
 * Broadcaster implementation that sends messages to log targets.
 *
 * @author Maksim Kiselev <maks280795@yandex.ru>
 */
class LogBroadcaster
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
        $channels = implode(', ', $this->formatChannels($channels));

        $payload = json_encode($payload, JSON_PRETTY_PRINT);

        $message = 'Broadcasting [' . $event . '] on channels [' . $channels . '] with payload:' . PHP_EOL . $payload;

        Yii::info($message, __METHOD__);
    }
}
