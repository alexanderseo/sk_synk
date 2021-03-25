<?php

require_once 'helpers/FilterTypeProduct.php';
require_once 'helpers/general_helpers.php';
require_once 'helpers/default_attributes.php';
require_once 'helpers/static_attributes.php';
require_once 'helpers/variable_attributes.php';
require_once 'helpers/GetUpsell.php';
require_once 'helpers/GetCrossSells.php';
require_once 'sk_sets.php';

class sk_product extends bootstrap {
    private static $instance;

    use general_helpers;
    use default_attributes;
    use static_attributes;
    use variable_attributes;

    private $log;
    private $products;

    private $all_products;
    private $all_ids_products;
    private $taxonomy;
    private $relashionships;
    private $terms;
    private $postmeta;
    private $attachments;
    private $attachments_with_parent;
    private $woocommerce_attribute_taxonomies;
    private $termmeta;
    private $terms_by_slug;

    private $filterHelper;


    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = [];
        $this->products = [];
        $this->all_products = $wordpress['products'];
        $this->all_ids_products = $wordpress['posts_ids']['products'];
        $this->postmeta = $wordpress['postmeta'];
        $this->taxonomy = $wordpress['term_taxonomy'];
        $this->relashionships = $wordpress['term_relationships'];
        $this->terms = $wordpress['terms'];
        $this->attachments = $wordpress['attachments'];
        $this->attachments_with_parent = $wordpress['attachments_with_parent'];
        $this->woocommerce_attribute_taxonomies = $wordpress['woocommerce_attribute_taxonomies'];
        $this->termmeta = $wordpress['termmeta'];
        $this->terms_by_slug = $wordpress['terms_by_slug'];
        $this->filterHelper = new FilterTypeProduct($this->relashionships, $this->taxonomy, $this->terms);

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get($variations, $categories, $fabrics, $materials) {

        foreach ($this->all_ids_products as $id) {
            if ($this->filterHelper->filter_simple_product($id)) {
                if ($this->filterHelper->filter_stock_product($id)) {
                    if ($this->filterHelper->filter_expo_product($id)) {

//                    $id = 187453;

                        $products_array = $this->set_products_array_by_id($id, $this->all_products);
                        $relashionships_array = $this->set_relashions_array_by_id($id, $this->relashionships);
                        $postmeta_array = $this->set_postmeta_array_by_id($id, $this->postmeta);
                        $category_id = $this->get_category_id($id, $relashionships_array, $this->taxonomy);
                        $caterory_item = $this->get_category($category_id, $categories);

                        $this->set_id('id', $id);
                        $this->set_modified_unix('modified_unix', $id, $products_array);
                        $this->set_slug('slug', $id, $products_array);
                        $this->set_name('name', $id, $products_array);
                        $this->set_category_id('category_id', $id, $category_id);
                        $this->set_default_variation_id('default_variation_id', $id, $postmeta_array);
                        $this->set_product_attributes('product_attributes', $id, $postmeta_array);
                        $this->set_subtitle('subtitle', $id, $postmeta_array);
                        $this->set_video('video', $id, $postmeta_array, $this->attachments, $this->attachments_with_parent);
                        $this->set_collection_id('collection_id', $id, $postmeta_array);
                        $this->set_interior_photos('interior_photos', $id, $postmeta_array, $this->attachments);
                        $this->set_up_sells_ids('up_sells_ids', $id, $postmeta_array, $variations, $this->all_products, $this->postmeta, $this->all_ids_products, $fabrics, $this->relashionships, $this->taxonomy, $this->terms, $this->woocommerce_attribute_taxonomies, $this->termmeta, $this->terms_by_slug, $materials);
                        $this->set_recommended_categories('recommended_categories', $id, $caterory_item, $categories);
                        $this->set_attributes($id, $postmeta_array, $fabrics, $relashionships_array, $this->taxonomy, $this->terms, $this->woocommerce_attribute_taxonomies, $this->postmeta, $this->termmeta, $materials);
                        $this->set_default_attributes('default_attributes', $id, $postmeta_array, $this->woocommerce_attribute_taxonomies, $this->terms_by_slug);
                        $this->set_cross_sells('cross_sells', $id, $postmeta_array, $category_id, $categories, $variations, $this->all_products, $this->postmeta, $this->relashionships, $this->taxonomy, $this->terms, $this->woocommerce_attribute_taxonomies, $materials, $this->all_ids_products, $fabrics, $this->termmeta, $this->terms_by_slug);
                        $this->set_popular_products($id, $postmeta_array);
                        $this->set_new_sets($id, $fabrics, $categories);
                    }
                }
            }
        }

//        var_dump($this->products);

        return $this->products;
    }

