<?php

namespace Sinclair\PaymentEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Wtbi\Schedulable\Traits\IsSchedulable;

/**
 * Class Charge
 * @package Sinclair\PaymentEngine\Models
 */
class Charge extends Model implements \Sinclair\PaymentEngine\Contracts\Charge, \Wtbi\Schedulable\Contracts\IsSchedulable
{
    use IsSchedulable, SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'plan_id',
        'amount',
        'description',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'plan_id'     => 'integer',
        'amount'      => 'decimal',
        'description' => 'string'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    /**
     * @var array
     */
    public $filters = [
        'plan'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
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
}