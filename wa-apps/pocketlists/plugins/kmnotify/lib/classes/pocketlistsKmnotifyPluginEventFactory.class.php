<?php

/**
 * Class pocketlistsKmnotifyPluginEventFactory
 */
final class pocketlistsKmnotifyPluginEventFactory
{
    const ITEM_UPDATE_ASSIGNED_TO_YOU         = 'item.update.new_assign';
    const ITEM_UPDATE_UNASSIGNED_TO_YOU       = 'item.update.unassign';
    const SOMEONE_COMPLETES_YOUR_ITEM         = 'item.complete.yours';
    const SOMEONE_COMPLETES_ASSIGNED_ITEM     = 'item.complete.assigned';
    const INCREASE_PRIORITY_FOR_ASSIGNED_ITEM = 'item.update.priority_increase';
    const SOMEONE_COMMENTS_YOUR_ITEM          = 'item.comment.yours';
    const SOMEONE_COMMENTS_ASSIGNED_ITEM      = 'item.comment.assigned';

    /**
     * @return array
     */
    public static function getEvents()
    {
        return [
            self::ITEM_UPDATE_ASSIGNED_TO_YOU         => _wp('Item assigned to you'),
            self::ITEM_UPDATE_UNASSIGNED_TO_YOU       => _wp('Item unassigned from you'),
            self::SOMEONE_COMPLETES_YOUR_ITEM         => _wp('Someone competes your item'),
            self::SOMEONE_COMPLETES_ASSIGNED_ITEM     => _wp('Someone competes item assigned to you'),
            self::INCREASE_PRIORITY_FOR_ASSIGNED_ITEM => _wp('Someone increases priority for item assigned to you'),
            self::SOMEONE_COMMENTS_YOUR_ITEM          => _wp('Someone left comment to your item'),
            self::SOMEONE_COMMENTS_ASSIGNED_ITEM      => _wp('Someone left comment to item assigned to you'),
        ];
    }

    /**
     * @param pocketlistsLog $log
     *
     * @return pocketlistsKmnotifyPluginEvent[]
     * @throws waException
     */
    public static function createFromLog(pocketlistsLog $log)
    {
        $events = [];

        switch ($log->getEntityType()) {
            case pocketlistsLog::ENTITY_ITEM:
                $events = self::itemRelated($log);
                break;

            case pocketlistsLog::ENTITY_COMMENT:
                $events = self::commentRelated($log);
                break;
        }

        return $events;
    }

    /**
     * @param pocketlistsLog $log
     *
     * @return pocketlistsKmnotifyPluginEvent[]
     * @throws waException
     */
    private static function itemRelated(pocketlistsLog $log)
    {
        $events = [];
        $currentContactId = pl2()->getUser()->getId();
        $actionPerformerContactId = $log->getContactId();
        $action = $log->getParamValueByKey('item_action');
        $itemOwnerContactId = $log->getParamValueByKey('item.contact_id');
        $isMyItem = $log->getContactId() == $itemOwnerContactId;
        $assignedContactId = $log->getAssignedContactId();

        switch ($log->getAction()) {
            case pocketlistsLog::ACTION_COMPLETE:
                // завершил свой айтем
                if ($isMyItem && $actionPerformerContactId == $assignedContactId) {
                    continue;
                }

                if ($actionPerformerContactId != $itemOwnerContactId) {
                    $events[] = (new pocketlistsKmnotifyPluginEvent())
                        ->setName(self::SOMEONE_COMPLETES_YOUR_ITEM)
                        ->setAffectedUser(
                            pl2()->getEntityFactory(pocketlistsContact::class)->createNewWithId($itemOwnerContactId)
                        );
                } elseif ($actionPerformerContactId != $assignedContactId) {
                    $events[] = (new pocketlistsKmnotifyPluginEvent())
                        ->setName(self::SOMEONE_COMPLETES_ASSIGNED_ITEM)
                        ->setAffectedUser($log->getAssignContact());
                }
                break;

            case pocketlistsLog::ACTION_ADD:
                // айтемы без назначения
                if (!$assignedContactId) {
                    continue;
                }

                // айтемы с назначением на самого себя
                if ($assignedContactId == $currentContactId) {
                    continue;
                }

                $events[] = (new pocketlistsKmnotifyPluginEvent())
                    ->setName(self::ITEM_UPDATE_ASSIGNED_TO_YOU)
                    ->setAffectedUser($log->getAssignContact());

                break;

            case pocketlistsLog::ACTION_UPDATE:
                // айтемы без назначения
                if (!$assignedContactId) {
                    continue;
                }

                $oldPriority = $log->getParamValueByKey('item_old.priority');
                $newPriority = $log->getParamValueByKey('item.priority');
                $oldAssignContactId = $log->getParamValueByKey('item_old.assigned_contact_id');

                // только для назначенных айтемов
                if ($assignedContactId != $actionPerformerContactId) {
                    if ($oldPriority < $newPriority) {
                        $events[] = (new pocketlistsKmnotifyPluginEvent())
                            ->setName(self::INCREASE_PRIORITY_FOR_ASSIGNED_ITEM)
                            ->setAffectedUser($log->getAssignContact());
                    } elseif ($action === pocketlistsLog::ITEM_ACTION_NEW_ASSIGN) {
                        $events[] = (new pocketlistsKmnotifyPluginEvent())
                            ->setName(self::ITEM_UPDATE_ASSIGNED_TO_YOU)
                            ->setAffectedUser($log->getAssignContact());
                    }
                }

                if ($oldAssignContactId
                    && $assignedContactId != $oldAssignContactId
                    && $oldAssignContactId != $actionPerformerContactId
                ) {
                    $events[] = (new pocketlistsKmnotifyPluginEvent())
                        ->setName(self::ITEM_UPDATE_UNASSIGNED_TO_YOU)
                        ->setAffectedUser(
                            pl2()->getEntityFactory(pocketlistsContact::class)->createNewWithId($oldAssignContactId)
                        );
                }

                break;

        }

        return $events;
    }

    /**
     * @param pocketlistsLog $log
     *
     * @return pocketlistsKmnotifyPluginEvent[]
     * @throws waException
     */
    private static function commentRelated(pocketlistsLog $log)
    {
        $events = [];
        $actionPerformerContactId = $log->getContactId();
        $itemOwnerContactId = $log->getParamValueByKey('item.contact_id');
        $isMyItem = $log->getContactId() == $itemOwnerContactId;
        $assignedContactId = $log->getAssignedContactId();

        switch ($log->getAction()) {
            case pocketlistsLog::ACTION_ADD:
                // свой айтем
                if ($isMyItem && $actionPerformerContactId == $assignedContactId) {
                    continue;
                }

                if ($actionPerformerContactId != $itemOwnerContactId) {
                    $events[] = (new pocketlistsKmnotifyPluginEvent())
                        ->setName(self::SOMEONE_COMMENTS_YOUR_ITEM)
                        ->setAffectedUser(
                            pl2()->getEntityFactory(pocketlistsContact::class)->createNewWithId($itemOwnerContactId)
                        );
                } elseif ($actionPerformerContactId != $assignedContactId) {
                    $events[] = (new pocketlistsKmnotifyPluginEvent())
                        ->setName(self::SOMEONE_COMMENTS_ASSIGNED_ITEM)
                        ->setAffectedUser($log->getAssignContact());
                }
                break;
        }

        return $events;
    }
}
