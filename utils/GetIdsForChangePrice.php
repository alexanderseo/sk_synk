<?php


class GetIdsForChangePrice {

    private $dbLocale;

    public function __construct() {
//        $this->admin_old = new PDO("mysql:host=localhost;dbname=price_sk;charset=utf8mb4", 'root', 'root');
//        $this->admin_old->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//        $this->admin_old->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->admin_new = new PDO("mysql:host=130.193.62.187;dbname=skdesign;charset=utf8mb4", 'skdesign', '1qaZse4rfVgy7');
        $this->admin_new->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->admin_new->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getIds() {

        $ids = $this->admin_new->query("SELECT * FROM wp_postmeta
                WHERE post_id IN (SELECT ID
                  FROM wp_posts
                  WHERE post_parent IN (
                      SELECT ID
                      FROM wp_posts
                      WHERE ID IN (
                          SELECT object_id
                          FROM wp_term_relationships
                          WHERE term_taxonomy_id = 6918)
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