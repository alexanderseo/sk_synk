<?php

require_once 'FilterTypeProduct.php';
require_once 'default_attributes.php';
require_once 'static_attributes.php';
require_once 'variable_attributes.php';

class GetUpsell {
    use default_attributes;
    use static_attributes;
    use variable_attributes;

    private $postmeta;
    private $variations;
    private $posts;
    private $all_postmeta;
    private $all_ids_products;
    private $data;

    private $fabrics;
    private $all_relashionships;
    private $taxonomy;
    private $terms;
    private $woocommerce_attribute_taxonomies;
    private $termmeta;
    private $terms_by_slug;
    private $materials;

    private $filterHelper;

    public function __construct($postmeta, $variations, $posts, $all_postmeta, $all_ids_products, $fabrics, $all_relashionships, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta, $terms_by_slug, $materials) {
        $this->posts = $posts;
        $this->postmeta = $postmeta;
        $this->variations = $variations;
        $this->all_postmeta = $all_postmeta;
        $this->all_ids_products = $all_ids_products;
        $this->data = [];

        $this->fabrics = $fabrics;
        $this->all_relashionships = $all_relashionships;
        $this->taxonomy = $taxonomy;
        $this->terms = $terms;
        $this->woocommerce_attribute_taxonomies = $woocommerce_attribute_taxonomies;
        $this->termmeta = $termmeta;
        $this->terms_by_slug = $terms_by_slug;
        $this->materials = $materials;

        $this->filterHelper = new FilterTypeProduct($this->all_relashionships, $this->taxonomy, $this->terms);
    }

    public function get_upsells($crosssell_ids) {
//        $upsell_ids = isset($this->postmeta['_upsell_ids']) ? unserialize($this->postmeta['_upsell_ids']) : "";
        if (empty($crosssell_ids)) {
            $upsell_ids = isset($this->postmeta['_crosssell_ids']) ? unserialize($this->postmeta['_crosssell_ids']) : "";

            if (empty($upsell_ids)) {
                return serialize([]);
            }
        } else {
            $upsell_ids = $crosssell_ids;
        }


        foreach ($upsell_ids as $id) {
            if ($this->filterHelper->filter_simple_product($id)) {
                if ($this->check_status_product($id, $this->all_ids_products)) {
                    $postmeta_array = $this->set_postmeta_array_by_id($id, $this->all_postmeta);
                    $relashionships_array = $this->set_relashions_array_by_id($id, $this->all_relashionships);

                    $this->data['enable_comparison'] = false;

                    $this->set_id('id', $id);
                    $this->set_slug('slug', $id, $this->posts);
                    $this->set_name('name', $id, $this->posts);
                    $this->set_subtitle('subtitle', $id, $postmeta_array);
                    $this->set_price('price', $id, $postmeta_array, $this->variations);
                    $this->set_image('image', $id, $postmeta_array, $this->variations);
                    $this->set_default_variation_id('default_variation_id', $id, $postmeta_array);
                    $this->set_product_attributes('product_attributes', $id, $postmeta_array);
                    $this->set_attributes($id, $postmeta_array, $this->fabrics, $relashionships_array, $this->taxonomy, $this->terms, $this->woocommerce_attribute_taxonomies, $this->all_postmeta, $this->termmeta, $this->materials);
                    $this->set_default_attributes('default_attributes', $id, $postmeta_array, $this->woocommerce_attribute_taxonomies, $this->terms_by_slug);
                }
            }
        }


        return serialize($this->data);
//        return $this->data;
    }

    /**
     * @param $id
     * @param $all_ids_products
     * @return bool|string
     * В кроссейл и апсейл могут лежать товары, у которых изменился статус (например, удалены),
     * поэтому осуществляется проверка на publish или inherit
     */
    public function check_status_product($id, $all_ids_products) {
        $ok = '';

        foreach ($all_ids_products as $key => $item) {
            if ((int)$item == (int)$id) {
                $ok = true;
                break;
            }
        }

        return $ok;
    }

    public function set_postmeta_array_by_id($id, $postmeta) {
        return $postmeta[$id];
    }

    public function set_relashions_array_by_id($id, $relashionships) {
        if (array_key_exists($id, $relashionships)) {
            return $relashionships[$id];
        }
    }

    private function get_upsell_id($id) {
        return (int)$id;
    }

    private function get_upsell_slug($id, $array_products) {
        return isset($array_products[$id]['post_name']) ? $array_products[$id]['post_name'] : 0;
    }

