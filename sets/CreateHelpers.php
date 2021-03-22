<?php


namespace sets;


trait CreateHelpers {

    /**
     * @param $array
     * @return array
     * Подстчет количества различных комплектов для этого товара
     */
    public function get_count_sets($array) {
        foreach ($array as $key => $value) {
            if (preg_match("/^options_bases_\d*_sets$/", $key) == 1) {
                $count = $value;
            }
        }

        return $count;
    }

    /**
     * @param $array
     * @return mixed
     * Метод - роутер, направляет на сбор данных, в зависимости от группы
     * options_bases_5_sets_0_entities
     * Комплекты для сущности 5-й
     * Группа комплекта 0-я
     */
    public function get_group_options($array) {
        $group = [];

        foreach ($array as $key => $value) {
            if (preg_match("/^options_bases_\d*_sets_\d*_entities$/", $key) == 1) {
                $f = explode('_', $key)[2];
                $s = explode('_', $key)[4];
                $group['set_' . $s][] = self::get_complect($array, $f, $s);
            }
        }

        return $group;
    }


    static function group_by_ids($array, $f, $s, $t) {
        $data = [];

        foreach ($array as $key_option => $value_option) {
            if (preg_match("/^options_bases_($f)_sets_($s)_discount$/", $key_option) == 1) {
                $data['discount'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_entity_entity_ids$/", $key_option) == 1) {
                $data['ids_products'] = implode(',', unserialize($value_option));
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_maximum_amount$/", $key_option) == 1) {
                $data['maximum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_minimum_amount$/", $key_option) == 1) {
                $data['minimum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_max$/", $key_option) == 1) {
                $data['base_max'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_min$/", $key_option) == 1) {
                $data['base_min'] = $value_option;
            }
        }

        return $data;
    }

    /**
     * @param $array
     * @param $f
     * @param $s
     * @param $t
     * @return array
     * Не путать с коллекция+категория
     */
    static function group_by_collections($array, $f, $s, $t) {
        $data = [];

        foreach ($array as $key_option => $value_option) {
            if (preg_match("/^options_bases_($f)_sets_($s)_discount$/", $key_option) == 1) {
                $data['discount'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_entity_entity_collections$/", $key_option) == 1) {
                if (!empty($value_option)) {
                    $data['ids_collections'] = implode(',', unserialize($value_option));
                }
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_maximum_amount$/", $key_option) == 1) {
                $data['maximum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_minimum_amount$/", $key_option) == 1) {
                $data['minimum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_max$/", $key_option) == 1) {
                $data['base_max'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_min$/", $key_option) == 1) {
                $data['base_min'] = $value_option;
            }
        }

        return $data;
    }

    static function group_by_categories($array, $f, $s, $t) {
        $data = [];

        foreach ($array as $key_option => $value_option) {
            if (preg_match("/^options_bases_($f)_sets_($s)_discount$/", $key_option) == 1) {
                $data['discount'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_entity_entity_categories$/", $key_option) == 1) {
                if (!empty($value_option)) {
                    $data['ids_categories'] = implode(',', unserialize($value_option));
                }
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_maximum_amount$/", $key_option) == 1) {
                $data['maximum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_minimum_amount$/", $key_option) == 1) {
                $data['minimum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_max$/", $key_option) == 1) {
                $data['base_max'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_min$/", $key_option) == 1) {
                $data['base_min'] = $value_option;
            }
        }

        return $data;
    }

    /**
     * @param $array
     * @param $f
     * @param $s
     * @param $t
     * @return array
     * Если добавляются опции по категория+коллекция, то если коллекция не заполнена, тогда use_parent_collection = 1
     * Если коллекция заполнена, тогда родительская коллекция не сработает (даже при учете галочки в админке)
     */
    static function group_by_catcol($array, $f, $s, $t) {
        $data = [];

        foreach ($array as $key_option => $value_option) {
            if (preg_match("/^options_bases_($f)_sets_($s)_discount$/", $key_option) == 1) {
                $data['discount'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_entity_entity_categories$/", $key_option) == 1) {
                if (!empty($value_option)) {
                    $data['ids_categories'] = implode(',', unserialize($value_option));
                }
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_entity_entity_collections$/", $key_option) == 1) {
                if (!empty($value_option)) {
                    $data['ids_collections'] = implode(',', unserialize($value_option));
                } else {
                    $data['use_parent_collection'] = 1;
                }
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_maximum_amount$/", $key_option) == 1) {
                $data['maximum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_minimum_amount$/", $key_option) == 1) {
                $data['minimum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_max$/", $key_option) == 1) {
                $data['base_max'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_min$/", $key_option) == 1) {
                $data['base_min'] = $value_option;
            }
        }

        return $data;
    }

    static function group_by_required_ids($array, $f, $s, $t) {
        $data = [];

        foreach ($array as $key_option => $value_option) {
            if (preg_match("/^options_bases_($f)_sets_($s)_discount$/", $key_option) == 1) {
                $data['discount'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_required_entities_($t)_entity_entity_ids$/", $key_option) == 1) {
                $data['required_ids'] = implode(',', unserialize($value_option));
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_maximum_amount$/", $key_option) == 1) {
                $data['maximum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_entities_($t)_minimum_amount$/", $key_option) == 1) {
                $data['minimum'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_max$/", $key_option) == 1) {
                $data['base_max'] = $value_option;
            }
            if (preg_match("/^options_bases_($f)_sets_($s)_base_quantity_min$/", $key_option) == 1) {
                $data['base_min'] = $value_option;
            }
        }

        return $data;
    }

    /**
     * @param $array
     * @param $f
     * @param $s
     * @return array
     * Сбор опций внутри каждой группы.
     * Всего 5 групп: by_ids, by_collections, by_categories, by_catcol, by_required_ids
     */
    static function get_complect($array, $f, $s) {
        $data = [];

        foreach ($array as $key => $value) {

            if (preg_match("/^options_bases_($f)_sets_($s)_entities_\d*_entity_entity_type$/", $key) == 1) {
                if ($array[$key] == 'e_ids') {
                    $t = explode('_', $key)[6];
                    $data['group_by_ids'][] = self::group_by_ids($array, $f, $s, $t);
                }

                if ($array[$key] == 'e_cols') {
                    $t = explode('_', $key)[6];
                    $data['group_by_collections'][] = self::group_by_collections($array, $f, $s, $t);
                }

                if ($array[$key] == 'e_cats') {
                    $t = explode('_', $key)[6];
                    $data['group_by_categories'][] = self::group_by_categories($array, $f, $s, $t);
                }

                if ($array[$key] == 'e_catcols') {
                    $t = explode('_', $key)[6];
                    $data['group_by_catcol'][] = self::group_by_catcol($array, $f, $s, $t);
                }

            }

            if (preg_match("/^options_bases_($f)_sets_($s)_required_entities_\d*_entity_entity_type$/", $key) == 1) {
                if ($array[$key] == 'e_ids') {
                    $t = explode('_', $key)[7];
                    $data['group_by_required_ids'][] = self::group_by_required_ids($array, $f, $s, $t);
                }
            }

        }

        return $data;
    }
}