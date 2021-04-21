<?php


class GetIdsForChangePrice {

    private $dbLocale;

    public function __construct() {
        $this->admin = new PDO("mysql:host=localhost;dbname=price_sk;charset=utf8mb4", 'root', 'root');
        $this->admin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->admin->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getIds() {

        $ids = $this->admin->query("SELECT * FROM wp_postmeta
                WHERE post_id IN (SELECT ID
                  FROM wp_posts
                  WHERE post_parent IN (
                      SELECT ID
                      FROM wp_posts
                      WHERE ID IN (
                          SELECT object_id
                          FROM wp_term_relationships
                          WHERE term_taxonomy_id = 1306)
                        AND post_type = 'product'
                        AND post_status = 'publish')
                    AND post_type = 'product_variation')
                AND meta_key = '_regular_price';")->fetchAll(PDO::FETCH_ASSOC);

        return $ids;
    }

}

$itemProduct = new GetIdsForChangePrice();

$count = 0;
foreach ($itemProduct->getIds() as $item) {
    $array[] = (string)$item['meta_id'];
    $count++;
    $amount[] = $count;
}

$fd = fopen("ids_price.txt", 'w') or die("не удалось создать файл");
$str = implode(',', $array);
fputs($fd, $str);
fclose($fd);

var_dump($amount);