<?php

require_once 'helpers/default_attributes.php';
require_once 'helpers/static_attributes.php';
require_once 'helpers/variable_attributes.php';
require_once 'helpers/general_helpers.php';
require_once 'helpers/variations_helpers.php';


class sk_expo_products extends bootstrap {
    private static $instance;

    use default_attributes;
    use static_attributes;
    use general_helpers;
    use variable_attributes;
    use variations_helpers;

    private $log;
    private $ids_products;
    private $expo_products;
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
        $this->expo_products = [];

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
                $posts = $this->posts[$id];
                $postmeta = $this->set_postmeta_array_by_id($id, $this->postmeta);
                $relashionships_array = $this->set_relashions_array_by_id($id, $this->relationships);
                $id_prototype = $this->get_prototype($postmeta);

                if ($id_prototype) {
                    $postmeta_prototype = $this->set_postmeta_array_by_id($id_prototype, $this->postmeta);
                    $relashionships_array_prototype = $this->set_relashions_array_by_id($id_prototype, $this->relationships);
                } else {
                    $postmeta_prototype = "";
                    $relashionships_array_prototype = "";
                }

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
                $this->set_static_attributes($id, $id_prototype, $postmeta_prototype, $relashionships_array_prototype, $this->term_taxonomy, $this->terms, $this->woocommerce_attribute);
                $this->set_attributes($id, $postmeta, $fabrics, $relashionships_array, $this->term_taxonomy, $this->terms, $this->woocommerce_attribute, $this->postmeta, $this->termmeta, $materials);
                $this->set_expo_data($id, $postmeta);
                $this->set_variations($id, $fabrics, $this->posts, $this->postmeta, $this->attachments, $this->posts_by_post_name,$this->terms_by_slug, $this->woocommerce_attribute, $this->terms);
                $this->set_prototype($id, $postmeta);
            }
        }

