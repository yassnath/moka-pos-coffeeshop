<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'last_number' => 'integer',
        ];
    }
}
