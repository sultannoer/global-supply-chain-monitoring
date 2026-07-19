<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    
    protected $primaryKey = 'code';
    
    
    public $incrementing = false;
    
    
    protected $keyType = 'string';

    
    protected $fillable = [
        'code', 
        'name', 
        'region', 
        'currency_code', 
        'language',
        'gdp', 
        'inflation_rate', 
        'population', 
        'export_volume', 
        'import_volume'
    ];

    
    public function ports(): HasMany
    {
        return $this->hasMany(Port::class, 'country_code', 'code');
    }
}