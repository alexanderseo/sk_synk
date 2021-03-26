<?php
class sk_categories extends bootstrap {
    private static $instance;

    private $log;
    private $terms;
    private $termmeta;
    private $term_taxonomy;
    private $attachments;
    private $term_relationships;
    private $posts;
    private $categories;

    public function __construct() {
        parent::__construct();
        global $wordpress;

        $this->terms = $wordpress['terms'];
        $this->termmeta = $wordpress['termmeta'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];
        $this->attachments = $wordpress['attachments'];
        $this->term_relationships = $wordpress['term_relationships'];
        $this->posts = $wordpress['posts'];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->get_ids($this->term_taxonomy) as $value) {
            $term_meta = $this->termmeta[$value];

            $this->set_id('id', (int)$value);
            $this->set_redis_key('redis_key', (int)$value, $this->term_relationships, $this->posts);
            $this->set_parent_id('parent_id', (int)$value, $this->term_taxonomy);
            $this->set_name('name', (int)$value, $this->terms);
            $this->set_slug('slug', (int)$value, $this->terms);
            $this->set_thumbnail('thumbnail', (int)$value, $term_meta, $this->attachments);
            $this->set_recommended_categories('recommended_categories', (int)$value, $term_meta);
            $this->set_dative_title('dative_title', (int)$value, $term_meta);
            $this->set_nominative_title('nominative_title', (int)$value, $term_meta);
            $this->set_has_fabric('has_fabric', (int)$value, $term_meta);
            $this->set_attributes_product_comparison('attributes_product_comparison', (int)$value, $term_meta);
            $this->set_attributes_filter_list('attributes_filter_list', (int)$value, $term_meta);
            $this->set_enable_comparison('enable_comparison', (int)$value, $term_meta);
            $this->set_icon('icon', (int)$value, $term_meta, $this->attachments);

        }

//        var_dump($this->categories);

