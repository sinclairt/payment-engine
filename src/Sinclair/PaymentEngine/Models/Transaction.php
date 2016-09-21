<?php

namespace Sinclair\PaymentEngine\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * Class Transaction
 *
 * @property $reference
 * @property $currency
 * @property Collection $items
 * @package Sinclair\PaymentEngine\Models
 */
class Transaction extends Model implements \Sinclair\PaymentEngine\Contracts\Transaction
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'plan_id',
        'reference',
        'is_success',
        'is_failure',
        'gateway_response',
        'card_number',
        'card_starts_at',
        'card_expires_at',
        'card_cvv',
        'card_type',
        'card_issue_number',
        'currency',
    ];

    /**
     * @var array
     */
    protected $with = [ 'items' ];

    /**
     * @var array
     */
    protected $dates = [
        'card_starts_at',
        'card_expires_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'plan_id'           => 'integer',
        'reference'         => 'string',
        'card_number'       => 'string',
        'is_success'        => 'boolean',
        'is_failure'        => 'boolean',
        'gateway_response'  => 'array',
        'card_cvv'          => 'string',
        'card_type'         => 'string',
        'card_issue_number' => 'string',
        'currency'          => 'string',
    ];

    /**
     * @var array
     */
    public $filters = [
        'plan',
        'card_type',
        'currency'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * @return mixed
     */
    public function total()
    {
        return array_sum($this->items->pluck('amount')
                                     ->toArray());
    }

    public function process()
    {
        return app('PaymentEngine')->processTransaction($this);
    }

    /**
     * @param Builder $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterPlan( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('plan_id', $value) : $query->where('plan_id', $value);
    }

    /**
     * @param Builder $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterCardType( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('card_type', $value) : $query->where('card_type', $value);
    }

    /**
     * @param Builder $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterCurrency( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('currency', $value) : $query->where('currency', $value);
    }
}