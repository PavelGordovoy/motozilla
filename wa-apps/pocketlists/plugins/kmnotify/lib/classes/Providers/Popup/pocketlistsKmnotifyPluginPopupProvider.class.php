<?php

/**
 * Class pocketlistsKmnotifyPluginPopupProvider
 */
class pocketlistsKmnotifyPluginPopupProvider implements pocketlistsKmnotifyPluginProviderInterface
{
    const IDENTIFIER = 'popup';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getDirection()
    {
        return pocketlistsNotification::DIRECTION_INTERNAL;
    }

    /**
     * @param pocketlistsContact $user
     *
     * @return bool
     * @throws waException
     */
    public function isEnabledForUser(pocketlistsContact $user, $event = null)
    {
        return $this->isEnabled();
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
        return _wp('Popup');
    }

    /**
     * @param pocketlistsKmnotifyPluginEvent $event
     * @param pocketlistsLog                 $log
     *
     * @return bool|pocketlistsNotificationContentInterface
     * @throws waException
     */
    public function createContentForEventFromLog(pocketlistsKmnotifyPluginEvent $event, pocketlistsLog $log)
    {
        $content = new pocketlistsKmnotifyPluginPopupContent();
        $itemName = $log->getParamValueByKey('item.name');

        switch ($event->getName()) {
            case pocketlistsKmnotifyPluginEventFactory::ITEM_UPDATE_ASSIGNED_TO_YOU:
                $content->setText(sprintf_wp('➡️ New to-do from %s: %s', $log->getContact()->getName(), $itemName));
                break;

            case pocketlistsKmnotifyPluginEventFactory::ITEM_UPDATE_UNASSIGNED_TO_YOU:
                $content->setText(sprintf_wp('❌️ To-do unassigned from you: %s', $itemName));
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMPLETES_YOUR_ITEM:
                $content->setText(sprintf_wp('✅ %s completed the to-do: %s', $log->getContact()->getName(), $itemName));
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMPLETES_ASSIGNED_ITEM:
                $content->setText(sprintf_wp('☑️ %s completed assigned to you to-do: %s', $log->getContact()->getName(), $itemName));
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
                    ->setText(sprintf_wp('%s %s increases to-do priority: %s', $emoji, $log->getContact()->getName(), $itemName));
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMMENTS_YOUR_ITEM:
                $content
                    ->setText(
                        sprintf_wp('💬 %s left comment to your to-do: %s', $log->getContact()->getName(), $itemName)
                    );
                break;

            case pocketlistsKmnotifyPluginEventFactory::SOMEONE_COMMENTS_ASSIGNED_ITEM:
                $content
                    ->setText(
                        sprintf_wp('💬 %s left comment to assigned to you to-do: %s', $log->getContact()->getName(), $itemName)
                    );
                break;

            default:
                $content = false;
        }

        return $content;
    }

    /**
     * @param string $rawData
     *
     * @return pocketlistsKmnotifyPluginPopupContent
     */
    public function createContent($rawData)
    {
        return new pocketlistsKmnotifyPluginPopupContent($rawData);
    }

    /**
     * @return string
     * @throws waException
     */
    public function getSettingsHtml()
    {
        $html = sprintf(
            '<div class="block"><a href="#" data-kmnotify-action="check-popup" class="inline-link" style="margin-bottom: 10px;display: inline-block;"><b>%s</b></a></div>',
            _wp('Show me popup notification examples')
        );

        $checkText = json_encode([
            sprintf_wp('➡️ New to-do from %s: %s', _wp('user'), _wp('To-do name')),
            sprintf_wp('❌️ To-do unassigned from you: %s', _wp('user'), _wp('To-do name')),
            sprintf_wp('✅ %s completed the to-do: %s', _wp('user'), _wp('To-do name')),
            sprintf_wp('☑️ %s completed assigned to you to-do: %s', _wp('user'), _wp('To-do name')),
        ]);

        $html .= <<<HTML
<script>
    'use strict';
    (function () {
        if ($.wa_push) {
            $.wa_push.init();
        }

        var popupCheck = $('[data-kmnotify-action="check-popup"]'),
            checkTexts = $checkText; 
        
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
        
        async function showPopups() {
            if (window['pocketlistsAlertBox']) {
                for(var i=0; i < checkTexts.length; i++) {
                    var alertbox = new pocketlistsAlertBox('#pl2-notification-area', {
                        closeTime: 120000,
                        persistent: true,
                        hideCloseButton: false
                    });
                    alertbox.show({text: checkTexts[i]}); 
                    await sleep(1000);
                }
            }
        }

        popupCheck.on('click', function (e) {
            e.preventDefault();
            showPopups();
        });
    }());
</script>
HTML;

        return $html;
    }
}
