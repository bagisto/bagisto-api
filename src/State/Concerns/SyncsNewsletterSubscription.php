<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Facades\Event;
use Webkul\Customer\Contracts\Customer;

/**
 * Keeps the subscribers_list row in step with the customer, as the storefront
 * RegistrationController and CustomerController do. Needs $this->subscriptionRepository.
 */
trait SyncsNewsletterSubscription
{
    /**
     * Link an earlier guest subscription to the new customer, or subscribe them when they asked to.
     */
    protected function linkSubscriptionOnRegister(Customer $customer, bool $wantsNewsletter): void
    {
        $subscription = $this->subscriptionRepository->findOneWhere(['email' => $customer->email]);

        if ($subscription) {
            $this->subscriptionRepository->update(['customer_id' => $customer->id], $subscription->id);

            return;
        }

        if (! $wantsNewsletter) {
            return;
        }

        Event::dispatch('customer.subscription.before');

        $subscription = $this->subscriptionRepository->create([
            'email' => $customer->email,
            'customer_id' => $customer->id,
            'channel_id' => core()->getCurrentChannel()->id,
            'is_subscribed' => 1,
            'token' => uniqid(),
        ]);

        Event::dispatch('customer.subscription.after', $subscription);
    }

    /**
     * Subscribe or unsubscribe the customer's email after a profile update.
     */
    protected function syncSubscriptionOnProfileUpdate(Customer $customer, bool $subscribed): void
    {
        $subscription = $this->subscriptionRepository->findOneWhere(['email' => $customer->email]);

        if ($subscription) {
            $this->subscriptionRepository->update([
                'customer_id' => $customer->id,
                'is_subscribed' => (int) $subscribed,
            ], $subscription->id);

            return;
        }

        if ($subscribed) {
            $this->subscriptionRepository->create([
                'email' => $customer->email,
                'customer_id' => $customer->id,
                'channel_id' => core()->getCurrentChannel()->id,
                'is_subscribed' => 1,
                'token' => uniqid(),
            ]);
        }
    }
}
