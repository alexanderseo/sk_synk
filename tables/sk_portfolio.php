<?php


class sk_portfolio extends bootstrap {

    private static $instance;
    private $portfolio;
    private $postmeta;
    private $attachments;
    private $portfolio_data;
    private $ids_portfolio;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->portfolio = $wordpress['portfolio'];
        $this->postmeta = $wordpress['postmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->ids_portfolio = $wordpress['posts_ids']['portfolio'];
        $this->portfolio_data = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->ids_portfolio as $id) {
            $post_portfolio = $this->portfolio[$id];
            $this_postmeta = $this->postmeta[$id];

            $this->set_id('id', $id);
            $this->set_name('name', $id, $post_portfolio);
            $this->set_slug('slug', $id, $post_portfolio);
            $this->set_content('content', $id, $post_portfolio);
            $this->set_status('status', $id, $post_portfolio);
            $this->set_modified_unix('modified_unix', $id, $post_portfolio);
            $this->set_image('image', $id, $this_postmeta, $this->attachments);

        }

        return $this->portfolio_data;
    }

    private function get_id($id) {
        return $id;
    }

    private function get_name($portfolio) {
        return isset($portfolio['post_title']) ? $portfolio['post_title'] : "";
    }

    private function get_slug($portfolio) {
        return isset($portfolio['post_name']) ? $portfolio['post_name'] : "";
    }

    private function get_content($portfolio) {
        return isset($portfolio['post_content']) ? $portfolio['post_content'] : "";
    }

    private function get_status($portfolio) {
        return isset($portfolio['post_status']) ? $portfolio['post_status'] : "";
    }

    private function get_modified_unix($portfolio) {
        return isset($portfolio['post_modified']) ? strtotime($portfolio['post_modified']) : "";
    }

    private function get_image($postmeta, $attachments) {
        $data = [];

        if (isset($postmeta['_thumbnail_id'])) {
            if (isset($attachments[$postmeta['_thumbnail_id']])) {
                $data['original'] = $attachments[$postmeta['_thumbnail_id']]['original'];
            }
        }

        return serialize($data);
    }

    private function set_id($key, $id) {
        $this->portfolio_data[$id][$key] = $this->get_id($id);
    }

    private function set_name($key, $id, $post_portfolio) {
        $this->portfolio_data[$id][$key] = $this->get_name($post_portfolio);
    }

    private function set_slug($key, $id, $post_portfolio) {
        $this->portfolio_data[$id][$key] = $this->get_slug($post_portfolio);
    }

    private function set_content($key, $id, $post_portfolio) {
        $this->portfolio_data[$id][$key] = $this->get_content($post_portfolio);
    }

    private function set_status($key, $id, $post_portfolio) {
        $this->portfolio_data[$id][$key] = $this->get_status($post_portfolio);
    }

    private function set_modified_unix($key, $id, $post_portfolio): void {
        $this->portfolio_data[$id][$key] = $this->get_modified_unix($post_portfolio);
    }

    private function set_image($key, $id, $this_postmeta, $attachments) {
        $this->portfolio_data[$id][$key] = $this->get_image($this_postmeta, $attachments);
    }
}