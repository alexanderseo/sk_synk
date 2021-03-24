<?php

require_once 'GetUpsell.php';

class GetCrossSells {

    private $cross_sells;

    public function __construct() {

        $this->cross_sells = [];

    }

    public function get_crosssells($id, $postmeta, $category_id, $categories, $variations, $all_products, $all_postmeta, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $materials, $all_ids_products, $fabrics, $termmeta, $terms_by_slug) {

        if ($this->check_category_id($category_id)) {


            $category_id = $this->check_enable_comparison($category_id, $categories);

            if ($this->second_check_enable_comparison($category_id, $categories)) {
                $crosssell_ids = $this->get_crosssell_ids($postmeta, $id);

                $cross_like_upsell = new GetUpsell($postmeta, $variations, $all_products, $all_postmeta, $all_ids_products, $fabrics, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $termmeta, $terms_by_slug, $materials);

                $this->cross_sells = $cross_like_upsell->get_upsells($crosssell_ids);

                return $this->cross_sells;
            }

            if ($this->check_attributes_product_comparison($category_id, $categories)) {

                if ($this->check_product_attributes($postmeta)) {

                    $attributes_product_comparison = $categories[$category_id]['attributes_product_comparison'];
                    $crosssell_ids = $this->get_crosssell_ids($postmeta, $id);

                    if (empty($crosssell_ids)) {
                        return serialize($this->cross_sells);
                    }

                    foreach ($crosssell_ids as $id) {
                        $products_array = $this->set_products_array_by_id($id, $all_products);

                        if ($products_array) {
                            $postmeta_array = $this->set_postmeta_array_by_id($id, $all_postmeta);
                            $product_attributes = isset($postmeta_array['_product_attributes']) ? $postmeta_array['_product_attributes'] : '';

                            $this->cross_sells['enable_comparison'] = true;

                            $this->set_cross_sells_id($id);
                            $this->set_cross_sells_name($id, $products_array);
                            $this->set_cross_sells_slug($id, $products_array);
                            $this->set_cross_sells_subtitle($id, $postmeta_array);
                            $this->set_cross_sells_price($id, $postmeta_array, $variations);
                            $this->set_cross_sells_image($id, $postmeta_array, $variations);
                            $this->set_cross_sells_attributes($id, $product_attributes, $attributes_product_comparison, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_array, $variations);
                        }
                    }
                }
            }
        }

//        var_dump('-------------------------------------', $this->cross_sells);

        return serialize($this->cross_sells);
    }

    /**
     * @param $category_id
     * @return bool
     * В каком-то случае прилетает пустой массив, простая проверка, чтобы избежать ошибки
     */
    private function check_category_id($category_id) {
        return is_array($category_id) ? false : true;
    }

    /**
     * @param $category_id
     * @param $categories
     * @return bool|mixed
     * Определяем, у какой категории необходимо использовать атрибуты сравнения (у дочерней или родительской).
     */
    private function check_enable_comparison($category_id, $categories) {
        if (isset($categories[$category_id])) {
            if (isset($categories[$category_id]['enable_comparison'])) {
                if ($categories[$category_id]['enable_comparison'] == 1) {
                    return $category_id;
                } else {
                    return $categories[$category_id]['parent_id'];
                }

            }
        }

        return true;
    }

    /**
     * @param $category_id
     * @param $categories
     * @return bool
     * Костыльная проверка, если и у родительской категори нет необходимости сравнивать атрибуты, то собираем товары, как аксессуары.
     */
    private function second_check_enable_comparison($category_id, $categories) {
        if (isset($categories[$category_id])) {
            if (isset($categories[$category_id]['enable_comparison'])) {
                if ($categories[$category_id]['enable_comparison'] == 1) {
                    return false;
                } else {
                    return true;
                }

            }
        }
    }

    /**
     * @param $category_id
     * @param $categories
     * @return bool
     * Проверяем, есть ли у категории атрибуты для сравнения
     */
    private function check_attributes_product_comparison($category_id, $categories) {
        if (isset($categories[$category_id])) {
            if (isset($categories[$category_id]['attributes_product_comparison'])) {
                return true;
            }
        }
    }

    /**
     * @param $postmeta
     * @return bool
     * Проверяем, есть ли у данного товара в мета ключ _product_attributes.
     * Товар именно тот, к которому привязываются кроссейлы.
     */
    private function check_product_attributes($postmeta) {
        return isset($postmeta['_product_attributes']);
    }

