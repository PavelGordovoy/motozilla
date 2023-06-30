<?php

/**
 * Class pocketlistsKmnotifyPluginPopupContent
 */
class pocketlistsKmnotifyPluginPopupContent extends pocketlistsNotificationAbstractContent
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return pocketlistsKmnotifyPluginPopupContent
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return pl2()->getHydrator()->extract($this, ['text', 'toContactId']);
    }

    /**
     * @return bool
     */
    public function send()
    {
        try {
            if ($this->getToContactId()) {
                $contact = pl2()->getEntityFactory(pocketlistsContact::class)->createNewWithId($this->getToContactId());
                if (!$contact->isExists()) {
                    $this->error = sprintf('Contact #%s doesn`t exist anymore (thanos snap)', $contact->getId());

                    return false;
                }
            }

            /** @var pocketlistsKmnotifyPluginPopupProvider $popupProvider */
            $popupProvider = pocketlistsKmnotifyPlugin::getInstance()->getProvider(
                pocketlistsKmnotifyPluginPopupProvider::IDENTIFIER
            );

            try {
                if (!$popupProvider->isEnabledForUser($contact)) {
                    $this->error = sprintf('Popup not enabled for #%s', $contact->getId());

                    return false;
                }
            } catch (Exception $ex) {
                $result = $ex->getMessage();
                $this->error = 'Unable to send POPUP notifications: '.$result;
                pocketlistsHelper::logError($this->error);

                return false;
            }

            pocketlistsHelper::logDebug(
                sprintf(
                    "Popup sent to %s\nData: %s",
                    $this->getToContactId(),
                    json_encode($this, JSON_UNESCAPED_UNICODE)
                ),
                'push.log'
            );

            return true;
        } catch (waException $ex) {
            $this->error = sprintf('Push send error to %s', $this->getToContactId());
            pocketlistsHelper::logError($this->error, $ex);
        }

        return false;
    }
}
