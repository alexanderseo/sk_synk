<?php

class sk_options extends bootstrap {
    private static $instance;

    private $log;
    private $options;
    private $attachments;
    private $data;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = [];
        $this->options = $wordpress['options'];
        $this->attachments = $wordpress['attachments'];
        $this->data = [];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        $this->set_instagram_posts_list($this->options);
        $this->set_kits($this->options);
        $this->set_superior_slider_items($this->options, $this->attachments);

        return $this->data;
    }

    private function set_instagram_posts_list($options) {
        $data = [];

        if (isset($options['instagram-post-list'])) {
            $this->data[1]['id'] = 1;
            $this->data[1]['slug'] = 'instagram_posts_list';

            foreach ($options['instagram-post-list'] as $option) {
                $data[] = $option['instagram-post'];
            }

            $this->data[1]['options'] = serialize($data);
        }
    }

    private function set_kits($options) {
        $data = [];

        $this->data[2]['id'] = 2;
        $this->data[2]['slug'] = 'kits';

        if (isset($options['kits'])) {
            foreach ($options['kits'] as $option) {
                $data[$option['child-category']] = $option;
            }
        }

        $this->data[2]['options'] = serialize($data);
    }

    private function set_superior_slider_items($options, $attachments) {
        $data = [];

        $this->data[3]['id'] = 3;
        $this->data[3]['slug'] = 'superior_slider_items';

        if (isset($options['superior-slider-items'])) {
            foreach ($options['superior-slider-items'] as $key => $value) {
                $data[$key]['superior-slide-desktop'] = $attachments[$value['superior-slide-desktop']];
                $data[$key]['superior-slide-mobile'] = $attachments[$value['superior-slide-mobile']];
                $data[$key]['superior-slide-hide'] = $value['superior-slide-hide'];
                $data[$key]['superior-slide-url'] = $value['superior-slide-url'];
            }
        }

        $this->data[3]['options'] = serialize($data);
    }
}