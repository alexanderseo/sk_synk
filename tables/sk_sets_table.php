<?php


class sk_sets_table extends bootstrap {
    private static $instance;
    private $all_options;
    private $all_products;
    private $all_postmeta;
    private $term_relationships;
    private $term_taxonomy;
    private $for_table_sets;

    public function __construct() {
        parent::__construct();

        global $wordpress;
        $this->all_options = $wordpress['options_bases'];
        $this->all_products = $wordpress['posts'];
        $this->all_postmeta = $wordpress['postmeta'];
        $this->term_relationships = $wordpress['term_relationships'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];

        $this->for_table_sets = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {
        $arrays_sets_id_and_ids = $this->routing_sets($this->all_options, $this->all_products, $this->all_postmeta, $this->term_relationships);
//        var_dump('--------', $arrays_sets_id_and_ids);

        $this->for_table_sets = $this->union_to_one_array($arrays_sets_id_and_ids);

//        var_dump('=============', $this->for_table_sets);

        return $this->for_table_sets;
    }

    /**
     * @param $all_options
     * @param $products
     * @param $postmeta
     * @param $relationships
     * @return array
     * Так легче обработать массив из разных ключей одним общим методом через этот промежуточный
     */
    private function routing_sets($all_options, $products, $postmeta, $relationships) {
        $data = [];

        foreach ($all_options as $key_option => $value_option) {
            if (isset($all_options[$key_option]['sets_by_id'])) {
                foreach ($all_options[$key_option]['sets_by_id'] as $key_id => $value_group) {
                    $data[] = $this->create_body_for_table($value_group, $products, $postmeta, $relationships);
                }
            }

            if (isset($all_options[$key_option]['sets_by_id_category'])) {
                foreach ($all_options[$key_option]['sets_by_id_category'] as $key_id => $value_group) {
                    $data[] = $this->create_body_for_table($value_group, $products, $postmeta, $relationships);
                }
            }

            if (isset($all_options[$key_option]['sets_by_id_collection'])) {
                foreach ($all_options[$key_option]['sets_by_id_collection'] as $key_id => $value_group) {
                    $data[] = $this->create_body_for_table($value_group, $products, $postmeta, $relationships);
                }
            }

            if (isset($all_options[$key_option]['sets_by_catcol'])) {
                foreach ($all_options[$key_option]['sets_by_catcol'] as $key_id => $value_group) {
                    $data[] = $this->create_body_for_table($value_group, $products, $postmeta, $relationships);
                }
            }


        }

//        var_dump('8888888888888888', $data);
        return $data;
    }

    private function create_body_for_table($value_group, $products, $postmeta, $relationships) {
        $data = [];

        if (isset($value_group['groups'])) {
            foreach ($value_group['groups'] as $key => $item) {
                foreach ($item as $option) {
                    if (isset($option['group_by_ids'])) {
                        $ids = $this->get_products($option['group_by_ids'][0]['ids_products'], $products);
                        $unique_id = crc32($ids);
                        $data[$unique_id]['id'] = $unique_id;
                        $data[$unique_id]['ids'] = $this->get_products($option['group_by_ids'][0]['ids_products'], $products);
                    }

                    if (isset($option['group_by_collections'])) {
                        $ids = $this->get_collections_products(isset($option['group_by_collections'][0]['ids_collections']) ? $option['group_by_collections'][0]['ids_collections'] : "", $products, $postmeta);
                        $unique_id = crc32($ids);
                        $data[$unique_id]['id'] = $unique_id;
                        $data[$unique_id]['ids'] = $this->get_collections_products(isset($option['group_by_collections'][0]['ids_collections']) ? $option['group_by_collections'][0]['ids_collections'] : "", $products, $postmeta);
                    }

                    if (isset($option['group_by_required_ids'])) {
                        $ids = $this->get_required_products(isset($option['group_by_required_ids'][0]['required_ids']) ? $option['group_by_required_ids'][0]['required_ids'] : "", $products);
                        $unique_id = crc32($ids);
                        $data[$unique_id]['id'] = $unique_id;
                        $data[$unique_id]['ids'] = $this->get_required_products(isset($option['group_by_required_ids'][0]['required_ids']) ? $option['group_by_required_ids'][0]['required_ids'] : "", $products);
                    }

                    if (isset($option['group_by_categories'])) {
                        $ids = $this->get_categories_products(isset($option['group_by_categories'][0]['ids_categories']) ? $option['group_by_categories'][0]['ids_categories'] : "", $products, $relationships);
                        $unique_id = crc32($ids);
                        $data[$unique_id]['id'] = $unique_id;
                        $data[$unique_id]['ids'] = $this->get_categories_products(isset($option['group_by_categories'][0]['ids_categories']) ? $option['group_by_categories'][0]['ids_categories'] : "", $products, $relationships);
                    }

                    if (isset($option['group_by_catcol'])) {
                        $ids_cat = $this->get_categories_products(isset($option['group_by_catcol'][0]['ids_categories']) ? $option['group_by_catcol'][0]['ids_categories'] : "", $products, $relationships);
                        $ids_col = $this->get_collections_products(isset($option['group_by_catcol'][0]['ids_collections']) ? $option['group_by_catcol'][0]['ids_collections'] : "", $products, $postmeta);

                        if ($ids_col == 404) {
                            $unique_id = crc32($ids_cat);
                            $data[$unique_id]['id'] = $unique_id;
                            $data[$unique_id]['ids'] = $ids_cat;
                        }

                        $ids = self::intersect_catcol($ids_cat, $ids_col);
                        if (!empty($ids)) {
                            $unique_id = crc32($ids);
                            $data[$unique_id]['id'] = $unique_id;
                            $data[$unique_id]['ids'] = $ids;
                        }

//                        var_dump('==========', $data);
                    }

                }
            }
        }



        return $data;
    }

    static function intersect_catcol($category_ids, $collection_ids) {
        $ids_category_array = explode(',', $category_ids);
        $ids_collection_array = explode(',', $collection_ids);

        $intersect = array_intersect($ids_category_array, $ids_collection_array);

        return implode(',', $intersect);
    }

    private function get_products($id, $products) {
        $data = [];

        foreach ($products as $key => $product_item) {
            if ($key == $id) {
                $data[] = $product_item['ID'];
            }
        }

        $data_out = array_unique($data);
        $ids_array = array_diff($data_out, array("null", ""));
        $data_string = implode(',', $ids_array);

        return $data_string;
    }

    /**
     * @param $id
     * @param $products
     * @param $postmeta
     * @return int|string
     * Возвращает 404, если id коллекции нет
     */
    private function get_collections_products($id, $products, $postmeta) {
        $data = [];

        if (!$id) {
            return 404;
        }

        $ids_products = $this->get_products_from_collection($id, $postmeta);

        foreach ($ids_products as $product_id) {
            foreach ($products as $key => $product_item) {
                if ($key == $product_id) {
                    $data[] = $product_item['ID'];
                }
            }
        }

        $data_out = array_unique($data);
        $ids_array = array_diff($data_out, array("null", ""));
        $data_string_collection = implode(',', $ids_array);

        return $data_string_collection;
    }

    private function get_products_from_collection($id_collection, $postmeta) {
        $ids_products = [];

        foreach ($postmeta as $key => $item) {
            if (isset($item['_product_collection'])) {
                if ($item['_product_collection'] == $id_collection) {
                    $ids_products[] = $key;
                }
            }
        }

        return $ids_products;
    }

    private function get_required_products($id, $products) {
        $data = [];
        $ids_products = explode(',', $id);

        foreach ($ids_products as $product_id) {
            foreach ($products as $key => $product_item) {
                if ($key == $product_id) {
                    $data[] = $product_item['ID'];
                }
            }
        }

        $data_out = array_unique($data);
        $ids_array = array_diff($data_out, array("null", ""));
        $data_string = implode(',', $ids_array);

        return $data_string;
    }

    private function get_categories_products($id, $products, $relationships) {
        $data = [];
        $ids_products = $this->get_products_from_category($id, $relationships);

        foreach ($ids_products as $product_id) {
            foreach ($products as $key => $product_item) {
                if ($key == $product_id) {
                    $data[] = $product_item['ID'];
                }
            }
        }

        $data_out = array_unique($data);
        $ids_array = array_diff($data_out, array("null", ""));
        $data_string = implode(',', $ids_array);

        return $data_string;
    }

    private function get_products_from_category($id, $relationships) {
        $ids_products = [];

        foreach ($relationships as $key => $item) {
            foreach ($item as $item_id) {
                if (isset($item_id['term_taxonomy_id'])) {
                    if ($item_id['term_taxonomy_id'] == $id) {
                        $ids_products[] = $key;
                    }
                }
            }
        }

        return $ids_products;
    }

    /**
     * @param $arrays_sets_id_and_ids
     * @return array
     * Объеденить массивы в один по общему ключу, чтобы устранить дубликаты
     */
    private function union_to_one_array($arrays_sets_id_and_ids) {
        $result_array = [];

        foreach ($arrays_sets_id_and_ids as $key => $value) {
            foreach($arrays_sets_id_and_ids[$key] as $item_key => $item_value) {
                $result_array[$item_key] = $item_value;
            }
        }

        return $result_array;
    }
}