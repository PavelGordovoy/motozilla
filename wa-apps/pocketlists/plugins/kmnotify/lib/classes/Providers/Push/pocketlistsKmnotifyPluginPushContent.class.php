<?php

/**
 * Class pocketlistsKmnotifyPluginPushContent
 */
class pocketlistsKmnotifyPluginPushContent extends pocketlistsNotificationAbstractContent
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $title;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return pocketlistsKmnotifyPluginPushContent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return pl2()->getHydrator()->extract(
            $this,
            [
                'text',
                'title',
                'toContactId',
            ]
        );
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

            /** @var pocketlistsKmnotifyPluginPushProvider $pushProvider */
            $pushProvider = pocketlistsKmnotifyPlugin::getInstance()->getProvider(
                pocketlistsKmnotifyPluginPushProvider::IDENTIFIER
            );

            try {
                if (!$pushProvider->isEnabledForUser($contact)) {
                    $this->error = sprintf('Push not enabled for #%s', $contact->getId());

                    return false;
                }

                $absolute_backend_url = $this->getBackendUrl($this->getToContactId())
                ?: pl2()->getRootUrl(true).pl2()->getBackendUrl();

                $data = [
                    'title'   => $this->getTitle(),
                    'message' => $this->getText(),
                    'url'     => rtrim($absolute_backend_url, '/').'/pocketlists/',
                ];

                $errors = $pushProvider->getPush()->sendByContact($this->getToContactId(), $data);
                if (!empty($errors)) {
                    if (is_array($errors)) {
                        $this->error = reset($errors);
                    } else {
                        $this->error = $errors;
                    }

                    return false;
                }
            } catch (Exception $ex) {
                $result = $ex->getMessage();
                $this->error = 'Unable to send PUSH notifications: '.$result;
                pocketlistsHelper::logError($this->error);

                return false;
            }

            pocketlistsHelper::logDebug(
                sprintf(
                    "Push sent to %s\nData: %s",
                    $this->getToContactId(),
                    json_encode($data)
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
     * @return pocketlistsKmnotifyPluginPushContent
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
