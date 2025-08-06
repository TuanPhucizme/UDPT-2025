<?php

abstract class Model {
    protected $attributes = [];
    protected $errors = [];

    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setAttributes($data) {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function getErrors() {
        return $this->errors;
    }

    abstract public function validate();
}