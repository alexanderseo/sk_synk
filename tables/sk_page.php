<?php


class sk_page extends bootstrap {
    private static $instance;

    private $data;
    private $pages;
    private $postmeta;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->pages = $wordpress['page'];
        $this->postmeta = $wordpress['postmeta'];
        $this->data = [];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(): array {

        foreach ($this->pages as $page) {
            $meta_array = $this->get_meta($page, $this->postmeta);

            $this->set_id('id', $page);
            $this->set_modified_unix('modified_unix', $page);
            $this->set_content('content', $page);
            $this->set_title('title', $page, $meta_array);
            $this->set_description('description', $page, $meta_array);
            $this->set_slug('slug', $page);
        }

        return $this->data;
    }

    private function get_meta($page, $postmeta): array {
        $data = [];

        if (isset($postmeta[$page['ID']])) {
            $data = $postmeta[$page['ID']];
        }

        return $data;
    }

    private function get_id($page): int {
        return (int) $page['ID'];
    }

    private function get_modified_unix($page): string {
        return strtotime($page['post_modified']) . $page['ID'];
    }

    private function get_content($page): string {
        return $page['post_content'] ?? "";
    }

    private function get_title($meta): string {
        return $meta['_yoast_wpseo_title'] ?? "";
    }

    private function get_description($meta): string {
        return $meta['_yoast_wpseo_metadesc'] ?? "";
    }

    private function get_slug($page): string {
        return $page['post_name'] ?? "";
    }

    private function set_id($key, $page): void {
        $this->data[$page['ID']][$key] = $this->get_id($page);
    }

    private function set_modified_unix($key, $page): void {
        $this->data[$page['ID']][$key] = $this->get_modified_unix($page);
    }

    private function set_content($key, $page): void {
        $this->data[$page['ID']][$key] = $this->get_content($page);
    }

    private function set_title($key, $page, $meta): void {
        $this->data[$page['ID']][$key] = $this->get_title($meta);
    }

    private function set_description($key, $page, $meta): void {
        $this->data[$page['ID']][$key] = $this->get_description($meta);
    }

    private function set_slug($key, $page): void {
        $this->data[$page['ID']][$key] = $this->get_slug($page);
    }
}