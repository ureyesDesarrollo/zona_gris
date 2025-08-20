<?php

namespace App\Helpers;

class Validator
{
    private $data = [];
    private $errors = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function required($fields)
    {
        foreach ($fields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[$field][] = 'El campo es obligatorio.';
            }
        }
        return $this;
    }

    public function email($fields)
    {
        foreach ($fields as $field) {
            if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = 'El campo debe ser un email válido.';
            }
        }
        return $this;
    }

    public function minLength($field, $length)
    {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field][] = "Debe tener al menos $length caracteres.";
        }
        return $this;
    }

    public function numeric($fields)
    {
        foreach ($fields as $field) {
            if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
                $this->errors[$field][] = 'Debe ser numérico.';
            }
        }
        return $this;
    }

    public function requiredAny(array $campos)
    {
        $present = false;
        foreach ($campos as $campo) {
            if (isset($this->data[$campo])) {
                $present = true;
                break;
            }
        }
        if (!$present) {
            $this->errors[] = 'Al menos uno de los siguientes campos es requerido: ' . implode(', ', $campos);
        }
    }


    // Puedes agregar más métodos (max, regex, custom, etc.)

    public function fails()
    {
        return !empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }
}
