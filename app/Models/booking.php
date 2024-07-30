<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking extends Model
{
     use HasFactory;
    protected $table = 'booking';
    public $timestamps = true;
    protected $fillable = [
        'cust_name','modeoftrans','forwordingno','refrenceno','deliverylocation','pickuplocation','status','product_type','content','weight','vol_weight','charg_weight','client_name',
        'pickupaddress','pickup_name','pickupcity','pickup_pincode','sendercontactno','con_client_name','receiveraddress','receiverstate','receivercity','receiver_pincode',
        'receivercontactno','booking_date',
    ];
}
