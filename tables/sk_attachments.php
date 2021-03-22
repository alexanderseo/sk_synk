<?php


class sk_attachments extends bootstrap {
    private static $instance;

    private $log;
    private $attachments;
    private $data_attachments;

    public function __construct() {
        parent::__construct();
        global $wordpress;

        $this->log = [];
        $this->attachments = $wordpress['attachments_with_parent'];
        $this->data_attachments = [];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->attachments as $key => $value) {
            $this->data_attachments[$key]['id'] = (int)$key;
            $this->data_attachments[$key]['parent_id'] = isset($value['post_parent']) ? $value['post_parent'] : "";
            $this->data_attachments[$key]['url'] = isset($value['original']) ? $value['original'] : "";
        }

        return $this->data_attachments;
    }
}