    /**
     * @param $postmeta
     * @param $id
     * @return array|mixed
     * Получаем _crosssell_ids и добавляем в начало массива id товара, к которому привязывается кроссейл
     */
    private function get_crosssell_ids($postmeta, $id) {

        if (isset($postmeta)) {
//            $ids = isset($postmeta['_crosssell_ids']) ? unserialize($postmeta['_crosssell_ids']) : [];
            $ids = isset($postmeta['_upsell_ids']) ? unserialize($postmeta['_upsell_ids']) : [];
        }

        if (empty($ids)) {
            return [];
        }

        array_unshift($ids, (int)$id);

        return $ids;
    }

    public function set_products_array_by_id($id, $products) {
        return isset($products[$id]) ? $products[$id] : "";
    }

    public function set_postmeta_array_by_id($id, $postmeta) {
        return $postmeta[$id];
    }

    private function get_id($id) {
        return $id;
    }

    private function get_name($this_products) {
        return $this_products['post_title'];
    }

    private function get_slug($this_products) {
        return $this_products['post_name'];
    }

    private function get_subtitle($postmeta) {
        return isset($postmeta['_product_classification']) ? $postmeta['_product_classification'] : "";
    }

    private function get_price($postmeta_array, $variations) {
        $price = '';
        $default_variation_id = isset($postmeta_array['_default_variation_id']) ? $postmeta_array['_default_variation_id'] : "";
        if (!$default_variation_id) {
            return $price;
        }

        if (isset($variations[$default_variation_id])) {
            $data_variation = $variations[$default_variation_id];
            $price = isset($data_variation['regular_price']) ? $data_variation['regular_price'] : "";
        }

        return $price;
    }

    private function get_image($postmeta_array, $variations) {
        $image = [];

        $default_variation_id = isset($postmeta_array['_default_variation_id']) ? $postmeta_array['_default_variation_id'] : "";
        if (!$default_variation_id) {
            return $image;
        }

        if (isset($variations[$default_variation_id])) {
            $data_variation = $variations[$default_variation_id];
            $image = isset($data_variation['thumbnail']) ? $this->cross_sells_get_size_image($data_variation['thumbnail']) : [];
        }

        return $image;
    }

    private function cross_sells_get_size_image($image_string) {
        $data = [];
        $images = unserialize($image_string);
        $array_sizes = ['w300'];

        foreach ($array_sizes as $size) {
            $data[$size] = $images[$size] ?? "";
        }

        return serialize($data);
    }

    private function get_attributes($id, $product_attributes, $attributes_product_comparison, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_array, $variations) {
        $data = [];
        $attributes_is_variation_1 = [];

        if (empty($product_attributes)) {
            return $data;
        }

        $attributes = unserialize($product_attributes);

        $terms_ids = isset($relaishionships[$id]) ? $relaishionships[$id] : [];
        $array_attributes = $this->create_array_attributes($attributes_product_comparison);


        foreach ($attributes as $key_attribute => $attribute) {
            $name_attribute = $this->check_name_attributes($attribute['name']);

            if ($this->select_attribute($name_attribute, $array_attributes)) {
//                var_dump('+++++++++++++++++++++', $attribute['is_variation']);
                if ($attribute['is_variation'] == 0) {

                    $attributes_is_variation_0[] = $attribute;

                } else {

                    $attributes_is_variation_1[] = $attribute;

                }
            }
        }

        if (!empty($attributes_is_variation_0)) {
            $data[] = $this->get_static_attributes($terms_ids, $attributes_is_variation_0, $taxonomies, $terms, $woocommerce_attribute_taxonomies);
        }



        $data[] = $this->get_variation_attributes($postmeta_array, $variations, $attributes_is_variation_1);

        $data = $this->check_array($data);


//        var_dump($data);

        return $data;
    }

    private function get_variation_attributes($postmeta_array, $variations, $attributes) {
        $data = [];

        if (!empty($attributes)) {
            if (isset($postmeta_array['_default_variation_id']) && $postmeta_array['_default_variation_id'] !== 0) {
                if (isset($variations[$postmeta_array['_default_variation_id']])) {
                    foreach (unserialize($variations[$postmeta_array['_default_variation_id']]['attributes']) as $v_attribute) {
                        foreach ($attributes as $attribute) {
                            if (str_replace('attribute_', '', $v_attribute['taxonomy_slug']) == $attribute['name']) {
                                $data[$attribute['name']] = [
                                    'taxonomy_name' => isset($v_attribute['taxonomy_name']) ? $v_attribute['taxonomy_name'] : 0,
                                    'term_name' => isset($v_attribute['term_name']) ? $v_attribute['term_name'] : 0,
                                ];
                            }
                        }
                    }
                    $data['c_pa_dimensions'] = $this->get_c_pa_dimensions($postmeta_array, $variations);
                }
            } else {
                return [];
            }
        } else {
            $data['c_pa_dimensions'] = $this->get_c_pa_dimensions($postmeta_array, $variations);
        }


        return $data;
    }

