<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuids;

class Banco extends Model
{
    use HasUuids;

    protected $fillable = ['nombre', 'tipo'];

    public function cuentas()
    {
        return $this->hasMany(CuentaFinanciera::class);
    }
}