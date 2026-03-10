<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuids
{
    /**
     * Al iniciar el modelo, se genera automáticamente un UUID
     */
    protected static function bootHasUuids()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Indica que el ID no es autoincrementable
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Indica que el tipo de llave primaria es un string (UUID)
     */
    public function getKeyType()
    {
        return 'string';
    }
}