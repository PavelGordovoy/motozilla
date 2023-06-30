<?php


class menuMenuCopyController extends waJsonController
{

    /**
     * @var menuItemModel
     */
    protected $mim;

    /**
     * @var menuItemParamsModel
     */
    protected $mpm;

    public function __construct()
    {

        $this->mim = new menuItemModel();
        $this->mpm = new menuItemParamsModel();
    }

    public function execute()
    {
        if(!$old_menu = $this->mim->getById(waRequest::get('id'))) {
            $this->errors = _w('Menu not found');
            return;
        }

        try {
            $new_menu = $this->createMenu($old_menu);
            $this->createItems($old_menu, $new_menu);

            $this->mim->repair();

            $this->response = $new_menu;
        } catch (Exception $e) {

            $this->errors = _w('An error occurred.');
        }


    }

    /**
     * @param array $old_menu
     * @return array
     * @throws waException
     */
    protected function createMenu($old_menu)
    {
        $i = 1;
        do {
            $new_name = $old_menu['name'] . ' (' . $i++ . ')';
        } while ($this->mim->getByField([
            'name' => $new_name,
            'depth' => 0,
        ]));

        $new_menu = $old_menu;
        $new_menu['name'] = $new_name;
        unset($new_menu['id']);
        //$new_menu['left_key'] = $new_menu['right_key'] = 0;
        $new_menu['id'] = $this->mim->insert($new_menu);

        return $new_menu;
    }

    /**
     * @param array $old_menu
     * @param array $new_menu
     */
    protected function createItems($old_menu, $new_menu)
    {
        $ids_map = [
            $old_menu['id'] => $new_menu['id']
        ];

        $old_items = $this->mim->select('*')
            ->where('left_key > ? AND right_key < ?', $old_menu['left_key'], $old_menu['right_key'])
            ->order('left_key ASC')
            ->fetchAll();

        $old_params = $this->mpm->get(array_column($old_items, 'id'));

        foreach ($old_items as $item) {
            $new_item = $item;

            unset($new_item['id']);
            //$new_item['left_key'] = $new_item['right_key'] = 0;

            $new_item['parent_id'] = ifempty($ids_map[$item['parent_id']], 0);

            $new_id = $this->mim->insert($new_item);

            $ids_map[$item['id']] = $new_id;

            if(isset($old_params[$item['id']])) {
                $this->mpm->set($new_id, $old_params[$item['id']]);
            }
        }
    }
}