    private function get_id($id): int {
        return (int)$id;
    }

    private function get_slug($array): string {
        return $array['post_name'];
    }

    private function get_name($array): string {
        return $array['post_title'];
    }

    private function get_modified_unix($array): string {
        return strtotime($array['post_modified']);
    }

    private function get_default_variation_id($postmeta): string {
        return isset($postmeta['_default_variation_id']) ? $postmeta['_default_variation_id'] : "";
    }

    private function get_product_attributes($postmeta): string {
        return isset($postmeta['_product_attributes']) ? $postmeta['_product_attributes'] : "";
    }

    private function get_subtitle($postmeta): string {
        return isset($postmeta['_product_classification']) ? $postmeta['_product_classification'] : "";
    }

    public function get_video($postmeta, $attachments, $attachments_with_parent): string {
        $video_id = isset($postmeta['_product_video']) ? $postmeta['_product_video'] : "";
        $data = [];

        if (empty($video_id)) {
            return serialize($data);
        }

        if (isset($attachments[$video_id])) {
            $data['url'] = $attachments[$video_id]['url'];
        }

        foreach ($attachments_with_parent as $key => $value) {
            if (isset($attachments_with_parent[$key]['post_parent'])) {
                if ($attachments_with_parent[$key]['post_parent'] == $video_id) {

                    $data['cover']['original'] = $value['original'];
                    $data['cover']['w500'] = $value['w500'];
                }
            }
        }

        return serialize($data);
    }

    private function get_collection_id($postmeta): string {
        return isset($postmeta['_product_collection']) ? $postmeta['_product_collection'] : "";
    }

    private function get_interior_photos($postmeta, $attachments): string {
        $data = [];
        $product_interior = isset($postmeta['_product_interior']) ? explode(',', $postmeta['_product_interior']) : [];

        if (empty($product_interior)) {
            return serialize($data);
        }

        foreach ($product_interior as $value) {
            if (isset($attachments[$value])) {
                $data[] = serialize([
                    'original' => $attachments[$value]['original'],
                    'w300' => $attachments[$value]['w300']
                ]);
            }
        }

        return serialize($data);
    }

    private function get_up_sells_ids($postmeta, $variations, $posts, $all_postmeta, $all_ids_products, $fabrics, $all_relashionships, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta, $terms_by_slug, $materials) {
        $upsells = new GetUpsell($postmeta, $variations, $posts,$all_postmeta, $all_ids_products, $fabrics, $all_relashionships, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta, $terms_by_slug, $materials);
        $cross_pass = "";
        return $upsells->get_upsells($cross_pass);
    }

    private function get_recommended_categories($recommended_categories, $categories) {
        $data = [];

        if (isset($recommended_categories['recommended_categories'])) {
            foreach (explode(',', $recommended_categories['recommended_categories']) as $value) {

                $category = isset($categories[$value]) ? $categories[$value] : [];

                if (empty($category)) {
                    return serialize($data);
                }

                $data[$value]['name'] = $category['name'];
                $data[$value]['slug'] = $category['slug'];
                $data[$value]['thumbnail'] = $this->recommended_categories_get_size_image($category['thumbnail']);

            }
        }

        return serialize(array_values($data));
    }

    private function recommended_categories_get_size_image($image_string) {
        $data = [];
        $images = unserialize($image_string);
        $array_sizes = ['original'];

        foreach ($array_sizes as $size) {
            $data[$size] = $images[$size];
        }

        return serialize($data);
    }

    /**
     * @param $product_attributes
     * @return array
     * Определяем статичные и вариативные атрибуты
     */
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

    private function get_popular_products($id, $postmeta) {
        $data = array();

        if ($this->has_meta_popular_products($id, $postmeta, 'product-popular')) {
            $data['popular'] = $postmeta[$id]['product-popular'];
        } else {
            $data['popular'] = 0;
        }

        if ($this->has_meta_popular_products($id, $postmeta, 'product-popular-category')) {
            $data['popular_order'] = $postmeta[$id]['product-popular-category'];
        } else {
            $data['popular_order'] = 0;
        }

        if ($this->has_meta_popular_products($id, $postmeta, 'product-popular-category')) {
            $data['category'] = $postmeta[$id]['product-popular-category'];
        } else {
            $data['category'] = 0;
        }

        if ($this->has_meta_popular_products($id, $postmeta, 'product-popular-category-order')) {
            $data['category_order'] = $postmeta[$id]['product-popular-category-order'];
        } else {
            $data['category_order'] = 0;
        }

        return serialize($data);
    }

