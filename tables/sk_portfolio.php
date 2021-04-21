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
            $this->set_content_block('content_block', $id, $this_postmeta, $this->attachments);

        }

        return $this->portfolio_data;
    }

    private function get_id($id) {
        return $id;
    }

    private function get_name($portfolio) {
        return $portfolio['post_title'] ?? "";
    }

    private function get_slug($portfolio) {
        return $portfolio['post_name'] ?? "";
    }

    private function get_content($portfolio) {
        return $portfolio['post_content'] ?? "";
    }

    private function get_status($portfolio) {
        return $portfolio['post_status'] ?? "";
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

    private function get_content_block($postmeta, $attachments) {
        $data = [];

        $count = $this->get_count_block($postmeta);
        if ($count == 0) return "";

        $ids_images = $this->get_ids_images($postmeta);
        $this_array_images = $this->get_this_images($ids_images, $attachments);

        for ($i = 0; $i < $count; $i++) {
            foreach ($postmeta as $name_key => $value_item) {
                if (stripos($name_key, 'blocks_' . $i . '_text') === 0) {
                    $data[$i]['text'][$name_key] = $value_item;
                }
                if (stripos($name_key, 'blocks_' . $i . '_image') === 0) {
                    $data[$i]['image'][$name_key] = $this->add_image($value_item, $this_array_images);
                }
            }
        }

        return serialize($data);
    }

    private function get_count_block($postmeta): int {
        $count = 0;

        $count = $postmeta['blocks'] ?? 0;

        return $count;
    }

    private function get_ids_images($postmeta): array {
        $data = [];

        foreach ($postmeta as $name_key => $value_item) {
            if (stripos($name_key, '_image-') == 8) {
                $data[] = $value_item;
            }
        }

        return $data;
    }

    private function get_this_images($ids_images, $attachments) {
        $data = [];

        foreach ($ids_images as $key_id => $value_id) {
            if (isset($attachments[$value_id])) {
                $data[$value_id] = $attachments[$value_id]['original'] ?? "";
            }
        }

        return $data;
    }

    private function add_image($id_img, $attachments): string {
        $result = "";

        if (empty($attachments)) return "";

        foreach ($attachments as $key_img => $url_img) {
            if ($key_img == $id_img) {
                $result = $url_img;
            }
        }

        return $result;
    }

    private function set_id($key, $id): void {
        $this->portfolio_data[$id][$key] = $this->get_id($id);
    }

    private function set_name($key, $id, $post_portfolio): void {
        $this->portfolio_data[$id][$key] = $this->get_name($post_portfolio);
    }

    private function set_slug($key, $id, $post_portfolio): void {
        $this->portfolio_data[$id][$key] = $this->get_slug($post_portfolio);
    }

    private function set_content($key, $id, $post_portfolio): void {
        $this->portfolio_data[$id][$key] = $this->get_content($post_portfolio);
    }

    private function set_status($key, $id, $post_portfolio): void {
        $this->portfolio_data[$id][$key] = $this->get_status($post_portfolio);
    }

    private function set_modified_unix($key, $id, $post_portfolio): void {
        $this->portfolio_data[$id][$key] = $this->get_modified_unix($post_portfolio);
    }

    private function set_image($key, $id, $this_postmeta, $attachments): void {
        $this->portfolio_data[$id][$key] = $this->get_image($this_postmeta, $attachments);
    }

    private function set_content_block($key, $id, $this_postmeta, $attachments): void {
        $this->portfolio_data[$id][$key] = $this->get_content_block($this_postmeta, $attachments);
    }
}