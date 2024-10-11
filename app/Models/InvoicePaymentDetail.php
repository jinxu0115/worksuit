<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompany;

class InvoicePaymentDetail extends BaseModel
{
    use HasCompany;

    protected $table = 'invoice_payment_details';
    protected $id = 'id';
    protected $fillable = ['title', 'company_id', 'payment_details'];


    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