//        var_dump($this->expo_products);

        return $this->expo_products;
    }

    /**
     * @param $id
     * @return bool|string
     * Если у товара есть категория 7986, то он прошел проверку на экспозицию
     */
    private function check_stock_category($id, $relationships, $taxonomies) {
        $status = "";

        foreach ($relationships[$id] as $relationship) {
            if ($taxonomies[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                if ($taxonomies[$relationship['term_taxonomy_id']]['term_id'] == "7986") {
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
     * Определяем категорию для товара в родительской категории stock
     * Товар уже попал в stock, но надо определить дочернюю категорию
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
            if (strpos($key, 'locations_') === 0) {
                preg_match('/\d+/', $key, $tmp);
                $outlet_array[$tmp[0]][$key] = $value;
            }
        }

        $body_outlet = ['isExpo' => 1];

        foreach ($outlet_array as $item_key => $item_value) {
            foreach ($item_value as $key_item => $value_item) {
                if (strpos($key_item, 'showroom') == 12) {
                    $body_outlet['entities'][$item_key]['location'] = $this->get_location($value_item);
                } elseif (strpos($key_item, 'amount') == 12) {
                    $body_outlet['entities'][$item_key]['amount'] = $value_item;
                }
            }
        }

        return serialize($body_outlet);
    }

    /**
     * @param $id_location
     * @return array
     * Формируем тело для location
     * TODO можно уменьшить массив
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
     * Получаем название и slug для location
     * TODO можно упростить
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

    private function get_variations($id, $fabrics, $posts, $all_postmeta, $attachments, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms) {
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
                $variations[$post['ID']]['gallery'] = $this->get_gallery($postmeta_variation, $attachments);
                $variations[$post['ID']]['drawing'] = $this->get_drawing($postmeta_variation, $attachments);
                $variations[$post['ID']]['variation_attributes'] = $this->get_attributes_variations($fabrics, $postmeta_variation, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms, $all_postmeta);
            }
        }

        return serialize($variations);
    }

    private function set_id($key, $id) {
        $this->expo_products[$id][$key] = $this->get_id($id);
    }

    private function set_slug($key, $id, $array) {
        $this->expo_products[$id][$key] = $this->get_slug($array);
    }

    private function set_name($key, $id, $array) {
        $this->expo_products[$id][$key] = $this->get_name($array);
    }

    private function set_category($key, $id, $relationships, $terms, $taxonomies) {
        $this->expo_products[$id]['category'] = $this->get_category($id, $relationships, $terms, $taxonomies);
    }

    private function set_category_id($key, $id, $relationships, $taxonomies) {
        $this->expo_products[$id]['category_id'] = $this->get_category_child_id($id, $relationships, $taxonomies);
    }

    private function set_product_attributes($key, $id, $postmeta) {
        if ($this->has_meta($id, '_product_attributes', $key, $postmeta)) {
            $this->expo_products[$id][$key] = $this->get_product_attributes($postmeta);
        }
    }

    private function set_subtitle($key, $id, $postmeta) {
        if ($this->has_meta($id, '_product_classification', $key, $postmeta)) {
            $this->expo_products[$id][$key] = $this->get_subtitle($postmeta);
        }
    }

    private function set_collection_id($key, $id, $postmeta) {
        if ($this->has_meta($id, '_product_collection', $key, $postmeta)) {
            $this->expo_products[$id][$key] = $this->get_collection_id($postmeta);
        }
    }

    private function set_default_attributes($key, $id, $postmeta, $woocommerce_attribute, $terms_by_slug) {
        if ($this->has_meta($id, '_default_attributes', $key, $postmeta)) {
            $this->expo_products[$id][$key] = $this->get_default_attributes($postmeta, $woocommerce_attribute, $terms_by_slug);
        }
    }

    private function set_default_variation_id($key, $id, $postmeta) {
        if ($this->has_meta($id, '_default_variation_id', $key, $postmeta)) {
            $this->expo_products[$id][$key] = $this->get_default_variation_id($postmeta);
        }
    }

    private function set_static_attributes($id, $id_prototype, $postmeta, $relashionships_array, $taxonomy, $terms, $woocommerce_attribute_taxonomies) {
        if ($id_prototype) {
            if ($this->has_meta($id_prototype, '_product_attributes', ['static_attributes', 'variable_attributes'], $postmeta)) {
                $attributes = $this->get_attributes(unserialize($postmeta['_product_attributes']));

                $this->expo_products[$id]['static_attributes'] = $this->get_static_attributes($relashionships_array, $attributes, $taxonomy, $terms, $woocommerce_attribute_taxonomies);
            }
        } else {
            $this->expo_products[$id]['static_attributes'] = "";
        }
    }

    private function set_attributes($id, $postmeta, $fabrics, $relashionships_array, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials) {

        if ($this->has_meta($id, '_product_attributes', ['static_attributes', 'variable_attributes'], $postmeta)) {
            $attributes = $this->get_attributes(unserialize($postmeta['_product_attributes']));

            $this->expo_products[$id]['variable_attributes'] = $this->get_variable_attributes($relashionships_array, $attributes, $fabrics, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials);
        }
    }

    private function get_ids_showrooms($data_expo) {
        $ids ="";
        $ids_array = [];
        $expo = unserialize($data_expo);

        if (!isset($expo['entities'])) return "";

        foreach ($expo['entities'] as $item_location) {
            $ids_array[] = $item_location['location']['id'];
        }

        $ids = '_' . implode('_', $ids_array) . '_';

        return $ids;
    }

    /**
     * @param $id
     * Определяем, где находится товар
     * Если в outlet, то собираем одно тело get_outlet
     * Если в stock, то собираем другое тело get_stock
     */
    private function set_expo_data($id, $postmeta) {
        if ($this->check_outlet($postmeta)) {
            $this->expo_products[$id]['stock'] = $this->get_stock($postmeta);
        } else {
            $data_expo = $this->get_outlet($postmeta);
            $this->expo_products[$id]['expo'] = $data_expo;
            $this->expo_products[$id]['ids_showrooms'] = $this->get_ids_showrooms($data_expo);
        }
    }

    /**
     * @param $id
     * @return bool
     * Проверяем на is-outlet
     */
    private function check_outlet($postmeta) {

        if (isset($postmeta['is-outlet'])) {
            $response = $postmeta['is-outlet'] == "0";
        } else {
            $response = false;
        }

        return $response;
    }

    private function set_variations($id, $fabrics, $posts, $all_postmeta, $attachments, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms) {
        $this->expo_products[$id]['variations'] = $this->get_variations($id, $fabrics, $posts, $all_postmeta, $attachments, $posts_by_post_name,$terms_by_slug, $woocommerce_attribute, $terms);
    }

    private function get_prototype($postmeta) {
        $prototype_id = unserialize($postmeta['prototype']);

        if (empty($prototype_id)) {
            return "";
        }

        return $prototype_id[0];
    }

    private function set_prototype($id, $postmeta) {
        $this->expo_products[$id]['prototype'] = $this->get_prototype($postmeta);
    }

    private function has_meta($id, $key, $key_new, $postmeta, $additional_verification = true, $log = true) {

        if (isset($postmeta[$key]) && $additional_verification) {
            if (!empty($postmeta[$key])) {
                return true;
            } else {
                if (is_array($key_new)) {
                    foreach ($key_new as $key_new_item) {
                        $this->expo_products[$id][$key_new_item] = 0;
                    }
                } else {
                    $this->expo_products[$id][$key_new] = 0;
                }

                if ($log) {
                    $this->log[] = array('Товар', $id, $key, 'EMPTY');
                }

                return false;
            }
        } else {
            if (is_array($key_new)) {
                foreach ($key_new as $key_new_item) {
                    $this->expo_products[$id][$key_new_item] = 0;
                }
            } else {
                $this->expo_products[$id][$key_new] = 0;
            }

            if ($log) {
                $this->log[] = array('Товар', $id, $key, 'NOT_EXIST');
            }

            return false;
        }
    }
}