<?php

namespace Webkul\BagistoApi\Repositories;

use Webkul\BagistoApi\Models\GuestCartTokens;
use Webkul\Core\Eloquent\Repository;

class GuestCartTokensRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return GuestCartTokens::class;
    }

    /**
     * Find token by value
     */
    public function findByToken(string $token)
    {
        return $this->findOneByField('token', $token);
    }

    /**
     * Find cart by token
     */
    public function findCartByToken(string $token)
    {
        $cartToken = $this->findByToken($token);

        if ($cartToken && $cartToken->cart) {
            return $cartToken->cart;
        }

        return null;
    }

    /**
     * Create a new guest cart token
     */
    public function createToken(int $cartId): GuestCartTokens
    {
        return $this->create([
            'cart_id' => $cartId,
            'token'   => (string) \Illuminate\Support\Str::uuid(),
        ]);
    }
}
