<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class PaymentValue extends Model
{
    use HasFactory, AsSource;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address_id',
        'electricity_day',
        'electricity_night',
        'gas',
        'gas_delivery',
        'water',
        'heating',
        'count_date',
    ];


    public function address()
    {
        return $this->belongsTo(PaymentAddress::class);
    }
}
