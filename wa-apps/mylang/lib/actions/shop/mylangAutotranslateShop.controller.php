<?php

class mylangAutotranslateShopController extends waLongActionController
{
    public const STAGE_CATEGORY = 'category';
    public const STAGE_PRODUCT = 'product';

    protected function init()
    {
        /* @var $source string */
        $source = waRequest::post('source');
        /* @var $target string */
        $target = waRequest::post('target');
        /* @var $provider string */
        $provider = waRequest::post('provider');
        /* @var $params array */
        $params = waRequest::post('params', []);
        $offset = ifset($params['product_offset'], '0');
        $this->data['offset'] = (int)$offset;
        $steps = [];
        $this->data['timestamp'] = time();
        $this->data['fields'] = [
            'product' => ['name', 'description', 'summary', 'meta_title', 'meta_keywords', 'meta_description'],
            'category' => ['name', 'description', 'meta_title', 'meta_keywords', 'meta_description']
        ];
        $model = new waModel();
        //prepare products
        if (isset($params['product'])) {
            $this->data['fields']['product'] = array_intersect(
                $this->data['fields']['product'],
                array_keys($params['product'])
            );
            if (!empty($params['full_list'])) {
                $sql = 'SELECT id from shop_product';
            } else {
                $sql = 'SELECT id from shop_product as sp WHERE NOT EXISTS (SELECT type_id, count(*) as cnt from mylang WHERE sp.id=mylang.type_id and mylang.type="product" and mylang.locale=s:locale and mylang.subtype in(s:fields) group by mylang.type_id having cnt < i:cnt_fields)';
            }
            $this->data['products'] = $model->query($sql, ['locale'=> $target, 'fields'=>$this->data['fields']['product'], 'cnt_fields'=>count($this->data['fields']['product'])])->fetchAll('id');
            $this->data['count'][self::STAGE_PRODUCT] = count($this->data['products']);
            $this->data['current'][self::STAGE_PRODUCT] = 0;
            $steps[] = self::STAGE_PRODUCT;
        }
        //prepare categories
        if (isset($params['category'])) {
            $this->data['fields']['category'] = array_intersect(
                $this->data['fields']['category'],
                array_keys($params['category'])
            );
            if (!empty($params['full_list'])) {
                $sql = 'SELECT id from shop_category';
            } else {
                $sql = <<<SQL
(SELECT type_id as id,count(*) as cnt from mylang WHERE mylang.type='category' and mylang.locale=s:locale and mylang.subtype in(s:fields) group by mylang.type_id having cnt < i:cnt_fields) UNION (SELECT sc.id, COUNT(*) as cnt from shop_category as sc WHERE NOT EXISTS (SELECT mylang.type_id from mylang WHERE mylang.type="category" and mylang.locale=s:locale and sc.id=mylang.type_id)) 
SQL;
            }
            $this->data['categories'] = $model->query($sql, ['locale'=> $target, 'fields'=>$this->data['fields']['category'], 'cnt_fields'=>count($this->data['fields']['category'])])->fetchAll('id');
            $this->data['categories'] = array_keys($this->data['categories']);
            $this->data['count'][self::STAGE_CATEGORY] = count($this->data['categories']);
            $steps[] = self::STAGE_CATEGORY;
            $this->data['current'][self::STAGE_CATEGORY] = 1;
        }

        $this->data['current_step'] = reset($steps);
        $this->data['source'] = $source;
        $this->data['target'] = $target;
        $this->data['provider'] = $provider;
        $this->data['hint'] = 'Working';
        $this->data['limit'] = '30';
        $this->data['errors'] = false;
        $this->data['steps'] = $steps;
    }

    protected function step()
    {
        $method = 'step' . ucfirst($this->data['current_step']);
        $this->$method();
        return !$this->isDone();
    }

