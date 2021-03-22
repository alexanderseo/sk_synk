<?php


namespace sets;

require_once 'CreateHelpers.php';

class CreateBodySetCatCol {

    private $body;

    use CreateHelpers;

    public function __construct() {

        $this->body = [];

    }

    public function __invoke($array_options) {

        foreach ($array_options['sets_by_catcol'] as $key => $item_array) {
            $this->set_count($key, $item_array);
            $this->set_groups($key, $item_array);
        }

        return $this->body;
    }

    public function set_count($key, $array) {
        $this->body['sets_by_catcol'][$key]['count_sets'] = $this->get_count_sets($array);
    }

    public function set_groups($key, $array) {
        $this->body['sets_by_catcol'][$key]['groups'] = $this->get_group_options($array);
    }
}