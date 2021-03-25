<?php

require_once 'general_helpers.php';


class CreateBodyOptions {
    private $sets;
    public static $empty_img = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Noun_Project_question_mark_icon_1101884_cc.svg/1200px-Noun_Project_question_mark_icon_1101884_cc.svg.png';

    use general_helpers;

    public function __construct() {
        $this->sets = [];
    }

    public function __invoke($options, $products, $postmeta, $relationships, $categories, $terms, $id_collection) {

        $this->sets = $this->set_bind($options, $products, $postmeta, $relationships, $categories, $terms, $id_collection);

        return $this->sets;
    }

    private function set_bind($options, $products, $postmeta, $relationships, $categories, $terms, $id_collection) {
        $data = [];

        if (isset($options['groups'])) {
            foreach ($options['groups'] as $key => $item) {

                foreach ($item as $option) {
                    if (isset($option['group_by_ids'])) {
                        $data[$key]['discount'] = $option['group_by_ids'][0]['discount'];
                        $data[$key]['base_max'] = $option['group_by_ids'][0]['base_max'];
                        $data[$key]['base_min'] = $option['group_by_ids'][0]['base_min'];
                        $ids_array_items = $this->get_products($option['group_by_ids'][0]['ids_products'], $products);
                        $data[$key]['details'][] = [
                            'maximum' => $option['group_by_ids'][0]['maximum'],
                            'minimum' => $option['group_by_ids'][0]['minimum'],
                            'type' => 'item',
                            'single_products' => $ids_array_items,
                            'unique_id' => crc32($ids_array_items),
                        ];
                    }

                    if (isset($option['group_by_collections'])) {
                        $data[$key]['discount'] = $option['group_by_collections'][0]['discount'];
                        $data[$key]['base_max'] = $option['group_by_collections'][0]['base_max'];
                        $data[$key]['base_min'] = $option['group_by_collections'][0]['base_min'];
                        $ids_array_collections = $this->get_collections_products(isset($option['group_by_collections'][0]['ids_collections']) ? $option['group_by_collections'][0]['ids_collections'] : "", $products, $postmeta);
                        $data[$key]['details'][] = [
                            'maximum' => $option['group_by_collections'][0]['maximum'],
                            'minimum' => $option['group_by_collections'][0]['minimum'],
                            'type' => 'collection',
                            'type_details' => [
                                'img' => ['original' => self::$empty_img],
                            ],
                            'id_collection' => isset($option['group_by_collections'][0]['ids_collections']) ? $option['group_by_collections'][0]['ids_collections'] : "",
                            'collections_products' => $ids_array_collections,
                            'unique_id' => crc32($ids_array_collections),
                        ];
                    }

                    if (isset($option['group_by_required_ids'])) {
                        $data[$key]['discount'] = $option['group_by_required_ids'][0]['discount'];
                        $data[$key]['base_max'] = $option['group_by_required_ids'][0]['base_max'];
                        $data[$key]['base_min'] = $option['group_by_required_ids'][0]['base_min'];
                        $ids_array_required = $this->get_required_products(isset($option['group_by_required_ids'][0]['required_ids']) ? $option['group_by_required_ids'][0]['required_ids'] : "", $products);
                        $data[$key]['details'][] = [
                            'type' => 'required',
                            'required_products' => $ids_array_required,
                            'unique_id' => crc32($ids_array_required),
                        ];
                    }

                    if (isset($option['group_by_categories'])) {
                        for ($i = 0; $i < 6; $i++) {
                            if (isset($option['group_by_categories'][$i])) {
                                $data[$key]['discount'] = $option['group_by_categories'][$i]['discount'];
                                $data[$key]['base_max'] = $option['group_by_categories'][$i]['base_max'];
                                $data[$key]['base_min'] = $option['group_by_categories'][$i]['base_min'];
                                $ids_array_categories = $this->get_categories_products(isset($option['group_by_categories'][$i]['ids_categories']) ? $option['group_by_categories'][$i]['ids_categories'] : "", $products, $relationships);
                                $data[$key]['details'][] = [
                                    'maximum' => $option['group_by_categories'][$i]['maximum'],
                                    'minimum' => $option['group_by_categories'][$i]['minimum'],
                                    'type' => 'category',
                                    'type_details' => self::get_data_for_category(isset($option['group_by_categories'][$i]['ids_categories']) ? $option['group_by_categories'][$i]['ids_categories'] : "", $categories),
                                    'categories_products' => $ids_array_categories,
                                    'unique_id' => crc32($ids_array_categories),
                                ];
                            }
                        }
                    }

                    if (isset($option['group_by_catcol'])) {
                        $data[$key]['discount'] = $option['group_by_catcol'][0]['discount'];
                        $data[$key]['base_max'] = $option['group_by_catcol'][0]['base_max'];
                        $data[$key]['base_min'] = $option['group_by_catcol'][0]['base_min'];
                        $ids_str_categories_catcol = $this->get_categories_products(isset($option['group_by_catcol'][0]['ids_categories']) ? $option['group_by_catcol'][0]['ids_categories'] : "", $products, $relationships);
                        $ids_str_collections_catcol = $this->get_collections_products(isset($option['group_by_catcol'][0]['ids_collections']) ? $option['group_by_catcol'][0]['ids_collections'] : "", $products, $postmeta);
                        $use_parent_collection = isset($option['group_by_catcol'][0]['use_parent_collection']) ? $option['group_by_catcol'][0]['use_parent_collection'] : "";

                        if (empty($use_parent_collection)) {
                            $data_collection = $this->get_collection_by_id($option['group_by_catcol'][0]['ids_collections'], $terms);
                            $products_intersect = self::intersect_catcol($ids_str_categories_catcol, $ids_str_collections_catcol);
                            $data[$key]['details'][] = [
                                'maximum' => $option['group_by_catcol'][0]['maximum'],
                                'minimum' => $option['group_by_catcol'][0]['minimum'],
                                'type' => 'catcol',
                                'type_details_category' => self::get_data_for_category(isset($option['group_by_catcol'][0]['ids_categories']) ? $option['group_by_catcol'][0]['ids_categories'] : "", $categories),
                                'catcol_products' => $products_intersect,
                                'type_details' => [
                                    'term_id' => $data_collection['term_id'],
                                    'name' => $data_collection['name'],
                                    'slug' => $data_collection['slug'],
                                    'img' => ['original' => self::$empty_img],
                                ],
                                'unique_id' => crc32($products_intersect),
                            ];
                        } else {
                            $data[$key]['details'][] = [
                                'maximum' => $option['group_by_catcol'][0]['maximum'],
                                'minimum' => $option['group_by_catcol'][0]['minimum'],
                                'cat' => $option['group_by_catcol'][0]['ids_categories'],
                                'parent_collection' => $id_collection,
                                'type' => 'catcol',
                                'type_details' => self::get_data_for_category(isset($option['group_by_catcol'][0]['ids_categories']) ? $option['group_by_catcol'][0]['ids_categories'] : "", $categories),
                                'cat_products' => $ids_str_categories_catcol,
                                'unique_id' => crc32($ids_str_categories_catcol),
                            ];
                        }
                    }

                }
            }
        }


        return $data;
    }