    private function get_upsell_name($id, $array_products) {
        return isset($array_products[$id]['post_title']) ? $array_products[$id]['post_title'] : 0;
    }

    private function get_upsell_subtitle($postmeta) {

        $subtitle = isset($postmeta['_product_classification']) ? $postmeta['_product_classification'] : 0;

        return $subtitle;
    }

    private function get_upsell_price($postmeta_array, $variations) {
        $default_variation_id = isset($postmeta_array['_default_variation_id']) ? $postmeta_array['_default_variation_id'] : "";

        $price = '';

        if (isset($variations[$default_variation_id])) {
            $data_variation = $variations[$default_variation_id];

            $price = isset($data_variation['regular_price']) ? $data_variation['regular_price'] : "";

        }

        return $price;
    }

    private function get_upsell_image($postmeta_array, $variations) {
        $image = [];

        $default_variation_id = isset($postmeta_array['_default_variation_id']) ? $postmeta_array['_default_variation_id'] : "";
        if (!$default_variation_id) {
            return $image;
        }

        if (isset($variations[$default_variation_id])) {
            $data_variation = $variations[$default_variation_id];
            $image = isset($data_variation['thumbnail']) ? self::size_image_upsell($data_variation['thumbnail']) : [];
        }

        return $image;
    }

    static function size_image_upsell($image_string) {
        $data = [];

        if (empty($image_string)) {
            return serialize($data);
        }

        $images = unserialize($image_string);
        $array_sizes = ['w300'];

        foreach ($array_sizes as $size) {
            $data[$size] = $images[$size] ?? "";
        }

        return serialize($data);
    }

    private function get_default_variation_id($postmeta): string {
        return isset($postmeta['_default_variation_id']) ? $postmeta['_default_variation_id'] : "";
    }

    private function get_product_attributes($postmeta): string {
        return isset($postmeta['_product_attributes']) ? $postmeta['_product_attributes'] : "";
    }

    private function set_id($key, $id) {
        $this->data['items'][$id][$key] = $this->get_upsell_id($id);
    }

    private function set_slug($key, $id, $posts) {
        $this->data['items'][$id][$key] = $this->get_upsell_slug($id, $posts);
    }

    private function set_name($key, $id, $posts) {
        $this->data['items'][$id][$key] = $this->get_upsell_name($id, $posts);
    }

    private function set_subtitle($key, $id, $postmeta) {
        $this->data['items'][$id][$key] = $this->get_upsell_subtitle($postmeta);
    }

    private function set_price($key, $id, $postmeta_array, $variations) {
        $this->data['items'][$id][$key] = $this->get_upsell_price($postmeta_array, $variations);
    }

    private function set_image($key, $id, $postmeta, $variations) {
        $this->data['items'][$id][$key] = $this->get_upsell_image($postmeta, $variations);
    }

    private function set_default_variation_id($key, $id, $postmeta): void {
        $this->data['items'][$id][$key] = $this->get_default_variation_id($postmeta);
    }

    private function set_product_attributes($key, $id, $postmeta): void {
        $this->data['items'][$id][$key] = $this->get_product_attributes($postmeta);
    }

    private function get_attributes($product_attributes) {
        $data = [];

//        var_dump('-----------------', $product_attributes);

        foreach ($product_attributes as $attribute) {
            if ($attribute['is_variation'] == 0) {
                $data['static'][$attribute['name']] = $attribute['name'];
            } else {
                $data['variable'][$attribute['name']] = $attribute['name'];
            }
        }

        return $data;
    }

    private function set_attributes($id, $postmeta, $fabrics, $relashionships_array, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials): void {

        if (isset($postmeta['_product_attributes'])) {
            if (!empty(unserialize($postmeta['_product_attributes']))) {


                $attributes = $this->get_attributes(unserialize($postmeta['_product_attributes']));

                $this->data['items'][$id]['static_attributes'] = $this->get_static_attributes($relashionships_array, $attributes, $taxonomy, $terms, $woocommerce_attribute_taxonomies);
                $this->data['items'][$id]['variable_attributes'] = $this->get_variable_attributes($relashionships_array, $attributes, $fabrics, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials);
            }
        }
    }

    private function set_default_attributes($key, $id, $postmeta, $woocommerce_attribute_taxonomies, $terms_by_slug): void {
        $this->data['items'][$id][$key] = $this->get_default_attributes($postmeta, $woocommerce_attribute_taxonomies, $terms_by_slug);

    }
}
