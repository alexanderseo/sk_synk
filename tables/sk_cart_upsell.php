<?php

class sk_cart_upsell extends bootstrap {
    private static $instance;

    private $data;
    private $options_cart;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->data = [];
        $this->options_cart = $wordpress['options_cart'];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        if (!empty($this->options_cart)) {
            foreach($this->options_cart as $key_option => $item_option) {

                $this->set_id('id', $key_option);
                $this->set_ids('ids', $key_option, $item_option);

            }
        }

        return $this->data;
    }

    private function get_id($id): int {
        return (int) $id;
    }

    private function get_ids(array $options): string {
        $ids = "";

        if (!empty($options)) {
            if (isset($options['option_value'])) {
                    $ids = implode(',', unserialize($options['option_value']));
            }
        }

        return $ids;
    }

    private function set_id(string $key, string $id): void {
        $this->data[$id][$key] = $this->get_id($id);
    }

    private function set_ids(string $key, string $id, array $options): void {
        $this->data[$id][$key] = $this->get_ids($options);
    }
}