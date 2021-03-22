<?php
class sk_showrooms extends bootstrap {
    private static $instance;

    private $log;
    private $ids_showrooms;
    private $posts;
    private $postmeta;
    private $terms;
    private $termmeta;
    private $attachments;
    private $data;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = array();
        $this->ids_showrooms = $wordpress['posts_ids']['showrooms'];
        $this->posts = $wordpress['posts'];
        $this->postmeta = $wordpress['postmeta'];
        $this->terms = $wordpress['terms'];
        $this->termmeta = $wordpress['termmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->data = array();
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->ids_showrooms as $id) {
            $posts = $this->posts[$id];
            $postmeta = $this->postmeta[$id];

            $this->set_id($id);
            $this->set_slug($id, $posts);
            $this->set_name($id, $posts);
            $this->set_group($id, $postmeta, $this->terms, $this->termmeta);
            $this->set_cover($id, $postmeta, $this->attachments);
            $this->set_address($id, $postmeta);
            $this->set_coordinates($id, $postmeta);
            $this->set_working_hours($id, $postmeta);
            $this->set_description($id, $postmeta);
            $this->set_city($id, $postmeta, $this->terms);
            $this->set_subway($id, $postmeta);
            $this->set_area($id, $postmeta);
            $this->set_services($id, $postmeta);
            $this->set_buttons($id, $postmeta, $this->attachments);
            $this->set_order($id, $postmeta);
            $this->set_hidden($id, $postmeta);
            $this->set_contact_form($id, $postmeta);
            $this->set_blocking($id, $postmeta);
        }

        $this->set_log($this->log);

