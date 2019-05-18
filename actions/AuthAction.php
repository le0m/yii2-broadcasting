<?php


namespace le0m\broadcasting\actions;

use Yii;
use yii\base\Action;


class AuthAction extends Action
{
    /**
     * Authorize a private or presence channel.
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $channelName = Yii::$app->request->post('channel_name');

        return $this->getBroadcasterInstance()
            ->auth(Yii::$app->user, $channelName);
    }

    /**
     * Get the broadcaster component.
     *
     * @return \le0m\broadcasting\broadcasters\Broadcaster
     * @throws \yii\base\InvalidConfigException
     */
    protected function getBroadcasterInstance()
    {
        /** @var \le0m\broadcasting\BroadcastManager $comp */
        $comp = Yii::$app->get('broadcasting');

        return $comp->getBroadcasterInstance();
    }
}
