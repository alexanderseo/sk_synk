<?php
require_once 'helpers/default_attributes.php';
require_once 'helpers/static_attributes.php';
require_once 'helpers/variable_attributes.php';
require_once 'helpers/general_helpers.php';
require_once 'helpers/variations_helpers.php';
require_once 'helpers/FilterTypeProduct.php';

class sk_stock_products extends bootstrap {
    private static $instance;

    use default_attributes;
    use static_attributes;
    use general_helpers;
    use variable_attributes;
    use variations_helpers;

    private $log;
    private $ids_products;
    private $stock_products;
    private $relationships;
    private $term_taxonomy;
    private $terms;
    private $termmeta;
    private $posts;
    private $postmeta;
    private $woocommerce_attribute;
    private $terms_by_slug;
    private $attachments;
    private $posts_by_post_name;
    private $filterHelper;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = [];
        $this->ids_products = $wordpress['posts_ids']['products'];
        $this->relationships = $wordpress['term_relationships'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];
        $this->posts = $wordpress['posts'];
        $this->posts_by_post_name = $wordpress['posts_by_post_name'];
        $this->terms = $wordpress['terms'];
        $this->postmeta = $wordpress['postmeta'];
        $this->woocommerce_attribute = $wordpress['woocommerce_attribute_taxonomies'];
        $this->terms_by_slug = $wordpress['terms_by_slug'];
        $this->termmeta = $wordpress['termmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->filterHelper = new FilterTypeProduct($this->relationships, $this->term_taxonomy, $this->terms);
        $this->stock_products = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get($fabrics, $materials) {

        foreach ($this->ids_products as $id) {
            if ($this->check_stock_category($id, $this->relationships, $this->term_taxonomy)) {
//                $id = 193883;
                $posts = $this->posts[$id];
                $postmeta = $this->set_postmeta_array_by_id($id, $this->postmeta);
                $relashionships_array = $this->set_relashions_array_by_id($id, $this->relationships);

                $id_prototype = $this->get_prototype($postmeta);


                if ($id_prototype) {
                    if ($this->filterHelper->filter_simple_product($id_prototype)) {
                        $postmeta_prototype = $this->set_postmeta_array_by_id($id_prototype, $this->postmeta);
                        $relashionships_array_prototype = $this->set_relashions_array_by_id($id_prototype, $this->relationships);
                    } else {
                        $postmeta_prototype = 'simple_filter';
                    }
                } else {
                    $postmeta_prototype = "";
                    $relashionships_array_prototype = "";
                }

                if ($this->check_simple_filter($postmeta_prototype)) {
                    $this->set_id('id', $id);
                    $this->set_slug('slug', $id, $posts);
                    $this->set_name('name', $id, $posts);
                    $this->set_category('category_id', $id, $this->relationships, $this->terms, $this->term_taxonomy);
                    $this->set_category_id('category_id', $id, $this->relationships, $this->term_taxonomy);
                    $this->set_product_attributes('product_attributes', $id, $postmeta);
                    $this->set_subtitle('subtitle', $id, $postmeta);
                    $this->set_collection_id('collection_id', $id, $postmeta);
                    $this->set_default_variation_id('default_variation_id', $id, $postmeta);
                    $this->set_default_attributes('default_attributes', $id, $postmeta, $this->woocommerce_attribute, $this->terms_by_slug);
                    $this->set_static_attributes($id, $id_prototype, $postmeta_prototype, $relashionships_array_prototype, $this->term_taxonomy, $this->terms, $this->woocommerce_attribute, $this->termmeta);
                    $this->set_attributes($id, $postmeta, $fabrics, $relashionships_array, $this->term_taxonomy, $this->terms, $this->woocommerce_attribute, $this->postmeta, $this->termmeta, $materials);
                    $this->set_stock($id, $postmeta);
                    $this->set_variations($id, $fabrics, $this->posts, $this->postmeta, $this->attachments, $this->posts_by_post_name,$this->terms_by_slug, $this->woocommerce_attribute, $this->terms, $postmeta_prototype);
                    $this->set_prototype($id, $postmeta);
                }


            }
        }

        /**
         * PHP 7.2
         */
        $clear_array = array_filter($this->stock_products, function($k) {
            return !empty($k);
        }, ARRAY_FILTER_USE_KEY);

        /**
         * PHP 7.4
         */
//        $clear_array = array_filter($this->stock_products,
//            fn ($key) => !empty($key),
//            ARRAY_FILTER_USE_KEY);

//        var_dump($clear_array);

        return $clear_array;
    }

    /**
     * @param $id
     * @return bool|string
     * ???????? ?? ???????????? ???????? ?????????????????? 7681, ???? ???? ???????????? ???????????????? ???? ??????????????
     */
    private function check_stock_category($id, $relationships, $taxonomies) {
        $status = "";

        foreach ($relationships[$id] as $relationship) {
            if ($taxonomies[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                if ($taxonomies[$relationship['term_taxonomy_id']]['term_id'] == "7681") {
                    $status = true;

                    break;
                } else {
                    $status = false;
                }
            } else {
                $status = false;
            }
        }

        return $status;
    }

    private function check_simple_filter($postmeta) {
        if ($postmeta != 'simple_filter') return true;
    }

    private function get_id($id) {
        return (int)$id;
    }

    private function get_slug($array) {
        return $array['post_name'];
    }

    private function get_name($array) {
        return $array['post_title'];
    }

    /**
     * @param $id
     * @return array|mixed
     * ???????????????????? ?????????????????? ?????? ???????????? ?? ???????????????????????? ?????????????????? stock
     * ?????????? ?????? ?????????? ?? stock, ???? ???????? ???????????????????? ???????????????? ??????????????????
     */
    private function get_category($id, $relationships, $terms, $taxonomies) {
        $array = [];

        foreach ($relationships[$id] as $relationship) {
            if ($taxonomies[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                if ($taxonomies[$relationship['term_taxonomy_id']]['parent'] !== "0") {
                    $array[] = $taxonomies[$relationship['term_taxonomy_id']];
                }
            }
        }

        $category_id = [];
        foreach ($array as $item) {
            if (isset($item['term_id'])) {
                if ($item['parent'] != '0') {
                    $category_id = $item['term_id'];
                } else {
                    $category_id = $item['term_id'];
                }
            }
        }

        $category = [];
        $category['name'] = $terms[$category_id]['name'];
        $category['slug'] = $terms[$category_id]['slug'];

        return serialize($category);
    }

    private function get_category_child_id($id, $relationships, $taxonomies) {
        $child_array = [];

        foreach ($relationships[$id] as $relationship) {
            if ($taxonomies[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                if ($taxonomies[$relationship['term_taxonomy_id']]['parent'] == "0") {
                    if ($taxonomies[$relationship['term_taxonomy_id']]['term_id'] !== "7681") {
                        $parent_id = $taxonomies[$relationship['term_taxonomy_id']]['term_id'];
                        foreach ($relationships[$id] as $relationship_item) {
                            if ($taxonomies[$relationship_item['term_taxonomy_id']]['parent'] == $parent_id) {
                                $child_array[] = $taxonomies[$relationship_item['term_taxonomy_id']]['term_id'];
                            }
                        }
                    }
                }
            }
        }

        $category_id = isset($child_array[0]) ? $child_array[0] : "";

        return $category_id;
    }

    private function get_product_attributes($postmeta) {
        return $postmeta['_product_attributes'];
    }

    private function get_subtitle($postmeta) {
        return $postmeta['_product_classification'];
    }

    private function get_collection_id($postmeta) {
        return $postmeta['_product_collection'];
    }

    private function get_default_variation_id($postmeta) {
        return $postmeta['_default_variation_id'];
    }

    private function get_attributes($product_attributes) {
        $data = [];

        foreach ($product_attributes as $attribute) {
            if ($attribute['is_variation'] == 0) {
                $data['static'][$attribute['name']] = $attribute['name'];
            } else {
                $data['variable'][$attribute['name']] = $attribute['name'];
            }
        }

        return $data;
    }

    private function get_stock($postmeta) {
        $stock_array = [];

        foreach ($postmeta as $key => $value) {
            if (strpos($key, 'stock_') === 0) {
                preg_match('/\d+/', $key, $tmp);

                $stock_array[$tmp[0]][$key] = $value;
            }
        }

        $body_stock = ['isOutlet' => 0];

        foreach ($stock_array as $item_key => $item_value) {
            foreach ($item_value as $key_item => $value_item) {
                if (strpos($key_item, 'location') == 14) {
                    $body_stock['entities'][$item_key]['location'] = $this->get_location($value_item);
                } elseif (strpos($key_item, 'amount') == 14) {
                    $body_stock['entities'][$item_key]['amount'] = $value_item;
                }
            }
        }

        return serialize($body_stock);
    }

    private function get_outlet($postmeta) {

        $outlet_array = [];

        foreach ($postmeta as $key => $value) {
            if (strpos($key, 'outlet_') === 0) {
                preg_match('/\d+/', $key, $tmp);

                $outlet_array[$tmp[0]][$key] = $value;
            }
        }

        $body_outlet = ['isOutlet' => 1];

        foreach ($outlet_array as $item_key => $item_value) {
            foreach ($item_value as $key_item => $value_item) {
                if (strpos($key_item, 'location') == 16) {
                    $body_outlet['entities'][$item_key]['location'] = $this->get_location($value_item);
                } elseif (strpos($key_item, 'reason') == 16) {
                    $body_outlet['entities'][$item_key]['reason'] = $value_item;
                }
            }
        }

        return serialize($body_outlet);
    }

    /**
     * @param $id_location
     * @return array
     * ?????????????????? ???????? ?????? location
     * TODO ?????????? ?????????????????? ????????????
     */
    private function get_location($id_location) {
        global $wordpress;
        $location = [];
        $location['id'] = $wordpress['posts'][$id_location]['ID'];
        $location['name'] = $wordpress['posts'][$id_location]['post_title'];
        $location['slug'] = $wordpress['posts'][$id_location]['post_name'];
        $location['address'] = $wordpress['postmeta'][$id_location]['showroom-address'];
        $location['city'] = $this->get_city($wordpress['posts'][$id_location]['ID']);
        $location['station'] = $wordpress['postmeta'][$id_location]['showroom-subway_showroom-subway-station'];
        $location['color'] = $wordpress['postmeta'][$id_location]['showroom-subway_showroom-subway-color'];
        $location['distance'] = $wordpress['postmeta'][$id_location]['showroom-subway_showroom-subway-distance'];

        return $location;
    }

    /**
     * @param $id
     * @return array
     * ???????????????? ???????????????? ?? slug ?????? location
     * TODO ?????????? ??????????????????
     */
    private function get_city($id) {
        global $wordpress;
        $data = [];

        if (isset($wordpress['postmeta'][$id]['showroom-city'])) {
            if (empty($wordpress['postmeta'][$id]['showroom-city'])) {
                $data = [];

            } else {
                if (isset($wordpress['terms'][$wordpress['postmeta'][$id]['showroom-city']]['name'])) {
                    if (empty($wordpress['terms'][$wordpress['postmeta'][$id]['showroom-city']]['name'])) {
                        $data['name'] = 0;

                    } else {
                        $data['name'] = $wordpress['terms'][$wordpress['postmeta'][$id]['showroom-city']]['name'];
                    }
                }

                if (isset($wordpress['terms'][$wordpress['postmeta'][$id]['showroom-city']]['slug'])) {
                    if (empty($wordpress['terms'][$wordpress['postmeta'][$id]['showroom-city']]['slug'])) {
                        $data['slug'] = 0;

                    } else {
                        $data['slug'] = $wordpress['terms'][$wordpress['postmeta'][$id]['showroom-city']]['slug'];
                    }
                }
            }
        }

        return $data;
    }

    private function get_variations($id, $fabrics, $posts, $all_postmeta, $attachments, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms, $postmeta_prototype) {
        $variations = [];

        foreach ($posts as $post) {
            if ($post['post_parent'] == $id && $post['post_type'] == 'product_variation') {
                $postmeta_variation = $this->set_postmeta_array_by_id($post['ID'], $all_postmeta);

                $variations[$post['ID']]['id'] = $post['ID'];
                $variations[$post['ID']]['regular_price'] = $this->get_price($postmeta_variation);
                $variations[$post['ID']]['sale_price'] = $this->get_sale_price($postmeta_variation);
                $variations[$post['ID']]['product_discount'] = $this->get_discount($postmeta_variation);
                $variations[$post['ID']]['sku'] = $this->get_sku($postmeta_variation);
                $variations[$post['ID']]['general_image'] = $this->get_thumbnail($postmeta_variation, $attachments);
                $variations[$post['ID']]['gallery'] = $this->get_gallery($postmeta_prototype, $attachments);
                $variations[$post['ID']]['drawing'] = $this->get_drawing($postmeta_prototype, $attachments);
                $variations[$post['ID']]['variation_attributes'] = $this->get_attributes_variations($fabrics, $postmeta_variation, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms, $all_postmeta);
            }
        }

        return serialize($variations);
    }

    private function set_id($key, $id) {
        $this->stock_products[$id][$key] = $this->get_id($id);
    }

    private function set_slug($key, $id, $array) {
        $this->stock_products[$id][$key] = $this->get_slug($array);
    }

    private function set_name($key, $id, $array) {
        $this->stock_products[$id][$key] = $this->get_name($array);
    }

    private function set_category($key, $id, $relationships, $terms, $taxonomies) {
        $this->stock_products[$id]['category'] = $this->get_category($id, $relationships, $terms, $taxonomies);
    }

    private function set_category_id($key, $id, $relationships, $taxonomies) {
        $this->stock_products[$id]['category_id'] = $this->get_category_child_id($id, $relationships, $taxonomies);
    }

    private function set_product_attributes($key, $id, $postmeta) {
        if ($this->has_meta($id, '_product_attributes', $key, $postmeta)) {
            $this->stock_products[$id][$key] = $this->get_product_attributes($postmeta);
        }
    }

    private function set_subtitle($key, $id, $postmeta) {
        if ($this->has_meta($id, '_product_classification', $key, $postmeta)) {
            $this->stock_products[$id][$key] = $this->get_subtitle($postmeta);
        }
    }

    private function set_collection_id($key, $id, $postmeta) {
        if ($this->has_meta($id, '_product_collection', $key, $postmeta)) {
            $this->stock_products[$id][$key] = $this->get_collection_id($postmeta);
        }
    }

    private function set_default_attributes($key, $id, $postmeta, $woocommerce_attribute, $terms_by_slug) {
        if ($this->has_meta($id, '_default_attributes', $key, $postmeta)) {
            $this->stock_products[$id][$key] = $this->get_default_attributes($postmeta, $woocommerce_attribute, $terms_by_slug);
        }
    }

    private function set_default_variation_id($key, $id, $postmeta) {
        if ($this->has_meta($id, '_default_variation_id', $key, $postmeta)) {
            $this->stock_products[$id][$key] = $this->get_default_variation_id($postmeta);
        }
    }

    private function set_static_attributes($id, $id_prototype, $postmeta, $relashionships_array, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta) {
        if ($this->has_meta($id_prototype, '_product_attributes', ['static_attributes', 'variable_attributes'], $postmeta)) {
            $attributes = $this->get_attributes(unserialize($postmeta['_product_attributes']));

            $this->stock_products[$id]['static_attributes'] = $this->get_static_attributes($relashionships_array, $attributes, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta);
        }
    }

    /**
     * @param $id
     * @param $postmeta
     * @param $fabrics
     * @param $relashionships_array
     * @param $taxonomy
     * @param $terms
     * @param $woocommerce_attribute_taxonomies
     * @param $postmeta_all
     * @param $termmeta
     * ???? product_attributes ???????????????? ?????? ??????????????????, ???? ?? ???????????? stock ???????? ???????????? variable_attributes. ?????????????????? ?????????????????? ???????????? ?? ?????? ??????????????????.
     */
    private function set_attributes($id, $postmeta, $fabrics, $relashionships_array, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials) {

        if ($this->has_meta($id, '_product_attributes', ['static_attributes', 'variable_attributes'], $postmeta)) {
            $attributes = $this->get_attributes(unserialize($postmeta['_product_attributes']));

            $this->stock_products[$id]['variable_attributes'] = $this->get_variable_attributes($relashionships_array, $attributes, $fabrics, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials);
        }
    }

    /**
     * @param $id
     * ????????????????????, ?????? ?????????????????? ??????????
     * ???????? ?? outlet, ???? ???????????????? ???????? ???????? get_outlet
     * ???????? ?? stock, ???? ???????????????? ???????????? ???????? get_stock
     */
    private function set_stock($id, $postmeta) {
        if ($this->check_outlet($postmeta)) {
            $this->stock_products[$id]['stock'] = $this->get_stock($postmeta);
        } else {
            $this->stock_products[$id]['stock'] = $this->get_outlet($postmeta);
        }
    }

    /**
     * @param $id
     * @return bool
     * ?????????????????? ???? is-outlet
     */
    private function check_outlet($postmeta) {

        $response = $postmeta['is-outlet'] == "0";

        return $response;
    }

    private function set_variations($id, $fabrics, $posts, $all_postmeta, $attachments, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms, $postmeta_prototype) {
        $this->stock_products[$id]['variations'] = $this->get_variations($id, $fabrics, $posts, $all_postmeta, $attachments, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms, $postmeta_prototype);
    }

    private function get_prototype($postmeta) {
        $prototype_id = unserialize($postmeta['prototype']);

        if (empty($prototype_id)) {
            return "";
        }

        return $prototype_id[0];
    }

    private function set_prototype($id, $postmeta) {
        $this->stock_products[$id]['prototype'] = $this->get_prototype($postmeta);
    }

    private function has_meta($id, $key, $key_new, $postmeta, $additional_verification = true, $log = true) {

        if (isset($postmeta[$key]) && $additional_verification) {
            if (!empty($postmeta[$key])) {
                return true;
            } else {
                if (is_array($key_new)) {
                    foreach ($key_new as $key_new_item) {
                        $this->stock_products[$id][$key_new_item] = 0;
                    }
                } else {
                    $this->stock_products[$id][$key_new] = 0;
                }

                if ($log) {
                    $this->log[] = array('??????????', $id, $key, 'EMPTY');
                }

                return false;
            }
        } else {
            if (is_array($key_new)) {
                foreach ($key_new as $key_new_item) {
                    $this->stock_products[$id][$key_new_item] = 0;
                }
            } else {
                $this->stock_products[$id][$key_new] = 0;
            }

            if ($log) {
                $this->log[] = array('??????????', $id, $key, 'NOT_EXIST');
            }

            return false;
        }
    }


}
