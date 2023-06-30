<?php

/**
 * @property array category_fields
 */
class mylangShopExportRunController extends waLongActionController
{
    const category_fields = [
        'name',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'description'
    ];
    const product_fields = [
        'name',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'description'
    ];
    private $writer;
    private $map;

    protected function init()
    {
        $config = [
            'encoding' => 'utf-8',//No choice
            'delimiter' => ';',
            'hash' => waRequest::post('hash', 'products'),
            'locales' => waRequest::post('locales', [])
        ];
        $this->map = $this->getMap($config['hash'], $config['locales']);
        $this->data['hash'] = $config['hash'];

        $this->data['file'] = wa()->getTempPath('mylang_' . $this->data['hash'] . '_export.csv');
        wa('shop');
        $this->writer = new shopCsvWriter($this->data['file'], $config['delimiter'], $config['encoding']);
        $this->writer->setMap($this->map); //Ставим заголовки файла CSV
        $data = $this->getData($config['hash'], $config['locales']);
        foreach ($data as $d) {
            $this->writer->write($d);
        }
        //$params = waRequest::post('params', []);
        $this->data['offset'] = 0;
        $this->data['products_total_count'] = 0;
        $this->data['timestamp'] = time();
        $this->data['fields'] = [
            'product' => ['name', 'description', 'summary'],
            'category' => ['name, description']
        ];
        $this->data['hint'] = 'Working';
        $this->data['limit'] = '30';
        $this->data['errors'] = false;
    }

    protected function step()
    {
        return true;
    }

    protected function isDone()
    {
        if ($this->data['errors']) {
            return true;
        }
        return $this->data['offset'] >= $this->data['products_total_count'];
    }

    /**
     * Called when $this->isDone() is true
     * $this->data is read-only, $this->fd is not available.
     *
     * $this->getStorage() session is already closed.
     *
     * @param $filename string full path to resulting file
     * @return boolean true to delete all process files; false to be able to access process again.
     */
    protected function finish($filename)
    {
        //UTF-8 BOM
        $BOM = "\xEF\xBB\xBF";
        $content = file_get_contents($this->data['file']);
        $content = $BOM . $content;
        waFiles::write($this->data['file'], $content);
        $this->getResponse()->addHeader('Content-type', 'text/csv');
        waFiles::readFile($this->data['file'], "mylang_" . $this->data['hash'] . '_export.csv');
        $this->info();
        if ($this->getRequest()->post('cleanup')) {
            return true;
        }
        return false;
    }

    protected function info()
    {
        $interval = 0;
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
        }
        $response = [
            'time' => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId' => $this->processId,
            'progress' => 0.0,
            'ready' => $this->isDone(),
            'offset' => $this->data['offset'],
            'hint' => $this->data['hint'],
            'total' => $this->data['products_total_count'],
            'error' => $this->data['errors'],
        ];
        $response['progress'] = empty($this->data['products_total_count']) ? 100 : ($this->data['offset'] / $this->data['products_total_count']) * 100;
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);

        if ($this->getRequest()->post('cleanup')) {
            $response['report'] = $this->report();
        }
        echo json_encode($response);
    }

    protected function report()
    {
        return $this->data['errors'] ?: _w('Translate process is finished.');
    }

    /**
     * @param string $hash
     * @param array $locales
     * @return array
     */
    private function getMap($hash = 'products', $locales)
    {
        $product = [
            'id' => _w('Product id'),
            'name' => _w('Product name'),
            //'skus:-1:name' => _w('SKU name'),
            //'skus:-1:sku' => _w('SKU code'),
            'price' => _w('Price'),
            'currency' => _w('Currency'),
            'summary' => _w('Summary'),
            'description' => _w('Description'),
            'meta_title' => 'Title',
            'meta_keywords' => 'META Keyword',
            'meta_description' => 'META Description',
            'url' => _w('Storefront link')
        ];

        $category = [
            'id' => _w('Product id'),
            'name' => _w('Product name'),
            'summary' => _w('Summary'),
            'description' => _w('Description'),
            'meta_title' => 'Title',
            'meta_keywords' => 'META Keyword',
            'meta_description' => 'META Description',
            'url' => _w('Storefront link')
        ];

        if ($hash === 'products') {
            $map = $map_all = $product;
            foreach ($map as $m => $val) {
                if (in_array($m, ['id', 'url', 'price', 'currency'])) {
                    continue;
                }
                foreach ($locales as $l) {
                    $map_all[$m . '_' . $l] = $val . '_' . $l;
                }
            }
            return $map_all;
        }
        if ($hash === 'categories') {
            $map = $map_all = $category;
            foreach ($map as $m => $val) {
                if ($m === 'url' || $m === 'id') {
                    continue;
                }
                foreach ($locales as $l) {
                    $map_all[$m . '_' . $l] = $val . '_' . $l;
                }
            }
            return $map_all;
        }
    }

    /**
     * @param string $hash
     * @param $locales
     * @return array
     */
    private function getData($hash = 'products', $locales)
    {
        wa('mylang');
        $model = new mylangModel();
        if ($hash == 'categories') {
            $categories = [];
            $map = array_combine(array_flip($this->map), array_fill(0, count($this->map), null));
            $data = $model->select('*')->where(
                'type="category" and locale IN(s:locales)',
                ['locales' => $locales]
            )->fetchAll();
            foreach ($data as $cat) {
                if (empty($categories[$cat['type_id']])) {
                    //initial empty data
                    $categories[$cat['type_id']] = $map;
                    $categories[$cat['type_id']]['id'] = $cat['type_id'];
                }
                $categories[$cat['type_id']][$cat['subtype'] . '_' . $cat['locale']] = $cat['text'];
            }
            if (!empty(array_keys($categories))) {
                $catModel = new shopCategoryModel();
                $orig_data = $catModel->query(
                    'SELECT id,url,' . implode(',', self::category_fields) . ' FROM ' . $catModel->getTableName(
                    ) . " WHERE id IN(" . implode(',', array_keys($categories)) . ")"
                )->fetchAll('id', true);
                foreach ($orig_data as $key => $cat) {
                    $categories[$key] = array_merge($categories[$key], $cat);
                }
            }
            return $categories;
        }
        if ($hash == "products") {
            $products = [];
            $map = array_combine(array_flip($this->map), array_fill(0, count($this->map), null));
            $data = $model->select('*')->where(
                'type="product" and locale IN(s:locales)',
                ['locales' => $locales]
            )->fetchAll();
            foreach ($data as $prod) {
                if (empty($products[$prod['type_id']])) {
                    //initial empty data
                    $products[$prod['type_id']] = $map;
                }
                $products[$prod['type_id']][$prod['subtype'] . '_' . $prod['locale']] = $prod['text'];
            }
            if (!empty(array_keys($products))) {
                $prodModel = new shopProductModel();
                $orig_data = $prodModel->query(
                    'SELECT id,url, price, currency, ' . implode(
                        ',',
                        self::product_fields
                    ) . ' FROM ' . $prodModel->getTableName() . " WHERE id IN(" . implode(',', array_keys($products)) . ")"
                )->fetchAll();
                foreach ($orig_data as $key => $prod) {
                    $products[$prod['id']] = array_merge($products[$prod['id']], $prod);
                }
            }
            return array_values($products);
        }
    }
}
