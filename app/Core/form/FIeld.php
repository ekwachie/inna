<?php

namespace app\core\form;
use app\core\Model;

class Field
{
    public $model;
    public string $attribute;
    public function __construct($model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    public function __toString()
    {
        return '
        <div class="col-sm">
            <label for="first-name" class="required">First name</label>
            <input type="text"
            class="form-control"
            name="fname"
            placeholder="First name"
            required="required"
            value="" />
         </div>';
    }
}