    protected function stepProduct()
    {
        static $model;
        if (empty($model)) {
            $model = new mylangModel();
        }

        $products = $this->data['products'];
        $options = [
            'provider' => $this->data['provider'],
            'source' => $this->data['source'],
            'target' => $this->data['target']
        ];
        $translate = wa('mylang')->getConfig()->getProvider($options);
        $chunk_size = $this->data['limit'];
        $chunk = array_slice(
            $products,
            $this->data['current'][self::STAGE_PRODUCT],
            $chunk_size,
            true
        );
        if (!empty($chunk)) {
            $sql = 'SELECT * from shop_product WHERE id IN(i:ids)';
            $products = $model->query($sql, ['ids' => array_keys($chunk)])->fetchAll();
            //Get existing translations
            $mylang_sql = 'SELECT * from mylang WHERE type_id IN(i:ids) and locale=s:locale and type="product"';
            $existing = $model->query($mylang_sql, ['ids' => array_keys($chunk), 'locale'=>$this->data['target']])->fetchAll();
            $tr_products = [];
            foreach ($existing as $prod) {
                $tr_products[$prod['type_id']][$prod['subtype']] = $prod['text'];
            }
            //Process products
            foreach ($products as $product) {
                foreach ($this->data['fields']['product'] as $field) {
                    if (!empty($product[$field]) && empty($tr_products[$product['id']][$field])) {
                        $translated = $translate->translate($product[$field]);
                        if ($translated === false) {
                            //something bad
                            $this->data['errors'] = $translate->getErrors();
                            return false;
                        }
                        $translated = str_ireplace(
                            ['{$ name}', '{$ summary}', '{$ price}'],
                            ['{$name}', '{$summary}', '{$price}'],
                            $translated
                        );
                        $product['data']['mylang']['product'][$this->data['target']][$product['id']][$field] = $translated;
                    }
                }
                $this->data['current'][self::STAGE_PRODUCT] += 1;
                if (isset($product['data']['mylang'])) {
                    $model->saveLocale($product, 'shop');
                }
            }
        } else {
            $this->data['current'][self::STAGE_PRODUCT] += 1;
        }
        return true;
    }

    protected function stepCategory()
    {
        static $model;
        if (empty($model)) {
            $model = new mylangModel();
        }

        $categories = $this->data['categories'];
        $options = [
            'provider' => $this->data['provider'],
            'source' => $this->data['source'],
            'target' => $this->data['target']
        ];
        $translate = wa('mylang')->getConfig()->getProvider($options);
        $chunk_size = $this->data['limit'];
        $chunk = array_slice(
            $categories,
            $this->data['current'][self::STAGE_CATEGORY],
            $chunk_size,
            true
        );
        if(empty($chunk)) {
            $this->data['current'][self::STAGE_CATEGORY] += 1;
            return true;
        }
        $sql = 'SELECT * from shop_category WHERE id IN(i:ids)';
        $categories = $model->query($sql, ['ids' => $chunk])->fetchAll();

        //Get existing translations
        $mylang_sql = 'SELECT * from mylang WHERE type_id IN(i:ids) and locale=s:locale and type="category"';
        $existing = $model->query($mylang_sql, ['ids' => array_keys($chunk), 'locale'=>$this->data['target']])->fetchAll();
        $tr_category = [];
        foreach ($existing as $prod) {
            $tr_category[$prod['type_id']][$prod['subtype']] = $prod['text'];
        }
        foreach ($categories as $category) {
            foreach ($this->data['fields']['category'] as $field) {
                if (!empty($category[$field]) && empty($tr_category[$category['id']][$field])) {
                    $translated = $translate->translate($category[$field]);
                    if ($translated === false) {
                        //something bad
                        $this->data['errors'] = $translate->getErrors();
                        return false;
                    }
                    $translated = str_ireplace(
                        ['{$ name}', '{$ summary}', '{$ price}'],
                        ['{$name}', '{$summary}', '{$price}'],
                        $translated
                    );
                    $category['data']['mylang']['category'][$this->data['target']][$category['id']][$field] = $translated;
                }
            }
            $this->data['current'][self::STAGE_CATEGORY] += 1;
            if (isset($category['data']['mylang'])) {
                $model->saveLocale($category, 'shop');
            }
        }
        return true;
    }

    protected function isDone(): bool
    {
        $done = true;
        //No Errors
        if (!$this->data['errors'] && isset($this->data['current'])) {
            foreach ($this->data['current'] as $stage => $current) {
                //Still working this stage
                if ($current <= $this->data['count'][$stage]) {
                    $done = false;
                    break;
                }
                $this->nextStep();
            }
        }
        return $done;
    }

    protected function finish($filename): bool
    {
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
            'offset' => ifset($this->data['current'][$this->data['current_step']], 1),
            'hint' => $this->data['hint'].':'.$this->data['current_step'],
            'total' => array_sum(ifset($this->data['count'], [1])),
            'error' => $this->data['errors'],
        ];
        //debug
        $response['progress'] = (ifempty($response['offset'], 1) / ($response['total'])) * 100;
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

    protected function nextStep() :bool
    {
        $steps = $this->data['steps'];
        foreach ($steps as $id => $step) {
            if (($step == $this->data['current_step']) && ($id+1 < count($steps))) {
                $this->data['current_step'] = $steps[$id+1];
                return true;
            }
        }
        return false;
    }
}
