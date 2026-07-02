<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'peoples',
        'table_id',
        'name',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('qty', 'note', 'id', 'printed', 'scope');
    }
}
