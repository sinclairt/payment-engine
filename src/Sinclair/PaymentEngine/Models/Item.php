<?php

namespace Sinclair\PaymentEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Item
 * @package Sinclair\PaymentEngine\Models
 */
class Item extends Model implements \Sinclair\PaymentEngine\Contracts\Item
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'amount',
        'description',
        'charged_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'transaction_id' => 'integer',
        'amount'         => 'float',
        'description'    => 'string'
    ];

    /**
     * @var string
     */
    protected $table = 'transaction_items';

    /**
     * @var array
     */
    protected $dates = [ 'deleted_at', 'charged_at' ];

    /**
     * @var array
     */
    public $filters = [
        'transaction'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * @param $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterTransaction( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('transaction_id', $value) : $query->where('transaction_id', $value);
    }
}