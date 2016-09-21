<?php

namespace Sinclair\PaymentEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Wtbi\Schedulable\Traits\IsSchedulable;

/**
 * Class Plan
 * @package Sinclair\PaymentEngine\Models
 */
class Plan extends Model implements \Sinclair\PaymentEngine\Contracts\Plan, \Wtbi\Schedulable\Contracts\IsSchedulable
{
    use IsSchedulable, SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'plannable_type',
        'plannable_id',
        'card_number',
        'card_starts_at',
        'card_expires_at',
        'card_cvv',
        'card_type',
        'card_issue_number',
        'currency',
        'last_failed_at'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'card_starts_at',
        'card_expires_at',
        'last_failed_at',
        'deleted_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'plannable_type'    => 'string',
        'plannable_id'      => 'integer',
        'card_number'       => 'string',
        'card_cvv'          => 'string',
        'card_type'         => 'string',
        'card_issue_number' => 'string',
        'currency'          => 'string',
    ];

    /**
     * @var array
     */
    public $filters = [
        'plannable_id',
        'plannable_type',
        'frequency',
        'currency',
        'card_type'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function plannable()
    {
        return $this->morphTo();
    }

    public function generateTransaction( $calculate = true, $process = true )
    {
        return app('PaymentEngine')->handleTransaction($this, $calculate, $process);
    }

    /**
     * @param Builder $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterPlannableId( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('plannable_id', $value) : $query->where('plannable_id', $value);
    }

    /**
     * @param Builder $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterPlannableType( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('plannable_type', $value) : $query->where('plannable_type', $value);
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

    /**
     * @param Builder $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterFrequency( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        if ( !is_array($value) )
            $value = [ $value ];

        foreach ( $value as $item )
            if ( method_exists($this, 'scopeIs' . studly_case($item)) )
                $query = $query->{'is' . studly_case($item)}();

        return $query;
    }
}