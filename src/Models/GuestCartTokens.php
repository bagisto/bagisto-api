<?php

namespace Webkul\BagistoApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Contracts\GuestCartTokens as GuestCartTokensContract;
use Webkul\Checkout\Models\Cart;

class GuestCartTokens extends Model implements GuestCartTokensContract
{
    use HasFactory;

    protected $table = 'guest_cart_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'cart_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // Add your attribute casts here
    ];

    /**
     * Get the cart associated with this token
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }
}
