<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class DealerStockRequest extends Model
{
    protected $fillable = ['dealer_id','product_id','quantity','notes','status','reviewed_by','reviewed_at','review_notes','approved_order_id'];
    protected $dates = ['reviewed_at'];
    public function dealer(){ return $this->belongsTo(User::class,'dealer_id'); }
    public function product(){ return $this->belongsTo(Product::class); }
    public function reviewer(){ return $this->belongsTo(User::class, 'reviewed_by'); }
}
