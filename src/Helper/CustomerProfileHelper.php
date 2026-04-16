<?php

namespace Webkul\BagistoApi\Helper;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Dto\CustomerProfileOutput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\CustomerProfile as CustomerProfileModel;
use Webkul\Customer\Models\Customer;

/**
 * Maps Customer model to CustomerProfile API resource
 */
class CustomerProfileHelper
{
    public static function mapCustomerToProfile(Customer $customer): CustomerProfileModel
    {
        $imageUrl = null;
        if ($customer->image) {
            $imageUrl = Storage::url($customer->image);
        }

        $profile = new CustomerProfileModel;
        $profile->id = (string) $customer->id;
        $profile->first_name = (string) $customer->first_name;
        $profile->last_name = (string) $customer->last_name;
        $profile->email = (string) $customer->email;
        $profile->phone = (string) $customer->phone;
        $profile->gender = (string) $customer->gender;
        $profile->date_of_birth = (string) $customer->date_of_birth;
        $profile->status = (string) $customer->status;
        $profile->subscribed_to_news_letter = (bool) $customer->subscribed_to_news_letter;
        $profile->is_verified = (string) $customer->is_verified;
        $profile->is_suspended = (string) $customer->is_suspended;
        $profile->image = $imageUrl;

        return $profile;
    }

    /**
     * Map customer model to CustomerProfileOutput DTO
     */
    public static function mapCustomerToProfileOutput(Customer $customer): CustomerProfileOutput
    {
        $imageUrl = null;
        if ($customer->image) {
            $imageUrl = Storage::url($customer->image);
        }

        return new CustomerProfileOutput(
            id: (string) $customer->id,
            _id: (string) $customer->id,
            firstName: $customer->first_name,
            lastName: $customer->last_name,
            email: $customer->email,
            phone: $customer->phone,
            gender: $customer->gender,
            dateOfBirth: $customer->date_of_birth,
            status: (string) $customer->status,
            subscribedToNewsLetter: (bool) $customer->subscribed_to_news_letter,
            isVerified: $customer->is_verified ? 'true' : 'false',
            isSuspended: $customer->is_suspended ? 'true' : 'false',
            image: $imageUrl,
        );
    }

    public static function handleImageUpload(string $imageData, Customer $customer): void
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $imageFormat = $matches[1];
                $base64Data = substr($imageData, strpos($imageData, ',') + 1);
                $decodedData = base64_decode($base64Data, true);

                if ($decodedData === false) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-base64'));
                }

                if (strlen($decodedData) > 5 * 1024 * 1024) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.size-exceeds-limit'));
                }

                $directory = 'customer/'.$customer->id;

                if ($customer->image) {
                    Storage::delete($customer->image);
                }

                $filename = $directory.'/'.uniqid().'.'.$imageFormat;

                Storage::put($filename, $decodedData);

                $customer->image = $filename;
                $customer->save();

                Event::dispatch('customer.image.upload.after', $customer);
            } else {
                throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-format'));
            }
        } catch (\Exception $e) {
            throw new InvalidInputException($e->getMessage(), 0, $e);
        }
    }
}