    static function get_data_for_category($category_ids, $categories) {
        $body = [];

        if (empty($category_ids)) {
            return $body;
        }


        if (explode(',', $category_ids) == 1) {
            if (isset($categories[$category_ids])) {
                $body = [
                    'nominative_title' => $categories[$category_ids]['nominative_title'],
                    'img' => !empty($categories[$category_ids]['thumbnail']) ? self::set_image_size($categories[$category_ids]['thumbnail']) : ['original' => self::$empty_img],
                ];
            }
        }
        if (explode(',', $category_ids) > 1) {
            $array_ids = explode(',', $category_ids);
            foreach ($array_ids as $id) {
                $nominative_titles[] = $categories[$id]['nominative_title'];
                $images[] = !empty($categories[$id]['thumbnail']) ? self::set_image_size($categories[$id]['thumbnail']) : ['original' => self::$empty_img];
            }

            $body = [
                'nominative_title' => $nominative_titles,
                'img' => $images,
            ];
        }

        return $body;
    }

    static function set_image_size($images) {
        $data = [];

        if(empty($images)) {
            return serialize($data);
        }

        $array = unserialize($images);
        $data['w100'] = isset($array['w100']) ? $array['w100'] : "";
        $data['original'] = isset($array['original']) ? $array['original'] : "";

        return serialize($data);
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

        return $this->unique_array_to_string($data);
    }

    /**
     * @param $id
     * @param $products
     * @param $postmeta
     * @return string
     * Получаем набор ids в строку
     */
    private function get_collections_products($id, $products, $postmeta) {
        $data = [];
        $ids_products = $this->get_products_from_collection($id, $postmeta);

        foreach ($ids_products as $product_id) {
            foreach ($products as $key => $product_item) {
                if ($key == $product_id) {
                    $data[] = $product_item['ID'];
                }
            }
        }

        return $this->unique_array_to_string($data);
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

        return $this->unique_array_to_string($data);
    }

    /**
     * @param $id
     * @param $products
     * @param $relationships
     * @return string
     * Получаем набор ids в строку
     */
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

        return $this->unique_array_to_string($data);
    }

    /**
     * @param $id
     * @param $relationships
     * @return array
     */
    private function get_products_from_category($id, $relationships) {
        $ids_products = [];

        if ($id) {

            $ids = explode(',', $id);
            foreach ($ids as $id_item) {
                foreach ($relationships as $key => $item) {
                    foreach ($item as $item_id) {
                        if (isset($item_id['term_taxonomy_id'])) {
                            if ($item_id['term_taxonomy_id'] == $id_item) {
                                $ids_products[] = $key;
                            }
                        }
                    }
                }
            }


        }

        return $ids_products;
    }

}