        return $this->categories;
    }

    /**
     * @param $taxonomies
     * @return array
     * Получаем все ids категорий из таксономий
     */
    private function get_ids($taxonomies) {
        $data = [];

        foreach ($taxonomies as $value) {
            if ($value['taxonomy'] == 'product_cat') {
                $data[] = $value['term_id'];
            }
        }

        return $data;
    }

    /**
     * @param $id_category
     * @return string
     * Метод формирует уникальную строку для каждой категории
     * Строка формируется из id и post_modified товаров в этой категории, преобразованных в строку и потом зашифрованы
     */
    private function get_redis_key(int $id_category, array $term_relationships, array $posts): string {
        $array = [];

        foreach ($term_relationships as $relationship) {
            foreach ($relationship as $item) {
                if ($item['term_taxonomy_id'] == $id_category) {
                    $array[] = $item['object_id'];
                }
            }
        }

        $products = [];

        foreach ($array as $key => $value) {
            if (isset($posts[$value])) {
                $products[] = $posts[$value]['ID'] . '_' . strtotime($posts[$value]['post_modified']);
            }
        }

        $string_uniq = implode('_', $products);

        return hash('sha512/256', $string_uniq);
    }

    private function get_id(int $value): int {
        return (int)$value;
    }

    private function get_parent_id(int $value, array $term_taxonomy): string {
        return $term_taxonomy[$value]['parent'] ?? "";
    }

    private function get_name(int $value, array $terms): string {
        return $terms[$value]['name'] ?? "";
    }

    private function get_slug(int $value, array $terms): string {
        return $terms[$value]['slug'] ?? "";
    }

    private function get_thumbnail(array $term_meta, array $attachments): string {
        $thumbnail = "0";

        if (isset($term_meta['thumbnail_id'])) {
            if (!empty($term_meta['thumbnail_id'])) {
                $thumbnail = serialize($attachments[$term_meta['thumbnail_id']]);
            }
        }

        return $thumbnail;
    }

    private function get_recommended_categories(array $term_meta): string {
        $recommended_categories = "0";

        if (isset($term_meta['recommended_categories'])) {
            if (!empty($term_meta['recommended_categories'])) {
                $recommended_categories = implode(',', unserialize($term_meta['recommended_categories']));
            }
        }

        return $recommended_categories;
    }

    private function get_dative_title(array $term_meta): string {
        $dative_title = "0";

        if (isset($term_meta['dative-title'])) {
            if (!empty($term_meta['dative-title'])) {
                $dative_title = $term_meta['dative-title'];
            }
        }

        return $dative_title;
    }

    private function get_nominative_title(array $term_meta): string {
        $nominative_title = "0";

        if (isset($term_meta['nominative-title'])) {
            if (!empty($term_meta['nominative-title'])) {
                $nominative_title = $term_meta['nominative-title'];
            }
        }

        return $nominative_title;
    }

    private function get_has_fabric(array $term_meta): string {
        $has_fabric = "0";

        if (isset($term_meta['has-fabric'])) {
            if (!empty($term_meta['has-fabric'])) {
                $has_fabric = $term_meta['has-fabric'];
            }
        }

        return $has_fabric;
    }

    private function get_attributes_product_comparison(array $term_meta): string {
        $attributes_product_comparison = "0";

        if (isset($term_meta['attributes-product-comparison'])) {
            if (!empty($term_meta['attributes-product-comparison'])) {
                $attributes_product_comparison = $term_meta['attributes-product-comparison'];
            }
        }

        return $attributes_product_comparison;
    }

    private function get_enable_comparison(array $term_meta): string {
        $enable_product_comparison = "0";

        if (isset($term_meta['enable-product-comparison'])) {
            if (!empty($term_meta['enable-product-comparison'])) {
                $enable_product_comparison = $term_meta['enable-product-comparison'];
            }
        }

        return $enable_product_comparison;
    }

    private function getting_attributes_filter_list(array $array): array {
        $data = [];

        foreach (preg_grep('/^attributes-filter-list_/', array_keys($array)) as $value) {
            $exploded_value = explode('_', $value);

            switch ($exploded_value[2]) {
                case 'attribute-item' : $data[$exploded_value[1]]['item'] = $array[$value]; break;
                case 'attribute-type' : $data[$exploded_value[1]]['type'] = $array[$value]; break;
            }
        }

        return $data;
    }

    private function get_attributes_filter_list_reformatted(array $array): array {
        $data = [];

        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $data[$value['item']] = $value['type'];
            }
        }

        return $data;
    }

    private function get_attributes_filter_list(array $term_meta): string {

        $attributes_filter_list = $this->get_attributes_filter_list_reformatted($this->getting_attributes_filter_list($term_meta));

        if (!empty($attributes_filter_list)) {
            return serialize($attributes_filter_list);
        } else {
            return "0";
        }
    }

    private function get_icon(array $term_meta, array $attachments): string {
        $icon = "0";

        if (isset($term_meta['icon'])) {
            if (!empty($term_meta['icon'])) {
                $icon = serialize($attachments[$term_meta['icon']]['original']);
            }
        }

        return $icon;
    }

    private function set_id(string $key, int $value): void {
        $this->categories[$value][$key] = $this->get_id($value);
    }

    private function set_redis_key(string $key, int $value, array $term_relationships, array $posts): void {
        $this->categories[$value][$key] = $this->get_redis_key($value, $term_relationships, $posts);
    }

    private function set_parent_id(string $key, int $value, array $term_taxonomy): void {
        $this->categories[$value][$key] = $this->get_parent_id($value, $term_taxonomy);
    }

    private function set_name(string $key, int $value, array $terms): void {
        $this->categories[$value][$key] = $this->get_name($value, $terms);
    }

    private function set_slug(string $key, int $value, array $terms): void {
        $this->categories[$value][$key] = $this->get_slug($value, $terms);
    }

    private function set_thumbnail($key, $value,  $term_meta, $attachments): void {
        $this->categories[$value][$key] = $this->get_thumbnail($term_meta, $attachments);
    }

    private function set_recommended_categories($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_recommended_categories($term_meta);
    }

    private function set_dative_title($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_dative_title($term_meta);
    }

    private function set_nominative_title($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_nominative_title($term_meta);
    }

    private function set_has_fabric($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_has_fabric($term_meta);
    }

    private function set_attributes_product_comparison($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_attributes_product_comparison($term_meta);
    }

    private function set_enable_comparison($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_enable_comparison($term_meta);
    }

    private function set_attributes_filter_list($key, $value,  $term_meta): void {
        $this->categories[$value][$key] = $this->get_attributes_filter_list($term_meta);
    }

    private function set_icon($key, $value,  $term_meta, $attachments): void {
        $this->categories[$value][$key] = $this->get_icon($term_meta, $attachments);
    }
}