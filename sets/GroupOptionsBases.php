<?php

namespace sets;

require_once 'CreateBodySetById.php';
require_once 'CreateBodySetByIdCategory.php';
require_once 'CreateBodySetByIdCollection.php';
require_once 'CreateBodySetCatCol.php';

/**
 * Class GroupOptionsBases
 * @package includes
 * Базовый класс группировки опций комплектов
 */
class GroupOptionsBases {
    private $sets;

    public function __construct() {

        $this->sets = [];

    }

    public function __invoke($array_options) {

        $ids_array = $this->get_array_indexes($array_options);
        $search_worlds = $this->create_search_str($ids_array, 'options_bases_');
        $group_options = $this->group_options($search_worlds, $array_options);
        $grouped_by_type = $this->group_option_by_type($group_options);

        if ($grouped_by_type) {
            if (isset($grouped_by_type['id'])) {
                $grouped_by_id_product = $this->set_group_by_id($grouped_by_type['id']);
                $createBodyById = new CreateBodySetById();
                $this->sets[] = $createBodyById($grouped_by_id_product);
            }

            if (isset($grouped_by_type['cat'])) {
                $grouped_by_id_category = $this->set_group_by_id_category($grouped_by_type['cat']);
                $createBodyByIdCategory = new CreateBodySetByIdCategory();
                $this->sets[] = $createBodyByIdCategory($grouped_by_id_category);
            }

            if (isset($grouped_by_type['col'])) {
                $grouped_by_id_collection = $this->set_group_by_id_collection($grouped_by_type['col']);
                $createBodyByIdCollection = new CreateBodySetByIdCollection();
                $this->sets[] = $createBodyByIdCollection($grouped_by_id_collection);
            }

            if (isset($grouped_by_type['catcol'])) {
                $grouped_by_catcol = $this->set_group_by_catcol($grouped_by_type['catcol']);
                $createBodyByCatCol = new CreateBodySetCatCol();
                $this->sets[] = $createBodyByCatCol($grouped_by_catcol);
            }
        }



        return $this->sets;
    }

    /**
     * @return array
     * Получаем массив индексов настроек
     * то есть получаем 2-йку от сюда options_bases_2_sets_0_entities_0_entity_entity_categories
     */
    private function get_array_indexes($array_options) {
        $array_indexes = [];

        foreach ($array_options as $option_item) {
            $array_indexes[] =  $this->get_index_bases($option_item['option_name']);
        }

        $data_out = array_unique($array_indexes);
        $ids_array = array_diff($data_out, array("null", ""));

        return $ids_array;
    }

    /**
     * @param $str
     * @return mixed|string
     * Делим строку по нижнему подчеркиванию и получаем 3 значение
     */
    private function get_index_bases($str) {
        $index = explode('_', $str);

        return $index[2];
    }

    /**
     * @param $ids_array
     * @param $search_str
     * @return array
     * Создаем массив из строк, которые служат началом для ключей в опциях
     * То есть "options_bases_ + число"
     */
    private function create_search_str($ids_array, $search_str) {
        $array_search_world = [];

        foreach ($ids_array as $id) {
            $array_search_world[] = $search_str . $id;
        }

        return $array_search_world;
    }

    private function group_options($search_worlds, $array_options) {
        $group = [];

        foreach ($search_worlds as $search_item) {
            foreach ($array_options as $option_item) {
                if (strpos($option_item['option_name'], $search_item) === 0) {
                    $group[$search_item][$option_item['option_name']] = $option_item['option_value'];
                }
            }
        }

        return $group;
    }

    /**
     * @param $group_array
     * @return array
     * Группировка опций по типу сущности
     * На данный момент 4 типа сущности
     * Категория, коллекция, конкретный товар, категория+коллекция
     */
    private function group_option_by_type($group_array) {
        $grouped_options = [];

        foreach ($group_array as $key_option => $value_option) {
            if (isset($value_option[$key_option . '_base_entity_base_type'])) {
                if ($value_option[$key_option . '_base_entity_base_type'] == 'cat') {
                    $grouped_options['cat'][] = $value_option;
                } elseif ($value_option[$key_option . '_base_entity_base_type'] == 'col') {
                    $grouped_options['col'][] = $value_option;
                } elseif ($value_option[$key_option . '_base_entity_base_type'] == 'id') {
                    $grouped_options['id'][] = $value_option;
                } else {
                    $grouped_options['catcol'][] = $value_option;
                }
            }
        }

        return $grouped_options;
    }


