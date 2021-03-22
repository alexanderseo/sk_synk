<?php

require_once 'sets/GroupOptionsBases.php';

use sets\GroupOptionsBases;

class wordpress {
    private static $instance;

    private $db;

    public $postmeta;
    public $posts;
    public $term_relationships;
    public $term_taxonomy;
    public $termmeta;
    public $terms;
    public $woocommerce_attribute_taxonomies;
    public $options;
    public $options_bases;
//    public $post_nav_menu;

//    public $posts_attachments;

    public function __construct() {
        $this->db = database::get_instance();

        $this->postmeta = $this->db->admin->query("SELECT * FROM wp_postmeta")->fetchAll(PDO::FETCH_ASSOC);
        $this->posts = $this->db->admin->query("SELECT ID, post_content, post_title, post_status, post_parent, guid, post_name, post_modified, post_parent, post_type, menu_order FROM wp_posts WHERE post_status = 'publish' OR post_status = 'inherit' OR post_status = 'future'")->fetchAll(PDO::FETCH_ASSOC);
//        $this->posts_attachments = $this->db->admin->query("SELECT ID, post_title, post_parent, guid FROM wp_posts WHERE post_type = 'attachment'")->fetchAll(PDO::FETCH_ASSOC);
        $this->term_relationships = $this->db->admin->query("SELECT * FROM wp_term_relationships")->fetchAll(PDO::FETCH_ASSOC);
        $this->term_taxonomy = $this->db->admin->query("SELECT * FROM wp_term_taxonomy")->fetchAll(PDO::FETCH_ASSOC);
        $this->termmeta = $this->db->admin->query("SELECT * FROM wp_termmeta")->fetchAll(PDO::FETCH_ASSOC);
        $this->terms = $this->db->admin->query("SELECT * FROM wp_terms")->fetchAll(PDO::FETCH_ASSOC);
        $this->woocommerce_attribute_taxonomies = $this->db->admin->query("SELECT attribute_name, attribute_label FROM wp_woocommerce_attribute_taxonomies")->fetchAll(PDO::FETCH_ASSOC);
        $this->options = $this->db->admin->query("SELECT * FROM wp_options WHERE option_name LIKE 'options%'")->fetchAll(PDO::FETCH_ASSOC);
        $this->options_bases = $this->db->admin->query("SELECT * FROM wp_options WHERE option_name LIKE 'options_bases_%'")->fetchAll(PDO::FETCH_ASSOC);
//        $this->post_nav_menu = $this->db->admin->query("SELECT ID, post_title, post_parent, post_name, guid FROM wp_posts WHERE post_type = 'nav_menu_item'")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function get_attachments() {
        $data = array();

        $suffixes = array(
            '--w_100',
            '--w_150',
            '--w_300',
            '--w_400',
            '--w_500'
        );

        $mime_types = array(
            'png',
            'jpg',
            'jpeg'
        );

        foreach ($this->posts as $value) {
            $array = array();

            if ($value['post_type'] == 'attachment') {
                $url = explode('.', $value['guid']);
                $count = count($url);

                $array['original'] = implode('.', $url);

                if (in_array(strtolower(end($url)), $mime_types)) {
                    foreach ($suffixes as $suffix) {
                        if ($count > 1) {
                            $_ = $url;

                            $_[$count - 2] = $_[$count - 2] . $suffix;

                            switch ($suffix) {
                                case '--w_100' : $suffix = 'w100'; break;
                                case '--w_150' : $suffix = 'w150'; break;
                                case '--w_300' : $suffix = 'w300'; break;
                                case '--w_400' : $suffix = 'w400'; break;
                                case '--w_500' : $suffix = 'w500'; break;
                            }

                            $array[$suffix] = implode('.', $_);
                        }
                    }

                    if (!empty($array)) {
                        $data[$value['ID']] = $array;
                    }
                } else {
                    $data[$value['ID']]['id'] = $value['ID'];
                    $data[$value['ID']]['url'] = $value['guid'];
                }
            }
        }

        return $data;
    }

