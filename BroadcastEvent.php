<?php


namespace le0m\broadcasting;

use ReflectionClass;
use ReflectionProperty;
use Yii;
use yii\base\BaseObject;


/**
 * Base Broadcast Event class.
 *
 * @property bool $toOthers Whether to send message only to other users in the channel
 *
 * @author Maksim Kiselev <maks280795@yandex.ru>
 * @author Leo Mainardi <mainardi.leo@gmail.com>
 */
abstract class BroadcastEvent extends BaseObject
{
    /*
     * Is it necessary to exclude the current user from the broadcast's recipients
     */
    private $_toOthers = false;


    /**
     * Get the broadcast component
     *
     * @return \le0m\broadcasting\BroadcastManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getBroadcastManagerInstance()
    {
        /** @var \le0m\broadcasting\BroadcastManager $comp */
        $comp = Yii::$app->get('broadcasting');
        return $comp;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function toOthers($value = true)
    {
        $this->_toOthers = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getToOthers()
    {
        return $this->_toOthers;
    }

    /**
     * Get the channels the event should broadcast on
     *
     * @return string|array
     */
    abstract public function broadcastOn();

    /**
     * The event's broadcast name
     *
     * @return string
     */
    public function broadcastAs()
    {
        return str_replace('\\', '.', static::class);
    }

    /**
     * Get the data to broadcast
     *
     * @return array
     * @throws \ReflectionException
     */
    public function broadcastWith()
    {
        $class = new ReflectionClass($this);
        $data = [];
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $name = $property->getName();
                $data[$name] = $property->getValue($this);
            }
        }

        return $data;
    }

    /**
     * Broadcast this event
     */
    final public function broadcast()
    {
        try {
            $this->getBroadcastManagerInstance()->dispatchEvent($this);
        } catch (\Exception $e) {
            Yii::error($e);
        }
    }
}