    /**
     * @param $array
     * Получаем массив для будущей группировки настроек по id товара
     * Ключ массива - индекс настроек на 2-й позиции, значение - id товара
     */
    public function set_group_by_id($array) {
        $indexes = [];

        foreach ($array as $item_key => $item_value) {
            foreach ($item_value as $option_key => $option_value) {
                $indexes[] = self::get_index($option_key);
            }
        }

        $data = [];

        foreach ($indexes as $index) {
            foreach ($array as $option_key => $option_value) {
                foreach ($option_value as $key_str => $value) {
                    if (strpos($key_str, 'options_bases_' . $index . '_base_entity_base_id') === 0) {
                        $data[$index] = unserialize($value)[0];
                    }
                }
            }
        }

        $grouped_by_id = [];

        foreach ($data as $key_index => $id_product) {
            foreach ($array as $item_key => $item_value) {
                foreach ($item_value as $key_option => $value_option) {
                    if (strpos($key_option, 'options_bases_' . $key_index) === 0) {
                        $grouped_by_id['sets_by_id'][$id_product] = $item_value;
                    }
                }
            }
        }

        return $grouped_by_id;
    }

    public function set_group_by_id_category($array) {
        $indexes = [];

        foreach ($array as $item_key => $item_value) {
            foreach ($item_value as $option_key => $option_value) {
                $indexes[] = self::get_index($option_key);
            }
        }

        $data = [];

        foreach ($indexes as $index) {
            foreach ($array as $option_key => $option_value) {
                foreach ($option_value as $key_str => $value) {
                    if (strpos($key_str, 'options_bases_' . $index . '_base_entity_base_category') === 0) {
                        $data[$index] = $value;
                    }
                }
            }
        }

        $grouped_by_id_category = [];

        foreach ($data as $key_index => $id_category) {
            foreach ($array as $item_key => $item_value) {
                foreach ($item_value as $key_option => $value_option) {
                    if (strpos($key_option, 'options_bases_' . $key_index) === 0) {
                        $grouped_by_id_category['sets_by_id_category'][$id_category] = $item_value;
                    }
                }
            }
        }

        return $grouped_by_id_category;
    }

    public function set_group_by_id_collection($array) {
        $indexes = [];

        foreach ($array as $item_key => $item_value) {
            foreach ($item_value as $option_key => $option_value) {
                $indexes[] = self::get_index($option_key);
            }
        }

        $data = [];

        foreach ($indexes as $index) {
            foreach ($array as $option_key => $option_value) {
                foreach ($option_value as $key_str => $value) {
                    if (strpos($key_str, 'options_bases_' . $index . '_base_entity_base_collection') === 0) {
                        $data[$index] = $value;
                    }
                }
            }
        }

        $grouped_by_id_collection = [];

        foreach ($data as $key_index => $id_collection) {
            foreach ($array as $item_key => $item_value) {
                foreach ($item_value as $key_option => $value_option) {
                    if (strpos($key_option, 'options_bases_' . $key_index) === 0) {
                        $grouped_by_id_collection['sets_by_id_collection'][$id_collection] = $item_value;
                    }
                }
            }
        }

        return $grouped_by_id_collection;
    }

    public function set_group_by_catcol($array) {
        $indexes = [];

        foreach ($array as $item_key => $item_value) {
            foreach ($item_value as $option_key => $option_value) {
                $indexes[] = self::get_index($option_key);
            }
        }

        $data = [];

        foreach ($indexes as $index) {
            foreach ($array as $option_key => $option_value) {

                foreach ($option_value as $key_str => $value) {

                    if (strpos($key_str, 'options_bases_' . $index . '_base_entity_base_category') === 0) {
                        $data[$index][] = $value;

                    }

                    if (strpos($key_str, 'options_bases_' . $index . '_base_entity_base_collection') === 0) {
                        $data[$index][] = $value;
                    }

                }

            }
        }

        $data_out = self::unique_array($data);

        $grouped_by_catcol = [];

        foreach ($data_out as $key_index => $item_cat_col) {
            $unique_string = implode('_', $item_cat_col);
            foreach ($array as $item_key => $item_value) {
                foreach ($item_value as $key_option => $value_option) {
                    if (strpos($key_option, 'options_bases_' . $key_index) === 0) {
                        $grouped_by_catcol['sets_by_catcol'][$unique_string] = $item_value;
                    }
                }
            }
        }

        return $grouped_by_catcol;
    }

    /**
     * @param $str
     * @return mixed|string
     * Получить индекс
     * Индексом является первое число в строке разделенной нижним подчеркиванием
     */
    static function get_index($str) {
        return explode('_', $str)[2];
    }

    /**
     * @param $array
     * @return array
     * Устраняет дубликаты значений в массиве, который сгруппирован по ключу
     */
    static function unique_array($array) {
        $data = [];
        $result = [];

        foreach ($array as $key => $value) {
            foreach ($value as $item) {
                $data[] = $item;
            }
            $data_out = array_unique($data);
            $item_array = array_diff($data_out, array("null", ""));
            foreach ($item_array as $item_i) {
                $result[$key][] = $item_i;
            }
        }

        return $result;
    }
}