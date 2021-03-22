<?php

require_once 'helpers/general_helpers.php';

class sk_nav_menu extends bootstrap {
    private static $instance;

    use general_helpers;

    private $data_menu;
    private $posts_nav_menu;
    private $postmeta;
    private $relationships;
    private $term_taxonomy;
    private $terms;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->data_menu = [];
        $this->posts_nav_menu = $wordpress['posts_nav_menu'];
        $this->postmeta = $wordpress['postmeta'];
        $this->relationships = $wordpress['term_relationships'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];
        $this->terms = $wordpress['terms'];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get($categories) {

        foreach ($this->posts_nav_menu as $item_menu) {
            $postmeta_array = $this->set_postmeta_array_by_id($item_menu['ID'], $this->postmeta);
            $relashionships_array = $this->set_relashions_array_by_id($item_menu['ID'], $this->relationships);

            $this->set_id('id', $item_menu);
            $this->set_name('name', $item_menu);
            $this->set_status('status', $item_menu);
            $this->set_guid('guid', $item_menu);
            $this->set_post_slug('post_slug', $item_menu);
            $this->set_menu_order('menu_order', $item_menu);
            $this->set_slug('slug', $item_menu, $postmeta_array);
            $this->set_type('type', $item_menu, $postmeta_array);
            $this->set_item_parent('item_parent', $item_menu, $postmeta_array);
            $this->set_object_id('object_id', $item_menu, $postmeta_array);
            $this->set_object('object', $item_menu, $postmeta_array);
            $this->set_data_taxonomies($item_menu, $postmeta_array, $relashionships_array, $this->term_taxonomy, $this->terms);
        }

        $result = $this->addition_objects($this->data_menu, $categories);

//        var_dump('-----------', $result);

        return $result;
    }

    private function get_menu_name($item_menu, $categories) {
        $name = "";

        if ($item_menu['type'] == 'taxonomy') {
            foreach ($categories as $category) {
                if ($category['id'] == $item_menu['object_id']) {
                    $name = $category['name'];
                }
            }
        }

        return $name;
    }

    private function get_menu_slug($item_menu, $categories) {
        $slug = "";

        if ($item_menu['type'] == 'taxonomy') {
            foreach ($categories as $category) {
                if ($category['id'] == $item_menu['object_id']) {
                    $slug = $category['slug'];
                }
            }
        }

        return $slug;
    }

    private function addition_objects($ready_menu, $categories) {
        $body = [];

        foreach ($ready_menu as $item_menu) {
            $body[$item_menu['id']] = [
                'id' => $item_menu['id'],
                'name' => $item_menu['name'] ?: $this->get_menu_name($item_menu, $categories),
                'status' => $item_menu['status'],
                'guid' => $item_menu['guid'],
                'post_slug' => $item_menu['post_slug'],
                'menu_order' => $item_menu['menu_order'],
                'slug' =>$item_menu['slug'] ?: $this->get_menu_slug($item_menu, $categories),
                'type' => $item_menu['type'],
                'item_parent' => $item_menu['item_parent'],
                'object_id' => $item_menu['object_id'],
                'object' => $item_menu['object'],
                'data_taxonomies' => $item_menu['data_taxonomies']
            ];
        }

        return $body;
    }

    private function get_id($menu) {
        return $menu['ID'];
    }

    private function get_name($menu) {
        return $menu['post_title'];
    }

    private function get_status($menu) {
        return $menu['post_status'];
    }

    private function get_guid($menu) {
        return $menu['guid'];
    }

    private function get_post_slug($menu) {
        return $menu['post_name'];
    }

    private function get_menu_order($menu) {
        return $menu['menu_order'];
    }

    private function get_slug($postmeta) {
//        return isset($postmeta['_menu_item_url']) ? $postmeta['_menu_item_url'] : "";
        return $postmeta['_menu_item_url'] ?? "";
    }

    /**
     * @param $postmeta
     * @return mixed
     * Типы: custom, post_type, taxonomy
     */
    private function get_type($postmeta) {
        return isset($postmeta['_menu_item_type']) ? $postmeta['_menu_item_type'] : "";
    }

    private function get_item_parent($postmeta) {
        return isset($postmeta['_menu_item_menu_item_parent']) ? $postmeta['_menu_item_menu_item_parent'] : "";
    }