    private function get_attachments_with_parent() {
        $data = array();

        $suffixes = array(
            '--w_100',
            '--w_150',
            '--w_300',
            '--w_400',
            '--w_500'
        );

        $mime_types = array(
            'png',
            'jpg',
            'jpeg'
        );

        foreach ($this->posts as $value) {
            $array = array();

            if ($value['post_type'] == 'attachment') {
                $url = explode('.', $value['guid']);
                $count = count($url);

                $array['original'] = implode('.', $url);
                $array['post_parent'] = $value['post_parent'];

                if (in_array(strtolower(end($url)), $mime_types)) {
                    foreach ($suffixes as $suffix) {
                        if ($count > 1) {
                            $_ = $url;

                            $_[$count - 2] = $_[$count - 2] . $suffix;

                            switch ($suffix) {
                                case '--w_100' : $suffix = 'w100'; break;
                                case '--w_150' : $suffix = 'w150'; break;
                                case '--w_300' : $suffix = 'w300'; break;
                                case '--w_400' : $suffix = 'w400'; break;
                                case '--w_500' : $suffix = 'w500'; break;
                            }

                            $array[$suffix] = implode('.', $_);
                        }
                    }

                    if (!empty($array)) {
                        $data[$value['ID']] = $array;
                    }
                } else {
                    $data[$value['ID']]['id'] = $value['ID'];
                    $data[$value['ID']]['post_parent'] = $value['post_parent'];
                    $data[$value['ID']]['url'] = $value['guid'];
                }
            }
        }

        return $data;
    }

    private function get_postmeta() {
        $data = array();

        foreach ($this->postmeta as $value) {
            $data[$value['post_id']][$value['meta_key']] = $value['meta_value'];
        }

        return $data;
    }

    /**
     * @return array
     * Огромный массив на 29 тыс записей
     */
    private function get_posts() {
        $data = array();

        foreach ($this->posts as $value) {
            $data[$value['ID']] = $value;
        }

        return $data;
    }

    /**
     * @return array
     * Массив продуктов на 600-700 записей
     */
    private function get_posts_product() {
        $data = array();

        foreach ($this->posts as $value) {
            if ($value['post_type'] == 'product') {
                $data[$value['ID']] = $value;
            }

        }

        return $data;
    }

    /**
     * @return array
     * Массив вариаций на 7000-8000 записей
     */
    private function get_posts_variations() {
        $data = array();

        foreach ($this->posts as $value) {
            if ($value['post_type'] == 'product_variation') {
                $data[$value['ID']] = $value;
            }

        }

        return $data;
    }

    private function get_posts_nav_menu() {
        $data = array();

        foreach ($this->posts as $value) {
            if ($value['post_type'] == 'nav_menu_item') {
                $data[$value['ID']] = $value;
            }

        }

        return $data;
    }

    private function get_posts_interiors() {
        $data = array();

        foreach ($this->posts as $value) {
            if ($value['post_type'] == 'interiors') {
                $data[$value['ID']] = $value;
            }

        }

        return $data;
    }

    private function get_posts_fabric() {
        $data = array();

        foreach ($this->posts as $value) {
            if ($value['post_type'] == 'fabric') {
                $data[$value['ID']] = $value;
            }

        }

        return $data;
    }

    /**
     * @return array
     * Массив портфолио
     */
    private function get_posts_portfolio() {
        $data = [];

        foreach ($this->posts as $value) {
            if ($value['post_type'] == 'portfolio') {
                $data[$value['ID']] = $value;
            }

        }

        return $data;
    }

    private function get_posts_by_post_name() {
        $data = array();

        foreach ($this->posts as $value) {
            if ($value['post_type'] != 'attachment') {
                $data[$value['post_name']] = $value;
            }
        }

        return $data;
    }

    private function get_term_relationships() {
        $data = array();

        foreach ($this->term_relationships as $value) {
            $data[$value['object_id']][] = $value;
        }

        return $data;
    }

