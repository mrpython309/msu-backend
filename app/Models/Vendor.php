<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_code',
        'name',
        'phone_number',
        'contact_person',
        'email',
        'gst_number',
        'pan_number',
        'address',
        'city',
        'state',
        'country',
        'status',
        'brands',
        'notes',
        'created_by',
        'updated_by',
    ];

     protected $casts = [
        'brands' => 'array', // JSON column cast to array
    ];

    // Add appended attribute
    protected $appends = ['brands_names'];

    /**
     * Vendor code from first letter of name + 3-digit sequence.
     * Examples: Avi → A001, Anu → A002, John → J001
     */
    public static function generateCompanyCode(string $name): string
    {
        $clean = preg_replace('/[^A-Za-z]/', '', $name) ?? '';
        $letter = strtoupper(substr($clean !== '' ? $clean : 'X', 0, 1));

        return DB::transaction(function () use ($letter) {
            $latest = static::query()
                ->whereNotNull('company_code')
                ->whereRaw('company_code REGEXP ?', ['^'.$letter.'[0-9]+$'])
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING(company_code, 2) AS UNSIGNED) DESC')
                ->value('company_code');

            $next = $latest ? ((int) substr((string) $latest, 1)) + 1 : 1;

            return $letter.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    // This accessor will return the brand names as a string
    public function getBrandsNamesAttribute()
    {
        if (!$this->brands) return '';

        // Assuming $brands is a collection of all brands passed in somehow
        $allBrands = Brand::all(); // Or cache it for performance
        return $allBrands->whereIn('id', $this->brands)->pluck('brand_name')->implode(', ');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

      public function scopeActive($query)
      {
            return $query->where('status', 'active');
      }

    public function quotations()
    {
        return $this->hasMany(VendorQuotation::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function grns()
    {
        return $this->hasMany(Grn::class);
    }

    public function rfqs()
    {
        return $this->belongsToMany(Rfq::class, 'rfq_vendors')
            ->withPivot(['status', 'invited_at'])
            ->withTimestamps();
    }
}