        return $this->data;
    }

    private function set_id($id) {
        $this->data[$id]['id'] = (int) $id;
    }

    private function set_slug($id, $post) {
        if (isset($post['post_name'])) {
            if (empty($post['post_name'])) {
                $this->data[$id]['slug'] = 0;

                $this->log[] = array('Товар', $id, 'post_name', 'EMPTY');
            } else {
                $this->data[$id]['slug'] = $post['post_name'];
            }
        } else {
            $this->data[$id]['slug'] = 0;

            $this->log[] = array('Товар', $id, 'post_name', 'NOT_EXIST');
        }
    }

    private function set_name($id, $post) {
        if (isset($post['post_title'])) {
            if (empty($post['post_title'])) {
                $this->data[$id]['name'] = 0;

                $this->log[] = array('Товар', $id, 'post_title', 'EMPTY');
            } else {
                $this->data[$id]['name'] = $post['post_title'];
            }
        } else {
            $this->data[$id]['name'] = 0;

            $this->log[] = array('Товар', $id, 'post_title', 'NOT_EXIST');
        }
    }

    private function set_group($id, $postmeta, $terms, $termmeta) {
        if (isset($postmeta['showroom-group'])) {
            if (empty($postmeta['showroom-group'])) {
                $this->data[$id]['group'] = 0;

                $this->log[] = array('Группа (шоурумы)', $id, 'showroom-group', 'EMPTY');
            } else {
                $data = array();

                foreach (unserialize($postmeta['showroom-group']) as $value) {
                    $terms = $terms[$value];
                    $termmeta = $termmeta[$value];

                    $data[$value]['id'] = $value;

                    if (isset($terms['name'])) {
                        if (empty($terms['name'])) {
                            $data[$value]['name'] = 0;

                            $this->log[] = array('Группа (шоурумы)', $id, 'name', 'EMPTY');
                        } else {
                            $data[$value]['name'] = $terms['name'];
                        }
                    } else {
                        $data[$value]['name'] = 0;

                        $this->log[] = array('Группа (шоурумы)', $id, 'name', 'NOT_EXIST');
                    }

                    if (isset($termmeta['showroom-list-subtitle'])) {
                        if (empty($termmeta['showroom-list-subtitle'])) {
                            $data[$value]['subtitle'] = 0;

                            $this->log[] = array('Группа (шоурумы)', $id, 'showroom-list-subtitle', 'EMPTY');
                        } else {
                            $data[$value]['subtitle'] = $termmeta['showroom-list-subtitle'];
                        }
                    } else {
                        $data[$value]['subtitle'] = 0;

                        $this->log[] = array('Группа (шоурумы)', $id, 'showroom-list-subtitle', 'NOT_EXIST');
                    }

                    if (isset($termmeta['showroom-list-order'])) {
                        if (empty($termmeta['showroom-list-order'])) {
                            $data[$value]['order'] = 0;

                            $this->log[] = array('Группа (шоурумы)', $id, 'showroom-list-order', 'EMPTY');
                        } else {
                            $data[$value]['order'] = $termmeta['showroom-list-order'];
                        }
                    } else {
                        $data[$value]['order'] = 0;

                        $this->log[] = array('Группа (шоурумы)', $id, 'showroom-list-order', 'NOT_EXIST');
                    }

                    if (isset($termmeta['showroom-list-icon'])) {
                        if (empty($termmeta['showroom-list-icon'])) {
                            $data[$value]['icon'] = 0;

                            $this->log[] = array('Группа (шоурумы)', $id, 'showroom-list-icon', 'EMPTY');
                        } else {
                            $data[$value]['icon'] = $termmeta['showroom-list-icon'];
                        }
                    } else {
                        $data[$value]['icon'] = 0;

                        $this->log[] = array('Группа (шоурумы)', $id, 'showroom-list-icon', 'NOT_EXIST');
                    }
                }

                $this->data[$id]['group'] = serialize($data);
            }
        } else {
            $this->data[$id]['group'] = 0;

            $this->log[] = array('Группа (шоурумы)', $id, 'showroom-group', 'NOT_EXIST');
        }
    }

    private function set_cover($id, $postmeta, $attachments) {
        if (isset($postmeta['showroom-cover'])) {
            if (empty($postmeta['showroom-cover'])) {
                $this->data[$id]['cover'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-cover', 'EMPTY');
            } else {
                $this->data[$id]['cover'] = serialize($attachments[$postmeta['showroom-cover']]);
            }
        } else {
            $this->data[$id]['cover'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-cover', 'NOT_EXIST');
        }
    }

    private function set_address($id, $postmeta) {
        if (isset($postmeta['showroom-address'])) {
            if (empty($postmeta['showroom-address'])) {
                $this->data[$id]['address'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-address', 'EMPTY');
            } else {
                $this->data[$id]['address'] = $postmeta['showroom-address'];
            }
        } else {
            $this->data[$id]['address'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-address', 'NOT_EXIST');
        }
    }

    private function set_coordinates($id, $postmeta) {
        if (isset($postmeta['showroom-coordinates'])) {
            if (empty($postmeta['showroom-coordinates'])) {
                $this->data[$id]['coordinates'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-coordinates', 'EMPTY');
            } else {
                $this->data[$id]['coordinates'] = $postmeta['showroom-coordinates'];
            }
        } else {
            $this->data[$id]['coordinates'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-coordinates', 'NOT_EXIST');
        }
    }

    private function set_working_hours($id, $postmeta) {
        if (isset($postmeta['showroom-working-hours'])) {
            if (empty($postmeta['showroom-working-hours'])) {
                $this->data[$id]['working_hours'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-working-hours', 'EMPTY');
            } else {
                $this->data[$id]['working_hours'] = $postmeta['showroom-working-hours'];
            }
        } else {
            $this->data[$id]['working_hours'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-working-hours', 'NOT_EXIST');
        }
    }

    private function set_description($id, $postmeta) {
        if (isset($postmeta['showroom-description'])) {
            if (empty($postmeta['showroom-description'])) {
                $this->data[$id]['description'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-description', 'EMPTY');
            } else {
                $this->data[$id]['description'] = $postmeta['showroom-description'];
            }
        } else {
            $this->data[$id]['description'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-description', 'NOT_EXIST');
        }
    }

    private function set_city($id, $postmeta, $terms) {
        $data = array();

        if (isset($postmeta['showroom-city'])) {
            if (empty($postmeta['showroom-city'])) {
                $this->data[$id]['city'] = 0;

                $this->log[] = array('Шоурумы', $id, 'showroom-city', 'EMPTY');
            } else {
                if (isset($terms[$postmeta['showroom-city']]['name'])) {
                    if (empty($terms[$postmeta['showroom-city']]['name'])) {
                        $data['name'] = 0;

                        $this->log[] = array('Город (шоурумы)', $id, 'name', 'EMPTY');
                    } else {
                        $data['name'] = $terms[$postmeta['showroom-city']]['name'];
                    }
                } else {
                    $this->log[] = array('Город (шоурумы)', $id, 'name', 'NOT_EXIST');
                }

                if (isset($terms[$postmeta['showroom-city']]['slug'])) {
                    if (empty($terms[$postmeta['showroom-city']]['slug'])) {
                        $data['slug'] = 0;

                        $this->log[] = array('Город (шоурумы)', $id, 'slug', 'EMPTY');
                    } else {
                        $data['slug'] = $terms[$postmeta['showroom-city']]['slug'];
                    }
                } else {
                    $this->log[] = array('Город (шоурумы)', $id, 'name', 'NOT_EXIST');
                }

                $this->data[$id]['city'] = serialize($data);
            }
        } else {
            $this->data[$id]['city'] = 0;

            $this->log[] = array('Шоурумы', $id, 'showroom-city', 'NOT_EXIST');
        }
    }

    private function set_subway($id, $postmeta) {
        $data = array();

        if (isset($postmeta['showroom-subway_showroom-subway-station'])) {
            if (empty($postmeta['showroom-subway_showroom-subway-station'])) {
                $data['station'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-subway_showroom-subway-station', 'EMPTY');
            } else {
                $data['station'] = $postmeta['showroom-subway_showroom-subway-station'];
            }
        } else {
            $data['station'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-subway_showroom-subway-station', 'NOT_EXIST');
        }

        if (isset($postmeta['showroom-subway_showroom-subway-color'])) {
            if (empty($postmeta['showroom-subway_showroom-subway-color'])) {
                $data['color'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-subway_showroom-subway-color', 'EMPTY');
            } else {
                $data['color'] = $postmeta['showroom-subway_showroom-subway-color'];
            }
        } else {
            $data['color'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-subway_showroom-subway-color', 'NOT_EXIST');
        }

        if (isset($postmeta['showroom-subway_showroom-subway-distance'])) {
            if (empty($postmeta['showroom-subway_showroom-subway-distance'])) {
                $data['distance'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-subway_showroom-subway-distance', 'EMPTY');
            } else {
                $data['distance'] = $postmeta['showroom-subway_showroom-subway-distance'];
            }
        } else {
            $data['distance'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-subway_showroom-subway-distance', 'NOT_EXIST');
        }

        $this->data[$id]['subway'] = serialize($data);
    }

    private function set_area($id, $postmeta) {
        if (isset($postmeta['showroom-subgroup-1_showroom-area'])) {
            if (empty($postmeta['showroom-subgroup-1_showroom-area'])) {
                $this->data[$id]['area'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-subgroup-1_showroom-area', 'EMPTY');
            } else {
                $this->data[$id]['area'] = $postmeta['showroom-subgroup-1_showroom-area'];
            }
        } else {
            $this->data[$id]['area'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-subgroup-1_showroom-area', 'NOT_EXIST');
        }
    }

    private function set_services($id, $postmeta) {
        if (isset($postmeta['showroom-subgroup-1_showroom-services'])) {
            if (empty($postmeta['showroom-subgroup-1_showroom-services'])) {
                $this->data[$id]['services'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-subgroup-1_showroom-services', 'EMPTY');
            } else {
                $this->data[$id]['services'] = $postmeta['showroom-subgroup-1_showroom-services'];
            }
        } else {
            $this->data[$id]['services'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-subgroup-1_showroom-services', 'NOT_EXIST');
        }
    }

    private function set_buttons($id, $postmeta, $attachments) {
        $data = array();

        $keys = preg_grep('/^showroom-waypoints_/', array_keys($postmeta));

        if (!empty($keys)) {
            foreach ($keys as $key) {
                if (strpos($key, 'title') !== false) {
                    $exploded_key = explode('_', $key);

                    $data[$exploded_key[1]]['title'] = $postmeta[$key];
                }

                if (strpos($key, 'image') !== false) {
                    $exploded_key = explode('_', $key);

                    $data[$exploded_key[1]]['image'] = serialize($attachments[$postmeta[$key]]);
                }
            }
        } else {
            $this->log[] = array('Шоурум', $id, 'showroom-waypoints', 'EMPTY');
        }

        if (!empty($data)) {
            $this->data[$id]['buttons'] = serialize($data);
        } else {
            $this->data[$id]['buttons'] = 0;
        }
    }

    private function set_order($id, $postmeta) {
        if (isset($postmeta['showroom-order'])) {
            if (empty($postmeta['showroom-order'])) {
                $this->data[$id]['order'] = 0;

                $this->log[] = array('Шоурум', $id, 'showroom-order', 'EMPTY');
            } else {
                $this->data[$id]['order'] = $postmeta['showroom-order'];
            }
        } else {
            $this->data[$id]['order'] = 0;

            $this->log[] = array('Шоурум', $id, 'showroom-order', 'NOT_EXIST');
        }
    }

    private function set_hidden($id, $postmeta) {
        if (isset($postmeta['showroom-hidden'])) {
            if (empty($postmeta['showroom-hidden'])) {
                $this->data[$id]['hidden'] = 0;
            } else {
                $this->data[$id]['hidden'] = $postmeta['showroom-hidden'];
            }
        } else {
            $this->data[$id]['hidden'] = 0;
        }
    }

    private function set_contact_form($id, $postmeta) {
        if (isset($postmeta['showroom-contact-form'])) {
            if (empty($postmeta['showroom-contact-form'])) {
                $this->data[$id]['contact_form'] = 0;
            } else {
                $this->data[$id]['contact_form'] = $postmeta['showroom-contact-form'];
            }
        } else {
            $this->data[$id]['contact_form'] = 0;
        }
    }

    private function set_blocking($id, $postmeta) {
        $data = array();

        if (isset($postmeta['showroom-state_showroom-disabled'])) {
            if (empty($postmeta['showroom-state_showroom-disabled'])) {
                $data['disabled'] = 0;
            } else {
                $data['disabled'] = $postmeta['showroom-state_showroom-disabled'];
            }
        } else {
            $data['disabled'] = 0;
        }

        if (isset($postmeta['showroom-state_showroom-disabled']) && $postmeta['showroom-state_showroom-disabled'] == '1') {
            if (isset($postmeta['showroom-state_showroom-disabled-reason'])) {
                if (empty($postmeta['showroom-state_showroom-disabled-reason'])) {
                    $data['reason'] = 0;
                } else {
                    $data['reason'] = $postmeta['showroom-state_showroom-disabled-reason'];
                }
            } else {
                $data['reason'] = 0;
            }
        } else {
            $data['reason'] = 0;
        }

        $this->data[$id]['blocking'] = serialize($data);
    }
}