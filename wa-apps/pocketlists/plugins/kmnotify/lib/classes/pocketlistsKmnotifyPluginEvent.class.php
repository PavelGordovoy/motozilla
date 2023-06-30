<?php

/**
 * Class pocketlistsKmnotifyEvent
 */
final class pocketlistsKmnotifyPluginEvent
{
    /**
     * @var pocketlistsContact
     */
    private $affectedUser;

    /**
     * @var string
     */
    private $name;

    /**
     * @param pocketlistsContact $user
     *
     * @return pocketlistsKmnotifyPluginEvent
     */
    public function setAffectedUser(pocketlistsContact $user)
    {
        $this->affectedUser = $user;

        return $this;
    }

    /**
     * @return pocketlistsContact
     */
    public function getAffectedUser()
    {
        return $this->affectedUser;
    }

    public function isValidVorUser(pocketlistsContact $user)
    {
//        return arra($user->getId())
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return pocketlistsKmnotifyPluginEvent
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
