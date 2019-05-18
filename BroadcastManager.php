<?php


namespace le0m\broadcasting;

use le0m\broadcasting\broadcasters\Broadcaster;
use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\base\Component;


/**
 * Broadcast Manager class.
 *
 * @property-read string|null $socketId Socket ID for the current request
 *
 * @author Maksim Kiselev <maks280795@yandex.ru>
 * @author Leo Mainardi <mainardi.leo@gmail.com>
 */
class BroadcastManager extends Component
{
    /**
     * @var string|array|\le0m\broadcasting\broadcasters\Broadcaster
     */
    public $broadcaster;


    /**
     * Get the broadcaster instance
     *
     * @return \le0m\broadcasting\broadcasters\Broadcaster|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getBroadcasterInstance()
    {
        if (!is_object($this->broadcaster)) {
            $this->broadcaster = Instance::ensure($this->broadcaster, Broadcaster::class);
        }

        return $this->broadcaster;
    }

    /**
     * Dispatch event
     *
     * @param \le0m\broadcasting\BroadcastEvent $event
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    public function dispatchEvent(BroadcastEvent $event)
    {
        $name = $event->broadcastAs();

        $channels = ArrayHelper::toArray($event->broadcastOn());

        $payload = $event->broadcastWith();

        if ($event->toOthers === true) {
            $payload = array_merge($payload, ['socket' => $this->getSocketId()]);
        }

        $this->getBroadcasterInstance()->broadcast($channels, $name, $payload);
    }

    /**
     * Get the socket ID for the current request
     *
     * @return string|null
     */
    public function getSocketId()
    {
        $request = Yii::$app->getRequest();

        if ($request instanceof yii\web\Request) {
            return $request->getHeaders()->get('X-Socket-ID');
        }

        return null;
    }
}
