<?php

/**
 * TransactionCurrency.php
 * Copyright (c) 2019 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Models;

use Eloquent;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FireflyIII\Models\TransactionCurrency
 *
 * @property int                                                $id
 * @property Carbon|null                                        $created_at
 * @property Carbon|null                                        $updated_at
 * @property Carbon|null                                        $deleted_at
 * @property bool                                               $enabled
 * @property bool                                               $userDefault
 * @property bool                                               $userEnabled
 * @property string                                             $code
 * @property string                                             $name
 * @property string                                             $symbol
 * @property int                                                $decimal_places
 * @property-read Collection|BudgetLimit[]                      $budgetLimits
 * @property-read int|null                                      $budget_limits_count
 * @property-read Collection|TransactionJournal[]               $transactionJournals
 * @property-read int|null                                      $transaction_journals_count
 * @property-read Collection|Transaction[]                      $transactions
 * @property-read int|null                                      $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency newQuery()
 * @method static Builder|TransactionCurrency onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency query()
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereDecimalPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionCurrency whereUpdatedAt($value)
 * @method static Builder|TransactionCurrency withTrashed()
 * @method static Builder|TransactionCurrency withoutTrashed()
 * @property-read Collection<int, UserGroup> $userGroups
 * @property-read int|null                   $user_groups_count
 * @property-read Collection<int, User>      $users
 * @property-read int|null                   $users_count
 * @mixin Eloquent
 */
class TransactionCurrency extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts
        = [
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
            'deleted_at'     => 'datetime',
            'decimal_places' => 'int',
            'enabled'        => 'bool',
        ];
    /** @var array Fields that can be filled */
    protected $fillable = ['name', 'code', 'symbol', 'decimal_places', 'enabled'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @param string $value
     *
     * @return TransactionCurrency
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): TransactionCurrency
    {
        if (auth()->check()) {
            $currencyId = (int)$value;
            $currency   = self::find($currencyId);
            if (null !== $currency) {
                $currency->refreshForUser(auth()->user());
                return $currency;
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * @return HasMany
     */
    public function budgetLimits(): HasMany
    {
        return $this->hasMany(BudgetLimit::class);
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function refreshForUser(User $user)
    {
        $current           = $user->userGroup->currencies()->where('transaction_currencies.id', $this->id)->first();
        $default           = app('amount')->getDefaultCurrencyByUserGroup($user->userGroup);
        $this->userDefault = (int)$default->id === (int)$this->id;
        $this->userEnabled = null !== $current;
    }

    /**
     * @return HasMany
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Link to user groups
     *
     * @return BelongsToMany
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class)->withTimestamps()->withPivot('group_default');
    }

    /**
     * Link to users
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('user_default');
    }
}
