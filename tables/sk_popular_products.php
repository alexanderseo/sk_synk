<?php


class sk_popular_products extends bootstrap {

    private static $instance;

    private $data_popular;
    private $popular_options;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->data_popular = [];
        $this->popular_options = $wordpress['options_tabs'];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        $data_name = [];
        $data_ids = [];
        foreach ($this->popular_options as $option_item) {
            for ($i = 0; $i < 7; $i++) {
                if (strpos($option_item['option_name'], 'options_tabs_' . $i . '_tab_title') === 0) {
                    $data_name[$i] = [
                        'name' => $option_item['option_value'],
                    ];
                }

                if (strpos($option_item['option_name'], 'options_tabs_' . $i . '_tab_products') === 0) {
                    $data_ids[$i] = [
                        'ids' => $option_item['option_value'],
                    ];
                }
            }
        }

        $this->data_popular = $this->union_array($data_name, $data_ids);

        return $this->data_popular;
    }

    private function union_array($data_name, $data_ids) {
        $data = [];

        foreach ($data_name as $key_name => $value_name) {
            foreach ($data_ids as $key_ids => $value_ids) {
                if ($key_name == $key_ids) {
                    $data[] = [
                        'id' => $key_name,
                        'name' => $value_name['name'],
                        'ids' => $value_ids['ids']
                    ];
                }
            }
        }

        return $data;
    }

}