<?php

require_once 'helpers/general_helpers.php';
require_once 'helpers/variations_helpers.php';

class sk_variations extends bootstrap {

    private static $instance;

    use general_helpers;
    use variations_helpers;

    private $variations;
    private $all_posts;
    private $all_posts_ids;
    private $relashionships;
    private $generalHelper;
    private $postmeta;
    private $attachments;
    private $posts_by_post_name;
    private $terms_by_slug;
    private $woocommerce_attribute_taxonomies;
    private $termmeta;
    private $terms;

    public function __construct() {
        parent::__construct();
        global $wordpress;

        $this->variations = [];
//        $this->all_posts = $wordpress['posts'];
        $this->all_posts = $wordpress['posts_variations'];
        $this->all_posts_ids = $wordpress['posts_ids'];
        $this->relashionships = $wordpress['term_relationships'];
        $this->postmeta = $wordpress['postmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->posts_by_post_name = $wordpress['posts_by_post_name'];
        $this->terms_by_slug = $wordpress['terms_by_slug'];
        $this->woocommerce_attribute_taxonomies = $wordpress['woocommerce_attribute_taxonomies'];
        $this->termmeta = $wordpress['termmeta'];
        $this->terms = $wordpress['terms'];

    }


    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get($fabrics) {

        if (isset($this->all_posts_ids['variations'])) {
            foreach ($this->all_posts_ids['variations'] as $id) {
//                $id = 164544;
//                $id = 164825;

                $posts_array = $this->set_products_array_by_id($id, $this->all_posts);
                $relashionships_array = $this->set_relashions_array_by_id($id, $this->relashionships);
                $postmeta_array = $this->set_postmeta_array_by_id($id, $this->postmeta);

                if ($this->check_visible_variation($postmeta_array)) {

                    $this->set_id($id);
                    $this->set_parent_id($id, $posts_array);
                    $this->set_sku($id, $postmeta_array);
                    $this->set_regular_price($id, $postmeta_array);
                    $this->set_sale_price($id, $postmeta_array);
                    $this->set_thumbnail($id, $postmeta_array, $this->attachments);
                    $this->set_gallery($id, $postmeta_array, $this->attachments);
                    $this->set_fabric_id($id, $postmeta_array);
                    $this->set_drawing($id, $postmeta_array, $this->attachments);
                    $this->set_length($id, $postmeta_array);
                    $this->set_width($id, $postmeta_array);
                    $this->set_height($id, $postmeta_array);
                    $this->set_weight($id, $postmeta_array);
                    $this->set_sleep_area($id, $postmeta_array);
                    $this->set_attributes($id, $this->posts_by_post_name, $postmeta_array, $this->terms_by_slug, $this->woocommerce_attribute_taxonomies, $fabrics, $this->termmeta, $this->terms, $this->attachments, $this->postmeta);
                    $this->set_stock($id, $postmeta_array);

                }
            }
        }

//        var_dump('-----------', $this->variations);

        return $this->variations;
    }

    private function check_visible_variation($this_postmeta) {
        if (isset($this_postmeta['_product_hidden'])) {
            if ($this_postmeta['_product_hidden'] == 'no') {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    private function get_id($id): int {
        return (int) $id;
    }

    private function get_parent_id($posts): string {
        return $posts['post_parent'] ?? "";
    }

    private function get_fabric_id($postmeta): string {
        return $postmeta['_product_fabric'] ?? "";
    }

    private function get_length($postmeta): string {
        return $postmeta['_length'] ?? "";
    }

    private function get_width($postmeta): string {
        return $postmeta['_width'] ?? "";
    }

    private function get_height($postmeta): string {
        return $postmeta['_height'] ?? "";
    }

    private function get_weight($postmeta): string {
        return $postmeta['_product_weight'] ?? "";
    }

    private function get_sleep_area($postmeta): string {
        return $postmeta['_product_sleeping_area'] ?? "";
    }

    private function get_attributes($posts, $postmeta, $terms_by_slug, $attribute_taxonomies, $fabrics, $termmeta, $terms, $attachments, $all_postmeta) {
        $data = [];

        foreach ($this->get_attributes_keys($postmeta) as $key) {
            if (isset($attribute_taxonomies[str_replace('attribute_pa_', '', $key)]) && isset($terms_by_slug[$postmeta[$key]]['name'])) {
                $data[$key]['taxonomy_slug'] = str_replace('attribute_', '', $key);
                $data[$key]['taxonomy_name'] = $attribute_taxonomies[str_replace('attribute_pa_', '', $key)];
                $data[$key]['term_slug'] = $postmeta[$key];
                $data[$key]['term_name'] = $terms_by_slug[$postmeta[$key]]['name'];

                if ($key == 'attribute_pa_fabric') {
                    $data[$key]['details'] = $this->get_attributes_fabric_details($key, $posts, $postmeta, $terms, $fabrics, $all_postmeta);
                }

                if ($key == 'attribute_pa_material') {
                    $slug = $postmeta[$key];
                    $image_id = $termmeta[$terms_by_slug[$slug]['term_id']]['material-image'] ?? "";
                    $data[$key]['details']['image']['w100'] = $attachments[$image_id]['w100'] ?? "";
                }
            }
        }

        return serialize(array_values($data));
    }

    private function get_attributes_keys($postmeta) {
        return preg_grep('/^attribute_/', array_keys($postmeta));
    }

    private function get_stock($postmeta) {
        if (isset($postmeta['_product_stock'])) {
            if ($postmeta['_product_stock'] == 'yes') {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function set_id($id) {
        $this->variations[$id]['id'] = $this->get_id($id);
    }

    private function set_parent_id($id, $posts) {
        $this->variations[$id]['parent_id'] = $this->get_parent_id($posts);
    }

    private function set_sku($id, $postmeta) {
        $this->variations[$id]['sku'] = $this->get_sku($postmeta);
    }

    private function set_regular_price($id, $postmeta) {
        $this->variations[$id]['regular_price'] = $this->get_price($postmeta);
    }

    private function set_sale_price($id, $postmeta) {
        $this->variations[$id]['sale_price'] = $this->get_sale_price($postmeta);
    }

    private function set_thumbnail($id, $postmeta, $attachments) {
        $this->variations[$id]['thumbnail'] = $this->get_thumbnail($postmeta, $attachments);
    }

    private function set_gallery($id, $postmeta, $attachments) {
        $this->variations[$id]['gallery'] = $this->get_gallery($postmeta, $attachments);
    }

    private function set_fabric_id($id, $postmeta) {
        $this->variations[$id]['fabric_id'] = $this->get_fabric_id($postmeta);
    }

    private function set_drawing($id, $postmeta, $attachments) {
        $this->variations[$id]['drawing'] = $this->get_drawing($postmeta, $attachments);
    }

    private function set_length($id, $postmeta) {
        $this->variations[$id]['length'] = $this->get_length($postmeta);
    }

    private function set_width($id, $postmeta) {
        $this->variations[$id]['width'] = $this->get_width($postmeta);
    }

    private function set_height($id, $postmeta) {
        $this->variations[$id]['height'] = $this->get_height($postmeta);
    }
    private function set_weight($id, $postmeta) {
        $this->variations[$id]['weight'] = $this->get_weight($postmeta);
    }

    private function set_sleep_area($id, $postmeta) {
        $this->variations[$id]['sleep_area'] = $this->get_sleep_area($postmeta);
    }

    private function set_attributes($id, $posts, $postmeta, $terms_by_slug, $attribute_taxonomies, $fabrics, $termmeta, $terms, $attachments, $all_postmeta) {
        $this->variations[$id]['attributes'] = $this->get_attributes($posts, $postmeta, $terms_by_slug, $attribute_taxonomies, $fabrics, $termmeta, $terms, $attachments, $all_postmeta);
    }

    private function set_stock($id, $postmeta) {
        $this->variations[$id]['stock'] = $this->get_stock($postmeta);
    }
}