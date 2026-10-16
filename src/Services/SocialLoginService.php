<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Exception\SocialLoginException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\SocialLogin\Repositories\CustomerSocialAccountRepository;

class SocialLoginService
{
    public function __construct(
        protected CustomerSocialAccountRepository $socialAccounts,
        protected CustomerRepository $customers,
        protected CustomerGroupRepository $customerGroups,
    ) {}

    /**
     * @param  array{id: string, email: ?string, name: ?string, email_verified: bool}  $profile
     * @return array{customer: Customer, isNew: bool}
     *
     * @throws SocialLoginException
     */
    public function resolve(array $profile, string $provider): array
    {
        if ($account = $this->socialAccounts->findOneWhere([
            'provider_name' => $provider,
            'provider_id' => $profile['id'],
        ])) {
            return ['customer' => $account->customer, 'isNew' => false];
        }

        if (empty($profile['email'])) {
            throw new SocialLoginException(
                'SOCIAL_EMAIL_REQUIRED',
                trans('bagistoapi::app.graphql.social-login.email-required'),
            );
        }

        $customer = $this->customers->findOneWhere([
            'email' => $profile['email'],
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        $isNew = ! $customer;

        if ($isNew) {
            $customer = $this->register($profile);
        }

        $this->socialAccounts->create([
            'customer_id' => $customer->id,
            'provider_id' => $profile['id'],
            'provider_name' => $provider,
        ]);

        return ['customer' => $customer, 'isNew' => $isNew];
    }

    protected function register(array $profile): Customer
    {
        [$firstName, $lastName] = $this->splitName((string) $profile['name'], (string) $profile['email']);

        $verified = $profile['email_verified'] || ! core()->getConfigData('customer.settings.email.verification');

        return $this->customers->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $profile['email'],
            'password' => null,
            'status' => 1,
            'is_verified' => $verified ? 1 : 0,
            'is_suspended' => 0,
            'subscribed_to_news_letter' => 0,
            'api_token' => Str::random(80),
            'channel_id' => core()->getCurrentChannel()->id,
            'customer_group_id' => $this->customerGroups->findOneWhere(['code' => 'general'])?->id,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitName(string $name, string $email): array
    {
        $name = trim($name);

        if ($name === '') {
            $name = Str::before($email, '@');
        }

        $names = $this->socialAccounts->getFirstLastName($name);

        return [$names['first_name'] ?: $name, $names['last_name']];
    }
}