    /**
     * @param $postmeta
     * @return string
     * У типа custom это свой собственый id.
     * У типа taxonomy это taxonomy_id, например, пункт меню ПО РАЗМЕРУ в категории диваны.
     * Сам пункт является родителем дря других объектов.
     * У типа post_type это id страницы (post_type=page).
     */
    private function get_object_id($postmeta) {
        return isset($postmeta['_menu_item_object_id']) ? $postmeta['_menu_item_object_id'] : "";
    }

    private function get_object($postmeta) {
        return isset($postmeta['_menu_item_object']) ? $postmeta['_menu_item_object'] : "";
    }

    private function set_id($key, $menu) {
        $this->data_menu[$menu['ID']][$key] = $this->get_id($menu);
    }

    private function set_name($key, $menu) {
        $this->data_menu[$menu['ID']][$key] = $this->get_name($menu);
    }

    private function set_status($key, $menu) {
        $this->data_menu[$menu['ID']][$key] = $this->get_status($menu);
    }

    private function set_guid($key, $menu) {
        $this->data_menu[$menu['ID']][$key] = $this->get_guid($menu);
    }

    private function set_post_slug($key, $menu) {
        $this->data_menu[$menu['ID']][$key] = $this->get_post_slug($menu);
    }

    private function set_menu_order($key, $menu) {
        $this->data_menu[$menu['ID']][$key] = $this->get_menu_order($menu);
    }

    private function set_slug($key, $menu, $postmeta) {
        $this->data_menu[$menu['ID']][$key] = $this->get_slug($postmeta);
    }

    private function set_type($key, $menu, $postmeta) {
        $this->data_menu[$menu['ID']][$key] = $this->get_type($postmeta);
    }

    private function set_item_parent($key, $menu, $postmeta) {
        $this->data_menu[$menu['ID']][$key] = $this->get_item_parent($postmeta);
    }

    private function set_object_id($key, $menu, $postmeta) {
        $this->data_menu[$menu['ID']][$key] = $this->get_object_id($postmeta);
    }

    private function set_object($key, $menu, $postmeta) {
        $this->data_menu[$menu['ID']][$key] = $this->get_object($postmeta);
    }

    private function get_data_taxonomies($relashionships_array, $term_taxonomy, $terms) {
        $data = [];

        foreach ($relashionships_array as $relashionship) {
            $data['taxonomy_id'] = isset($term_taxonomy[$relashionship['term_taxonomy_id']]) ? $term_taxonomy[$relashionship['term_taxonomy_id']]['term_taxonomy_id'] : "";
            $data['taxonomy'] = isset($term_taxonomy[$relashionship['term_taxonomy_id']]) ? $term_taxonomy[$relashionship['term_taxonomy_id']]['taxonomy'] : "";
            $data['parent'] = isset($term_taxonomy[$relashionship['term_taxonomy_id']]) ? $term_taxonomy[$relashionship['term_taxonomy_id']]['parent'] : "";
            $data['count'] = isset($term_taxonomy[$relashionship['term_taxonomy_id']]) ? $term_taxonomy[$relashionship['term_taxonomy_id']]['count'] : "";
            $data['name'] = isset($terms[$term_taxonomy[$relashionship['term_taxonomy_id']]['term_taxonomy_id']]) ? $terms[$term_taxonomy[$relashionship['term_taxonomy_id']]['term_taxonomy_id']]['name'] : "";
            $data['slug'] = isset($terms[$term_taxonomy[$relashionship['term_taxonomy_id']]['term_taxonomy_id']]) ? $terms[$term_taxonomy[$relashionship['term_taxonomy_id']]['term_taxonomy_id']]['slug'] : "";
        }

        return serialize($data);
    }

    private function set_data_taxonomies($menu, $postmeta_array, $relashionships_array, $term_taxonomy, $terms) {
        if (empty($relashionships_array)) {
            $this->data_menu[$menu['ID']]['data_taxonomies'] = [];
        } else {
            $this->data_menu[$menu['ID']]['data_taxonomies'] = $this->get_data_taxonomies($relashionships_array, $term_taxonomy, $terms);
        }
    }
}