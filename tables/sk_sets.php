<?php

require_once 'helpers/CreateBodyOptions.php';


class sk_sets extends bootstrap {
    private static $instance;
    private $all_options;
    private $all_products;
    private $all_postmeta;
    private $sets_options;
    private $term_relationships;
    private $term_taxonomy;
    private $terms;
    private $for_table_sets;

    public function __construct() {
        parent::__construct();

        global $wordpress;
        $this->all_options = $wordpress['options_bases'];
        $this->all_products = $wordpress['products'];
        $this->all_postmeta = $wordpress['postmeta'];
        $this->term_relationships = $wordpress['term_relationships'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];
        $this->terms = $wordpress['terms'];

        $this->sets_options = [];
        $this->for_table_sets = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __invoke($id_product, $fabrics, $categories): string {

        $id_collection = self::get_id_collection($id_product, $this->all_postmeta);
        $id_parent_category = self::get_id_parent_category($id_product, $this->term_relationships, $this->term_taxonomy);
        $id_child_category = self::get_id_child_category($id_product, $this->term_relationships, $this->term_taxonomy);

        $this->sets_options = [
            $this->check_id_product($id_product, $this->all_products, $this->all_postmeta, $this->term_relationships, $categories, $this->terms, $id_collection),
            $this->check_id_collection($id_collection, $this->all_products, $this->all_postmeta, $this->term_relationships, $categories, $this->terms),
            $this->check_id_category($id_parent_category, $id_child_category, $this->all_products, $this->all_postmeta, $this->term_relationships, $categories, $this->terms, $id_collection),
            $this->check_catcol($id_collection, $id_parent_category, $id_child_category, $this->all_products, $this->all_postmeta, $this->term_relationships, $categories, $this->terms),
        ];

        $this->sets_options = $this->cleaning($this->sets_options);
//        var_dump('00000000000', $this->sets_options);
        return serialize($this->sets_options);
    }

    /**
     * @param $array
     * @return array
     * Метод очистки от пустых массивов и создания одного массива из различных элементов пронумерованных по порядку
     */
    private function cleaning($array) {
        $data = [];

        foreach ($array as $key => $value) {
            if (!empty($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    $data[] = $sub_value;
                }
            }
        }

        return $data;
    }

    /**
     * @param $id_product
     * @param $postmeta
     * @return array
     * Получаем id коллекции
     */
    static function get_id_collection($id_product, $postmeta) {
        $id_collection = isset($postmeta[$id_product]['_product_collection']) ? $postmeta[$id_product]['_product_collection'] : [];

        return $id_collection;
    }

    /**
     * @param $id
     * @param $term_relationships
     * @param $term_taxonomy
     * @return array|mixed
     * Получаем id родительской категории товара
     */
    static function get_id_parent_category($id, $term_relationships, $term_taxonomy) {
        $array = [];

        foreach ($term_relationships[$id] as $relationship) {
            if ($term_taxonomy[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat' && $term_taxonomy[$relationship['term_taxonomy_id']]['parent'] == '0') {
                $array[] = $term_taxonomy[$relationship['term_taxonomy_id']];
            }
        }

        $category_id = '';
        foreach ($array as $item) {
            if (isset($item['term_id'])) {
                $category_id = $item['term_id'];
            }
        }

        return $category_id;
    }

    static function get_id_child_category($id, $term_relationships, $term_taxonomy) {
        $array = [];

        if (isset($term_relationships[$id])) {
            foreach ($term_relationships[$id] as $relationship) {
                if ($term_taxonomy[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat' && $term_taxonomy[$relationship['term_taxonomy_id']]['parent'] !== '0') {
                    $array[] = $term_taxonomy[$relationship['term_taxonomy_id']];
                }
            }
        }

        $category_id = '';
        foreach ($array as $item) {
            if (isset($item['term_id'])) {
                $category_id = $item['term_id'];
            }
        }

        return $category_id;
    }

    /**
     * @param $id
     * @return array
     * Если к id товара привязаны настройки комплекта, тогда получаем эти настройки
     */
    private function check_id_product($id, $products, $postmeta, $relationships, $categories, $terms, $id_collection) {
        $options = $this->all_options;

        $options_by_id = [];

        foreach ($options as $item_options) {
            if (isset($item_options['sets_by_id'])) {
                foreach ($item_options as $option) {
                    if (isset($option[$id])) {
                        $options_by_id = $option[$id];
                    }
                }
            }
        }

        $setsById = new CreateBodyOptions();
        $data = $setsById($options_by_id, $products, $postmeta, $relationships, $categories, $terms, $id_collection);

        return $data;
    }

    /**
     * @param $id
     * @return array
     * Получаем настройки комплектов по id коллекции товара
     */
    private function check_id_collection($id, $products, $postmeta, $relationships, $categories, $terms) {
        $options = $this->all_options;

        $options_by_id_collection = [];

        foreach ($options as $item_options) {
            if (isset($item_options['sets_by_id_collection'])) {
                foreach ($item_options as $option) {
                    if (isset($option[$id])) {
                        $options_by_id_collection = $option[$id];
                    }
                }
            }
        }

        $setsByIdCollection = new CreateBodyOptions();
        $data = $setsByIdCollection($options_by_id_collection, $products, $postmeta, $relationships, $categories, $terms, $id);

        return $data;
    }

    /**
     * @param $id
     * @return array
     * Получаем настройки комплектов по id категории товара
     */
    private function check_id_category($id, $id_child_category, $products, $postmeta, $relationships, $categories, $terms, $id_collection) {
        $options = $this->all_options;

        if (empty($id)) {
            return [];
        }

        $options_by_id_category = [];

        foreach ($options as $item_options) {
            if (isset($item_options['sets_by_id_category'])) {
                foreach ($item_options as $option) {
                    if (isset($option[$id])) {
                        $options_by_id_category = $option[$id];
                    }
                }
            }
        }

        if (empty($options_by_id_category)) {
            foreach ($options as $item_options) {
                if (isset($item_options['sets_by_id_category'])) {
                    foreach ($item_options as $option) {
                        if (isset($option[$id_child_category])) {
                            $options_by_id_category = $option[$id_child_category];
                        }
                    }
                }
            }
        }


        $setsByIdCollection = new CreateBodyOptions();
        $data = $setsByIdCollection($options_by_id_category, $products, $postmeta, $relationships, $categories, $terms, $id_collection);

        return $data;
    }

    /**
     * @param $id_collection
     * @param $id_parent_category
     * @param $id_child_category
     * @param $products
     * @param $postmeta
     * @param $relationships
     * @param $categories
     * @return array|mixed
     * Получаем настройки для категория+коллекция
     * Для сравнения создается идентификатор id_collection_id_category в 4 вариантах и сравнивается с получаемым в настройках
     */
    private function check_catcol($id_collection, $id_parent_category, $id_child_category, $products, $postmeta, $relationships, $categories, $terms) {
        $options = $this->all_options;

        if (empty($id_collection)) {
            return [];
        }

        $id_cat_col_parent = $id_collection . '_' . $id_parent_category;
        $id_col_cat_parent = $id_parent_category . '_' . $id_collection;
        $id_col_cat_child = $id_collection . '_' . $id_child_category;
        $id_cat_col_child = $id_child_category . '_' . $id_collection;

        $options_by_catcol = [];

        foreach ($options as $item_options) {
            if (isset($item_options['sets_by_catcol'])) {
                foreach ($item_options as $option) {
                    foreach ($option as $key_id_catcol => $value_option) {

                        if ($key_id_catcol == $id_cat_col_parent) {
                            $options_by_catcol = $value_option;
                        }

                        if ($key_id_catcol == $id_col_cat_parent) {
                            $options_by_catcol = $value_option;
                        }

                        if ($key_id_catcol == $id_col_cat_child) {
                            $options_by_catcol = $value_option;
                        }

                        if ($key_id_catcol == $id_cat_col_child) {
                            $options_by_catcol = $value_option;
                        }

                    }
                }
            }
        }

        $setsByIdCatCol = new CreateBodyOptions();
        $data = $setsByIdCatCol($options_by_catcol, $products, $postmeta, $relationships, $categories, $terms, $id_collection);

        return $data;
    }
}