    private function get_term_taxonomy() {
        $data = array();

        foreach ($this->term_taxonomy as $value) {
            $data[$value['term_taxonomy_id']] = $value;
        }

        return $data;
    }

    private function get_termmeta() {
        $data = array();

        foreach ($this->termmeta as $value) {
            $data[$value['term_id']][$value['meta_key']] = $value['meta_value'];
        }

        return $data;
    }

    private function get_terms() {
        $data = array();

        foreach ($this->terms as $value) {
            $data[$value['term_id']] = $value;
        }

        return $data;
    }

    private function get_terms_by_slug() {
        $data = array();

        foreach ($this->terms as $value) {
            $data[$value['slug']] = $value;
        }

        return $data;
    }

    private function get_woocommerce_attribute_taxonomies() {
        $data = array();

        foreach ($this->woocommerce_attribute_taxonomies as $value) {
            $data[$value['attribute_name']] = $value['attribute_label'];
        }

        return $data;
    }

    private function get_options() {
        $data = array();

        foreach ($this->options as $value) {
            $exploded_value = explode('_', $value['option_name']);

            if (count($exploded_value) > 2) {
                $data[$exploded_value[1]][$exploded_value[2]][$exploded_value[3]] = $value['option_value'];
            }
        }

        return $data;
    }

    /**
     * @return array
     * Метод собирает базовые настройки для комплектов
     * В метод
     */
    private function get_options_bases() {
        $data = [];

        foreach ($this->options_bases as $option_item) {
            $data[] = [
                'option_name' => $option_item['option_name'],
                'option_value' => $option_item['option_value']
            ];
        }

        $grouped_options = new GroupOptionsBases();

        $grouped_by_type = $grouped_options($data);

//        var_dump('============================', $grouped_by_type);

        return $grouped_by_type;
    }

    private function get_posts_ids() {
        $data = array();

        foreach ($this->posts as $value) {
            switch ($value['post_type']) {
                case 'fabric' :
                    $data['fabrics'][] = $value['ID'];

                    break;
                case 'product' :
                    $data['products'][] = $value['ID'];

                    break;
                case 'product_variation' :
                    $data['variations'][] = $value['ID'];

                    $data['variations_ids'][$value['post_parent']][] = $value['ID'];

                    break;

                case 'showroom' :
                    $data['showrooms'][] = $value['ID'];

                    break;

                case 'portfolio' :
                    $data['portfolio'][] = $value['ID'];

                    break;

                case 'interiors' :
                    $data['interiors'][] = $value['ID'];

                    break;
            }
        }

        return $data;
    }

    public function set_wordpress() {
        global $wordpress;

        $wordpress['attachments'] = $this->get_attachments();
        $wordpress['attachments_with_parent'] = $this->get_attachments_with_parent();
        $wordpress['postmeta'] = $this->get_postmeta();
        $wordpress['posts'] = $this->get_posts();
        $wordpress['products'] = $this->get_posts_product();
        $wordpress['posts_variations'] = $this->get_posts_variations();
        $wordpress['fabric'] = $this->get_posts_fabric();
        $wordpress['portfolio'] = $this->get_posts_portfolio();
        $wordpress['posts_by_post_name'] = $this->get_posts_by_post_name();
        $wordpress['term_relationships'] = $this->get_term_relationships();
        $wordpress['term_taxonomy'] = $this->get_term_taxonomy();
        $wordpress['termmeta'] = $this->get_termmeta();
        $wordpress['terms'] = $this->get_terms();
        $wordpress['terms_by_slug'] = $this->get_terms_by_slug();
        $wordpress['posts_ids'] = $this->get_posts_ids();
        $wordpress['woocommerce_attribute_taxonomies'] = $this->get_woocommerce_attribute_taxonomies();
        $wordpress['options'] = $this->get_options();
        $wordpress['options_bases'] = $this->get_options_bases();
        $wordpress['posts_nav_menu'] = $this->get_posts_nav_menu();
        $wordpress['posts_interiors'] = $this->get_posts_interiors();
    }
}