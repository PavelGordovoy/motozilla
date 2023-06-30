<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginFavouritesModel extends waModel
{

    protected $table = 'helpdesk_pro_favourites';

    /**
     * Get favourite messages for request
     * 
     * @param int $request_id
     * @return array
     */
    public function getFavourites($request_id)
    {
        $favourites = array();
        $sql = "SELECT * FROM {$this->table} WHERE `request_id` = '" . (int) $request_id . "' AND contact_id = '" . wa()->getUser()->getId() . "'";

        foreach ($this->query($sql) as $r) {
            $favourites[$r['message_id']] = $r['message_id'];
        }
        return $favourites;
    }

}