    private function get_c_pa_dimensions($postmeta_array, $variations) {
        $data = [];

        if (isset($postmeta_array['_default_variation_id']) && $postmeta_array['_default_variation_id'] !== 0) {
            if (isset($variations[$postmeta_array['_default_variation_id']])) {
                $length = $this->variation_length($variations[$postmeta_array['_default_variation_id']]);
                $width = $this->variation_width($variations[$postmeta_array['_default_variation_id']]);
                $height = $this->variation_height($variations[$postmeta_array['_default_variation_id']]);
            }
        }

        if (!empty($length) && !empty($width) && !empty($height)) {
            $data = [
                'taxonomy_name' => 'Габариты',
                'term_name' => $width . '×' . $height . '×' . $length
            ];
        } elseif (empty($length) && !empty($width) && !empty($height)) {
            $data = [
                'taxonomy_name' => 'Габариты',
                'term_name' => $width . '×' . $height
            ];
        } elseif (!empty($length) && !empty($width) && empty($height)) {
            $data = [
                'taxonomy_name' => 'Габариты',
                'term_name' => $width . '×' . $length
            ];
        }



        return $data;
    }

    private function variation_length($variation) {
        return $variation['length'] ?? "";
    }

    private function variation_width($variation) {
        return $variation['width'] ?? "";
    }

    private function variation_height($variation) {
        return $variation['height'] ?? "";
    }

    /**
     * @param $attributes_product_comparison
     * @return array|false|string[]
     * Создается массив аттрибутов для сравнения (все имена без префикса pa_)
     */
    private function create_array_attributes($attributes_product_comparison) {
        $data = [];

        if (!empty($attributes_product_comparison)) {
            $str_attributes = str_replace(' ', '', $attributes_product_comparison);
            $data = explode(',', $str_attributes);
        }

        return $data;
    }

    /**
     * @param $attribute
     * @return false|string
     * Имя аттрибута прилетает с префиксом pa_, на выходе префикс отрезается.
     * Если префикса нет, то имя не меняется.
     */
    private function check_name_attributes($attribute) {

        if (strpos($attribute, 'pa_') === 0) {
            $x = substr($attribute, 3);
        } else {
            $x = $attribute;
        }

        return $x;
    }

    private function select_attribute($name_attribute, $array_attributes) {
        return in_array($name_attribute, $array_attributes);
    }

    /**
     * @param $array
     * @return mixed
     * Удалить пустые из результирующего массива
     */
    public function check_array($array) {

        foreach ($array as $key => $value) {
            if ($value === 0) {
                unset($array[$key]);
            }
        }

        if (empty($array)) {
            return "";
        }

        $data = [];
        foreach ($array as $k => $v) {
            foreach ($v as $e => $item) {
                $data[$e] = $item;
            }
        }

        return $data;
    }

    private function get_static_attributes($terms_ids, $attributes, $taxonomies, $terms, $woocommerce_attribute_taxonomies) {
        $data = [];

        foreach ($terms_ids as $term_id) {
            $taxonomy = $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];
            foreach ($attributes as $attribute) {

                if ($attribute['name'] == $taxonomy) {
                    $term = $terms[$taxonomies[$term_id['term_taxonomy_id']]['term_id']];

                    $data[$attribute['name']] = [
                        'taxonomy_name' => $woocommerce_attribute_taxonomies[str_replace('pa_', '', $taxonomy)],
                        'term_name' => $term['name']
                    ];

                }
            }
        }

        return $data;
    }

    private function set_cross_sells_id($id) {
        $this->cross_sells['items'][$id]['id'] = $this->get_id($id);
    }

    private function set_cross_sells_name($id, $this_products) {
        $this->cross_sells['items'][$id]['name'] = $this->get_name($this_products);
    }

    private function set_cross_sells_slug($id, $this_products) {
        $this->cross_sells['items'][$id]['slug'] = $this->get_slug($this_products);
    }

    private function set_cross_sells_subtitle($id, $postmeta) {
        $this->cross_sells['items'][$id]['subtitle'] = $this->get_subtitle($postmeta);
    }

    private function set_cross_sells_price($id, $postmeta_array, $variations) {
        $this->cross_sells['items'][$id]['price'] = $this->get_price($postmeta_array, $variations);
    }

    private function set_cross_sells_image($id, $postmeta_array, $variations) {
        $this->cross_sells['items'][$id]['image'] = $this->get_image($postmeta_array, $variations);
    }

    private function set_cross_sells_attributes($id, $product_attributes, $attributes_product_comparison, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_array, $variations) {
        $this->cross_sells['items'][$id]['attributes'] = $this->get_attributes($id, $product_attributes, $attributes_product_comparison, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_array, $variations);
    }

}