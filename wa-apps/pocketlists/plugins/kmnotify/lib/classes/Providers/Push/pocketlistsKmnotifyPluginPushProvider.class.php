<?php

/**
 * Class pocketlistsKmnotifyPluginPushProvider
 */
class pocketlistsKmnotifyPluginPushProvider implements pocketlistsKmnotifyPluginProviderInterface
{
    const IDENTIFIER = 'push';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        try {
            return wa()->getPush()->isEnabled();
        } catch (waException $ex) {
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getDirection()
    {
        return pocketlistsNotification::DIRECTION_EXTERNAL;
    }

    /**
     * @param pocketlistsContact $user
     *
     * @return bool
     * @throws waException
     */
    public function isEnabledForUser(pocketlistsContact $user)
    {
        return $this->isEnabled()
            && (new waPushSubscribersModel())->getByField('contact_id', $user->getId());
    }

    /**
     * @return waPushAdapter
     * @throws waException
     */
    public function getPush()
    {
        return wa()->getPush();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return _wp('Push');
    }

    /**
     * @param pocketlistsKmnotifyPluginEvent $event
     * @param pocketlistsLog                 $log
     *
     * @return bool|pocketlistsKmnotifyPluginPushContent
     * @throws waException
     */
    public function createContentForEventFromLog(pocketlistsKmnotifyPluginEvent $event, pocketlistsLog $log)
    {
        $content = new pocketlistsKmnotifyPluginPushContent();
        $itemName = $log->getParamValueByKey('item.name');

        switch ($event->getName()) {
            case pocketlistsKmnotifyPluginEventFactory::ITEM_UPDATE_ASSIGNED_TO_YOU:
                $content
                    ->setText(sprintf_wp('➡️ New to-do from %s', $log->getContact()->getName()))
                    ->setTitle($itemName);
                break;

            case pocketlistsKmnotifyPluginEventFactory::ITEM_UPDATE_UNASSIGNED_TO_YOU:
                $content
                    ->setText(_wp('❌️ To-do unassigned from you'))
                    ->setTitle($itemName);
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMPLETES_YOUR_ITEM:
                $content
                    ->setText(sprintf_wp('✅ %s completed the to-do', $log->getContact()->getName()))
                    ->setTitle($itemName);
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMPLETES_ASSIGNED_ITEM:
                $content
                    ->setText(
                        sprintf_wp('☑️ %s completed assigned to you to-do', $log->getContact()->getName())
                    )
                    ->setTitle($itemName);
                break;

            case pocketlistsKmnotifyPluginEventFactory::INCREASE_PRIORITY_FOR_ASSIGNED_ITEM:
                $priority = $log->getParamValueByKey('item.priority');
                if ($priority == 1) {
                    $emoji = '❕';
                } elseif ($priority == 4) {
                    $emoji = '🔥';
                } elseif ($priority == 5) {
                    $emoji = '🔥🔥🔥';
                } else {
                    $emoji = implode(array_fill(0, $priority, '❗'));
                }

                $content
                    ->setText(sprintf_wp('%s %s increases priority', $emoji, $log->getContact()->getName()))
                    ->setTitle($itemName);
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMMENTS_YOUR_ITEM:
                $content
                    ->setText(
                        sprintf_wp('💬 %s left comment to your to-do', $log->getContact()->getName())
                    )
                    ->setTitle($itemName);
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMMENTS_ASSIGNED_ITEM:
                $content
                    ->setText(
                        sprintf_wp('💬 %s left comment to assigned to you to-do', $log->getContact()->getName())
                    )
                    ->setTitle($itemName);
                break;

            default:
                $content = false;
        }

        return $content;
    }

    /**
     * @param string $rawData
     *
     * @return pocketlistsKmnotifyPluginPushContent
     */
    public function createContent($rawData)
    {
        return new pocketlistsKmnotifyPluginPushContent($rawData);
    }

    /**
     * @return string
     * @throws waException
     */
    public function getSettingsHtml()
    {
        $push = $this->isEnabled();
        $isSubscriber = $this->isEnabledForUser(pl2()->getUser());

        if (empty($push)) {
            $html = sprintf(
                '<div class="block"><div class="small highlighted">%s</div></div>',
                _wp('Push notification are not enabled in Settings')
            );
        } else {
            $html = sprintf(
                '<div class="block"><a href="#" data-kmnotify-action="enable-push" class="inline-link" style="margin-bottom: 10px;display: inline-block;"><b>%s</b></a><div class="small highlighted">%s</div></div>',
                _wp('Enable push notifications'),
                _wp('Push notification invitation will appear in case if you didn\'t enabled previously')
            );
        }

        $wa_url = wa()->getRootUrl(true);

        $html .= <<<HTML
<script>
    'use strict';
    (function () {
        if ($.wa_push) {
            $.wa_push.init();
        }

        var pushEnable = $('[data-kmnotify-action="enable-push"]');

        // if ($.storage.get('/pocketlists/kmnotify/push/inited') !== null) {
        //     pushEnable.hide();
        // }

        pushEnable.on('click', function (e) {
            e.preventDefault();

            $.getScript('{$wa_url}wa-content/js/jquery-wa/wa.push.js', function () {
                $.wa_push.init();
                // $.storage.set('/pocketlists/kmnotify/push/inited', 1);
                pushEnable.hide();
            });
        });
    }());
</script>
HTML;

        return $html;
    }
}
