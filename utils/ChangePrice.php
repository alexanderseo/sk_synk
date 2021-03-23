<?php

require_once '../database.php';

class ChangePrice {

    private static $instance;
    private $db;
    private $postmeta_price;
    private $data;

    public function __construct() {

        $this->db = new database();
        $this->postmeta_price = $this->db->admin->query("SELECT * FROM test_price_wp_postmeta WHERE meta_key = '_regular_price';")->fetchAll(PDO::FETCH_ASSOC);
        $this->data = [];
    }

    public function get_all_rows_price(float $percent) {


        foreach ($this->postmeta_price as $item) {
            $price = (int)$item['meta_value'] * $percent;

            $this->data[$item['meta_id']] = [
                'meta_id' => $item['meta_id'],
                'post_id' => $item['post_id'],
                'meta_key' => $item['meta_key'],
                'meta_value' => round($price),
            ];
        }

        return $this->data;
    }

}

$meta = new ChangePrice();

$x = $meta->get_all_rows_price(1.0025);

var_dump($x);