    private function has_meta_popular_products($id, $postmeta, $key) {
        if (isset($postmeta[$id][$key])) {
            if (!empty($postmeta[$id][$key])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function set_id($key, $id): void {
        $this->products[$id][$key] = $this->get_id($id);
    }

    private function set_slug($key, $id, $array): void {
        $this->products[$id][$key] = $this->get_slug($array);
    }

    private function set_name($key, $id, $array): void {
        $this->products[$id][$key] = $this->get_name($array);
    }

    private function set_modified_unix($key, $id, $array): void {
        $this->products[$id][$key] = $this->get_modified_unix($array);

    }

    private function set_category_id($key, $id, $category_id): void {
        $this->products[$id][$key] = $category_id;
    }

    private function set_default_variation_id($key, $id, $postmeta): void {
        $this->products[$id][$key] = $this->get_default_variation_id($postmeta);
    }

    private function set_product_attributes($key, $id, $postmeta): void {
        $this->products[$id][$key] = $this->get_product_attributes($postmeta);
    }

    private function set_subtitle($key, $id, $postmeta): void {
        $this->products[$id][$key] = $this->get_subtitle($postmeta);
    }

    private function set_video($key, $id, $postmeta, $attachments, $attachments_with_parent): void {
        $this->products[$id]['video'] = $this->get_video($postmeta, $attachments, $attachments_with_parent);
    }

    private function set_collection_id($key, $id, $postmeta): void {
        $this->products[$id][$key] = $this->get_collection_id($postmeta);
    }

    private function set_interior_photos($key, $id, $postmeta, $attachments): void {
        $this->products[$id][$key] = $this->get_interior_photos($postmeta, $attachments);
    }

    /**
     * ЭТО ЛЕТИТ В FIT из админки КРОССЕЛЫ
     */
    private function set_up_sells_ids($key, $id, $postmeta, $variations, $products, $all_postmeta, $all_ids_products, $fabrics, $all_relashionships, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta, $terms_by_slug, $materials): void {
        $this->products[$id][$key] = $this->get_up_sells_ids($postmeta, $variations, $products, $all_postmeta, $all_ids_products, $fabrics, $all_relashionships, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $termmeta, $terms_by_slug, $materials);
    }

    private function set_recommended_categories($key, $id, $category, $categories): void {
        $this->products[$id][$key] = $this->get_recommended_categories($category, $categories);
    }

    /**
     * @param $id
     * @param $postmeta
     * @param $fabrics
     * Устанавливаем static и variable атрибуты товара
     */
    private function set_attributes($id, $postmeta, $fabrics, $relashionships_array, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials): void {

        if (isset($postmeta['_product_attributes'])) {
            $attributes = $this->get_attributes(unserialize($postmeta['_product_attributes']));

            $this->products[$id]['static_attributes'] = $this->get_static_attributes($relashionships_array, $attributes, $taxonomy, $terms, $woocommerce_attribute_taxonomies);
            $this->products[$id]['variable_attributes'] = $this->get_variable_attributes($relashionships_array, $attributes, $fabrics, $taxonomy, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials);
        }

    }

    private function set_default_attributes($key, $id, $postmeta, $woocommerce_attribute_taxonomies, $terms_by_slug): void {
        $this->products[$id][$key] = $this->get_default_attributes($postmeta, $woocommerce_attribute_taxonomies, $terms_by_slug);

    }

    /**
     * ЭТО ЛЕТИТ В COMPARISON из админки АПСЕЙЛЫ
     */
    private function set_cross_sells($key, $id, $postmeta, $category_id, $categories, $variations, $all_product, $all_postmeta, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $materials, $all_ids_products, $fabrics, $termmeta, $terms_by_slug): void {
        $cross_sells = new GetCrossSells();
        $this->products[$id][$key] = $cross_sells->get_crosssells($id, $postmeta, $category_id, $categories, $variations, $all_product, $all_postmeta, $relaishionships, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $materials, $all_ids_products, $fabrics, $termmeta, $terms_by_slug);
    }

    private function set_popular_products($id, $postmeta): void {
        $this->products[$id]['popular'] = $this->get_popular_products($id, $postmeta);
    }

    private function set_new_sets($id, $fabrics, $categories): void {
        $create_sets = new sk_sets();
        $this->products[$id]['sets'] = $create_sets($id, $fabrics, $categories);
    }
}