<?php


class GetCrossSells {

    private $cross_sells;

    public function __construct() {

        $this->cross_sells = [];

    }

    public function get_crosssells($id, $postmeta, $category_id, $categories, $variations, $all_products, $all_postmeta, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies) {

        if ($this->check_category_id($category_id)) {

            if ($this->check_attributes_product_comparison($category_id, $categories)) {

                    if ($this->check_product_attributes($postmeta)) {

                        $attributes_product_comparison = $categories[$category_id]['attributes_product_comparison'];
//                        var_dump('-----------------', $attributes_product_comparison);
//                        var_dump('----------', $category_id);
                        $product_attributes = isset($postmeta['_product_attributes']) ? $postmeta['_product_attributes'] : '';
                        $crosssell_ids = $this->get_crosssell_ids($postmeta, $id);

                        if (empty($crosssell_ids)) {
                            return serialize($this->cross_sells);
                        }

                        foreach ($crosssell_ids as $id) {
                            $products_array = $this->set_products_array_by_id($id, $all_products);
                            if ($products_array) {
                                $postmeta_array = $this->set_postmeta_array_by_id($id, $all_postmeta);

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

//        var_dump('-----', $this->cross_sells);

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

        if (empty($product_attributes)) {
            return $data;
        }

        $attributes = unserialize($product_attributes);

        $terms_ids = isset($relaishionships[$id]) ? $relaishionships[$id] : [];
        $array_attributes = $this->create_array_attributes($attributes_product_comparison);
        foreach ($attributes as $key_attribute => $attribute) {
            $name_attribute = $this->check_name_attributes($attribute['name']);
            if ($this->select_attribute($name_attribute, $array_attributes)) {
                if ($attribute['is_variation'] == 0) {
                    $data[$attribute['name']] = !empty($this->get_static_attributes($terms_ids, $attribute, $taxonomies, $terms, $woocommerce_attribute_taxonomies)) ? $this->get_static_attributes($terms_ids, $attribute, $taxonomies, $terms, $woocommerce_attribute_taxonomies) : 0;

                    break;
                } else {
                    var_dump('88888', $postmeta_array);
                    if (isset($postmeta_array['_default_variation_id']) && $postmeta_array['_default_variation_id'] !== 0) {
                        var_dump('++++++++++++++');
                        if (isset($variations[$postmeta_array['_default_variation_id']])) {
                            foreach (unserialize($variations[$postmeta_array['_default_variation_id']]['attributes']) as $v_attribute) {
                                if (str_replace('attribute_', '', $v_attribute['taxonomy_slug']) == $attribute['name']) {
                                    $data[$attribute['name']] = [
                                        'taxonomy_name' => isset($v_attribute['taxonomy_name']) ? $v_attribute['taxonomy_name'] : 0,
                                        'term_name' => isset($v_attribute['term_name']) ? $v_attribute['term_name'] : 0,
                                    ];

                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        $data = $this->check_array($data);

        return $data;
    }

    private function create_array_attributes($attributes_product_comparison) {
        $data = [];

        if (!empty($attributes_product_comparison)) {
            $str_attributes = str_replace(' ', '', $attributes_product_comparison);
            $data = explode(',', $str_attributes);
        }

        return $data;
    }

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

        return $array;
    }

    private function get_static_attributes($terms_ids, $attributes, $taxonomies, $terms, $woocommerce_attribute_taxonomies) {
        $data = [];

        foreach ($terms_ids as $term_id) {
            $taxonomy = $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];
            if ($attributes['name'] == $taxonomy) {
                $term = $terms[$taxonomies[$term_id['term_taxonomy_id']]['term_id']];

                $data['taxonomy_name'] = $woocommerce_attribute_taxonomies[str_replace('pa_', '', $taxonomy)];
                $data['term_name'] = $term['name'];

            }
        }

        return $data;
    }

    private function set_cross_sells_id($id) {
        $this->cross_sells[$id]['id'] = $this->get_id($id);
    }

    private function set_cross_sells_name($id, $this_products) {
        $this->cross_sells[$id]['name'] = $this->get_name($this_products);
    }

    private function set_cross_sells_slug($id, $this_products) {
        $this->cross_sells[$id]['slug'] = $this->get_slug($this_products);
    }

    private function set_cross_sells_subtitle($id, $postmeta) {
        $this->cross_sells[$id]['subtitle'] = $this->get_subtitle($postmeta);
    }

    private function set_cross_sells_price($id, $postmeta_array, $variations) {
        $this->cross_sells[$id]['price'] = $this->get_price($postmeta_array, $variations);
    }

    private function set_cross_sells_image($id, $postmeta_array, $variations) {
        $this->cross_sells[$id]['image'] = $this->get_image($postmeta_array, $variations);
    }

    private function set_cross_sells_attributes($id, $product_attributes, $attributes_product_comparison, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_array, $variations) {
        $this->cross_sells[$id]['attributes'] = $this->get_attributes($id, $product_attributes, $attributes_product_comparison, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_array, $variations);
    }

}