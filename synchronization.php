<?php
class synchronization {
    private static $instance;

    private $database;

    public function __construct() {
        $this->database = database::get_instance();
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function start(
        $sk_attachments,
        $sk_categories,
        $sk_fabrics,
        $sk_variations,
        $sk_products,
        $sk_options,
        $sk_showrooms,
        $sk_stock_products,
        $sk_materials,
        $sk_type_materials,
        $sk_products_categories_relashionships,
        $sk_sets_table,
        $sk_portfolio,
        $sk_expo_products,
        $sk_nav_menu,
        $sk_interiors,
        $sk_product_collections,
        $sk_cart_upsell,
        $sk_popular_products,
        $sk_page
    ) {
        $tables = array(
            'attachments' => $sk_attachments,
            'categories' => $sk_categories,
            'fabrics' => $sk_fabrics,
            'variations' => $sk_variations,
            'products' => $sk_products,
            'options' => $sk_options,
            'showrooms' => $sk_showrooms,
            'stock_products' => $sk_stock_products,
            'material' => $sk_materials,
            'type_material' => $sk_type_materials,
            'products_categories_relashionships' => $sk_products_categories_relashionships,
            'sets_table' => $sk_sets_table,
            'portfolio' => $sk_portfolio,
            'expo_products' => $sk_expo_products,
            'nav_menu' => $sk_nav_menu,
            'interiors' => $sk_interiors,
            'sk_product_collections' => $sk_product_collections,
            'cart_upsell' => $sk_cart_upsell,
            'popular_products' => $sk_popular_products,
            'sk_page' => $sk_page
        );

        foreach ($tables as $key => $value) {
            $wp_table_formatted = array();

            $wp_table = $this->database->development->query("SELECT * FROM {$key}")->fetchAll();

            foreach ($wp_table as $_key => $_value) {
                $wp_table_formatted[$_value['id']] = $_value;
            }


            foreach ($value as $_key => $_value) {
                if (isset($wp_table_formatted[$_key])) {

//                    $result = array_diff_assoc($_value, $wp_table_formatted[$_key]);
                    $result = $this->arrayRecursiveDiff($_value, $wp_table_formatted[$_key]);

                    if (!empty($result)) {
                        $values = array();

                        foreach ($result as $__key => $__value) {
                            switch ($__key) {
                                case 'id' : $values[] = "`" . $__key . "` = " . $__value; break;
                                default : $values[] = "`" . $__key . "` = '" . $__value . "'"; break;
                            }
                        }

                        $imploded_values = implode(', ', $values);

                        $this->database->development->query("UPDATE {$key} SET $imploded_values WHERE id = {$_key}");
                    }
                } else {
                    $columns = array();
                    $values = array();

                    foreach ($_value as $__key => $__value) {

                        $columns[] = "`" . $__key . "`";

                        if (is_array($__value)) {
                            var_dump('Значение массив, должна быть строка', $__value);
                            var_dump('Этот ключ косячит', $__key);
                        }

                        switch ($__key) {
                            case 'id' : $values[] = (int) $__value; break;
                            default : $values[] = "'" . $__value . "'"; break;
                        }
                    }

                    $imploded_columns = implode(', ', $columns);
                    $imploded_values = implode(', ', $values);


                    try {
                    $this->database->development->query("INSERT INTO {$key} ($imploded_columns) VALUES ($imploded_values)");
                    } catch (\PDOException $e) {
                        var_dump('+++', $values);
                        var_dump('====', $imploded_columns);
                        var_dump('////', $imploded_values);
                        var_dump('---', $e);
                    }
                }
            }

            foreach ($wp_table_formatted as $_value) {
                if (!isset($value[$_value['id']])) {
                    $this->database->development->query("DELETE FROM {$key} WHERE id = {$_value['id']}");
                }
            }
        }
    }

    public function arrayRecursiveDiff($aArray1, $aArray2) {
        $aReturn = array();
        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }
}