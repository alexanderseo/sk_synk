<?php
require_once 'synchronization.php';

require_once 'tables/sk_attachments.php';
require_once 'tables/sk_categories.php';
require_once 'tables/sk_fabrics.php';
require_once 'tables/sk_options.php';
require_once 'tables/sk_showrooms.php';
require_once 'tables/sk_stock_products.php';
require_once 'tables/sk_variations.php';
require_once 'tables/sk_product.php';
require_once 'tables/sk_sets.php';
require_once 'tables/sk_sets_table.php';
require_once 'tables/sk_materials.php';
require_once 'tables/sk_type_materials.php';
require_once 'tables/sk_products_categories_relashionships.php';
require_once 'tables/sk_portfolio.php';
require_once 'tables/sk_expo_products.php';
require_once 'tables/sk_nav_menu.php';
require_once 'tables/sk_interiors.php';
require_once 'tables/sk_product_collections.php';
require_once 'tables/sk_cart_upsell.php';
require_once 'tables/sk_popular_products.php';
require_once 'tables/sk_page.php';
require_once 'tables/sk_promo.php';
require_once 'tables/sk_photo_pokupatelei.php';


class bootstrap {
    public $database;

    public $sk_attachments;
    public $sk_categories;
    public $sk_fabrics;
    public $sk_materials;
    public $sk_options;
    public $sk_showrooms;
    public $sk_stock_products;
    public $sk_variations;
    public $sk_product;
    public $sk_sets;
    public $sk_sets_table;
    public $sk_type_materials;
    public $sk_products_categories_relashionships;
    public $sk_portfolio;
    public $sk_expo_products;
    public $sk_nav_menu;
    public $sk_interiors;
    public $sk_product_collections;
    public $sk_cart_upsell;
    public $sk_popular_products;
    public $sk_page;
    public $sk_promo;
    public $sk_photo_pokupatelei;


    public function __construct() {
        $this->database = database::get_instance();
    }

    public function start() {
        $this->clear_log();

        $wordpress = wordpress::get_instance();
        $synchronization = synchronization::get_instance();

        $wordpress->set_wordpress();

        $this->sk_attachments = sk_attachments::get_instance();
        $this->sk_categories = sk_categories::get_instance();
        $this->sk_fabrics = sk_fabrics::get_instance();
//        $this->sk_materials = sk_materials::get_instance();
//        $this->sk_options = sk_options::get_instance();
//        $this->sk_showrooms = sk_showrooms::get_instance();
//        $this->sk_stock_products = sk_stock_products::get_instance();
//        $this->sk_variations = sk_variations::get_instance();
//        $this->sk_product = sk_product::get_instance();
//        $this->sk_sets = sk_sets::get_instance();
//        $this->sk_sets_table = sk_sets_table::get_instance();
//        $this->sk_type_materials = sk_type_materials::get_instance();
//        $this->sk_products_categories_relashionships = sk_products_categories_relashionships::get_instance();
//        $this->sk_portfolio = sk_portfolio::get_instance();
//        $this->sk_expo_products = sk_expo_products::get_instance();
//        $this->sk_nav_menu = sk_nav_menu::get_instance();
//        $this->sk_interiors = sk_interiors::get_instance();
//        $this->sk_product_collections = sk_product_collections::get_instance();
//        $this->sk_cart_upsell = sk_cart_upsell::get_instance();
//        $this->sk_popular_products = sk_popular_products::get_instance();
//        $this->sk_page = sk_page::get_instance();
//        $this->sk_promo = sk_promo::get_instance();
//        $this->sk_photo_pokupatelei = sk_photo_pokupatelei::get_instance();

        $this->sk_attachments = $this->sk_attachments->get();
        $this->sk_categories = $this->sk_categories->get();
        $this->sk_fabrics = $this->sk_fabrics->get();
//        $this->sk_materials = $this->sk_materials->get();
//        $this->sk_options = $this->sk_options->get();
//        $this->sk_showrooms = $this->sk_showrooms->get();
//        $this->sk_stock_products = $this->sk_stock_products->get($this->sk_fabrics, $this->sk_materials);
//        $this->sk_variations = $this->sk_variations->get($this->sk_fabrics);
//        $this->sk_product = $this->sk_product->get($this->sk_variations, $this->sk_categories, $this->sk_fabrics, $this->sk_materials);
//        $this->sk_sets_table = $this->sk_sets_table->get();
//        $this->sk_type_materials = $this->sk_type_materials->get();
//        $this->sk_products_categories_relashionships = $this->sk_products_categories_relashionships->get();
//        $this->sk_portfolio = $this->sk_portfolio->get();
//        $this->sk_expo_products = $this->sk_expo_products->get($this->sk_fabrics, $this->sk_materials);
//        $this->sk_nav_menu = $this->sk_nav_menu->get($this->sk_categories);
//        $this->sk_interiors = $this->sk_interiors->get();
//        $this->sk_product_collections = $this->sk_product_collections->get();
//        $this->sk_cart_upsell = $this->sk_cart_upsell->get();
//        $this->sk_popular_products = $this->sk_popular_products->get();
//        $this->sk_page = $this->sk_page->get();
//        $this->sk_promo = $this->sk_promo->get();
//        $this->sk_photo_pokupatelei = $this->sk_photo_pokupatelei->get();

//        $synchronization->start(
//            $this->sk_attachments,
//            $this->sk_categories,
//            $this->sk_fabrics,
//            $this->sk_variations,
//            $this->sk_product,
//            $this->sk_options,
//            $this->sk_showrooms,
//            $this->sk_stock_products,
//            $this->sk_materials,
//            $this->sk_type_materials,
//            $this->sk_products_categories_relashionships,
//            $this->sk_sets_table,
//            $this->sk_portfolio,
//            $this->sk_expo_products,
//            $this->sk_nav_menu,
//            $this->sk_interiors,
//            $this->sk_product_collections,
//            $this->sk_cart_upsell,
//            $this->sk_popular_products,
//            $this->sk_page,
//            $this->sk_promo
//        );
    }

    private function clear_log() {
        file_put_contents('wp-log.txt', '');
    }

    public function set_log($array) {
        foreach ($array as $value) {
            error_log('[' . date("m.d.Y, H:i:s") . '] : ' . $value[0] . ' : ' . $value[1] . ' : ' . $value[2] . ' : ' . $value[3] . PHP_EOL, 3, 'wp-log.txt');
        }
    }
}

$start = microtime(true);
$start_memory = memory_get_usage();

$bootstrap = new bootstrap();

$bootstrap->start();

echo 'Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.' . '</br>';
echo 'Потребляемая память: ' . ceil((((memory_get_usage() - $start_memory) / 1024) / 1024)) . ' мегабайт';