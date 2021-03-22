<?php

class sk_products_categories_relashionships extends bootstrap {
    private static $instance;

    private $log;

    private $product_categories;

    public function __construct() {
        parent::__construct();

        $this->log = [];
        $this->product_categories = [];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {
        global $wordpress;

        foreach ($wordpress['posts_ids']['products'] as $id) {
            $this->set_category_id($id);
        }

        $this->set_log($this->log);

        $new_array = [];

        foreach ($this->product_categories as $item) {
            foreach ($item as $key => $value) {
                $new_array[$key] = $value;
            }
        }

//        var_dump($new_array);
        return $new_array;
    }

    private function get_category_id($id) {
        global $wordpress;

        $array = array();

        foreach ($wordpress['term_relationships'][$id] as $relationship) {
            if ($wordpress['term_taxonomy'][$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                $array[] = $wordpress['term_taxonomy'][$relationship['term_taxonomy_id']];
            }
        }

        $categories_products = [];

        foreach ($array as $key_array => $value_array) {
            $categories_products[$id . $value_array['term_id']]['id'] = $id . $value_array['term_id'];
            $categories_products[$id . $value_array['term_id']]['product_id'] = $id;
            $categories_products[$id . $value_array['term_id']]['category_id'] = $value_array['term_id'];
            $categories_products[$id . $value_array['term_id']]['parent'] = $value_array['parent'];
        }


        return $categories_products;
    }

    private function set_category_id($id) {
        $this->product_categories[] = $this->get_category_id($id);
    }
}