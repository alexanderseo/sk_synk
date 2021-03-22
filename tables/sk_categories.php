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

            $this->categories[$value]['id'] = (int) $value;
            $this->categories[$value]['redis_key'] = $this->set_redis_key((int) $value, $this->term_relationships, $this->posts);
            $this->categories[$value]['parent_id'] = $this->term_taxonomy[$value]['parent'];
            $this->categories[$value]['name'] = $this->terms[$value]['name'];
            $this->categories[$value]['slug'] = $this->terms[$value]['slug'];
            $this->categories[$value]['thumbnail'] = (isset($term_meta['thumbnail_id']) && !empty($term_meta['thumbnail_id'])) ? serialize($this->attachments[$term_meta['thumbnail_id']]) : 0;
            $this->categories[$value]['recommended_categories'] = (isset($term_meta['recommended_categories']) && !empty($term_meta['recommended_categories'])) ? implode(',', unserialize($term_meta['recommended_categories'])) : 0;
            $this->categories[$value]['dative_title'] = (isset($term_meta['dative-title']) && !empty($term_meta['dative-title'])) ? $term_meta['dative-title'] : 0;
            $this->categories[$value]['nominative_title'] = (isset($term_meta['nominative-title']) && !empty($term_meta['nominative-title'])) ? $term_meta['nominative-title'] : 0;
            $this->categories[$value]['has_fabric'] = (isset($term_meta['has-fabric']) && !empty($term_meta['has-fabric'])) ? $term_meta['has-fabric'] : 0;

            if (isset($term_meta['enable-product-comparison']) && isset($term_meta['attributes-product-comparison']) && $term_meta['enable-product-comparison'] != 0 && !empty($term_meta['attributes-product-comparison'])) {
                $this->categories[$value]['attributes_product_comparison'] = $term_meta['attributes-product-comparison'];
            } else {
                $this->categories[$value]['attributes_product_comparison'] = 0;
            }

            $attributes_filter_list = $this->get_attributes_filter_list_reformatted($this->get_attributes_filter_list($term_meta));

            $this->categories[$value]['attributes_filter_list'] = (!empty($attributes_filter_list)) ? serialize($attributes_filter_list) : 0;
        }

        return $this->categories;
    }

    private function get_ids($taxonomies) {
        $data = [];

        foreach ($taxonomies as $value) {
            if ($value['taxonomy'] == 'product_cat') {
                $data[] = $value['term_id'];
            }
        }

        return $data;
    }

    private function get_attributes_filter_list($array) {
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

    private function get_attributes_filter_list_reformatted($array) {
        $data = [];

        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $data[$value['item']] = $value['type'];
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
    private function set_redis_key($id_category, $term_relationships, $posts) {
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
}