<?php

require_once '../database.php';

class ChangePrice {

    private static $instance;
    private $db;
    private $postmeta_price;
    private $data;

    public function __construct() {

        $this->db = new database();
        $this->postmeta_price = $this->db->admin->query("SELECT * FROM wp_postmeta WHERE meta_key = '_regular_price';")->fetchAll(PDO::FETCH_ASSOC);
        $this->data = [];
    }

    public function get_all_rows_price(float $percent) {


        foreach ($this->postmeta_price as $item) {
            $price = (int)$item['meta_value'] * $percent;

            $this->data[$item['meta_id']] = [
                'meta_id' => $item['meta_id'],
                'post_id' => $item['post_id'],
                'meta_key' => $item['meta_key'],
                'meta_value' => round($price, -2),
            ];
        }

        return $this->data;
    }

    public function update_price($array_meta) {
        $count = 0;
        foreach ($array_meta as $key => $item_meta) {
            $id = (int)$key;
            $value_price = (int)$item_meta['meta_value'];

            try {
                $status = $this->db->admin->query("UPDATE wp_postmeta SET meta_value = $value_price WHERE meta_id = $id");
                var_dump($status);
                $count++;
                echo ' Count: ' . $count;
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

//            $this->db->admin->query("UPDATE test_price_wp_postmeta SET meta_value = 120000 WHERE meta_id = 4595238");
        }

    }
}

$meta = new ChangePrice();

$array_meta_prices = $meta->get_all_rows_price(1.05);

var_dump($array_meta_prices);

$meta->update_price($array_meta_prices);

echo " - OK";