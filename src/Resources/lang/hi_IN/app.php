<?php

return [
    'graphql' => [
        'cart' => [
            'authentication-required'           => 'Authentication token is required',
            'invalid-token'                     => 'Invalid or expired authentication token',
            'unauthorized-access'               => 'Unauthorized access to cart',
            'authenticated-only'                => 'Only authenticated users can fetch their carts',
            'merge-requires-auth'               => 'Guest merge requires authentication',
            'unknown-operation'                 => 'Unknown cart operation',

            'cart-not-found'                    => 'Cart not found',
            'guest-cart-not-found'              => 'Guest cart not found',
            'product-not-found'                 => 'Product not found',

            'product-id-quantity-required'      => 'Product ID and quantity are required',
            'cart-item-id-quantity-required'    => 'Cart item ID and quantity are required',
            'cart-item-id-required'             => 'Cart item ID is required',
            'item-ids-required'                 => 'Item IDs array is required',
            'coupon-code-required'              => 'Coupon code is required',
            'address-data-required'             => 'Country, state, and postcode are required',

            'add-product-failed'                => 'Failed to add product to cart',
            'update-item-failed'                => 'Failed to update cart item',
            'remove-item-failed'                => 'Failed to remove cart item',
            'apply-coupon-failed'               => 'Failed to apply coupon',
            'remove-coupon-failed'              => 'Failed to remove coupon',
            'move-to-wishlist-failed'           => 'Failed to move item to wishlist',
            'estimate-shipping-failed'          => 'Failed to estimate shipping',

            'product-added-successfully'         => 'Product added to cart successfully',
            'guest-cart-merged'                  => 'Guest cart merged successfully',
            'using-authenticated-cart'           => 'Using authenticated customer cart',
            'cart-item-not-found'                => 'Cart item not found',
            'new-guest-cart-created'             => 'New guest cart created with unique session token',
            'select-items-to-remove'             => 'Please select items to remove',
            'select-items-to-move-wishlist'      => 'Please select items to move to wishlist',
            'invalid-or-expired-token'           => 'Cart token is invalid or expired. Please create a new cart.',
            'invalid-token-of-login-user'        => 'Login user token is invalid.',
        ],

        'token-verification' => [
            'invalid-operation'                 => 'Invalid operation',
            'invalid-input-data'                => 'Invalid input data',
            'token-required'                    => 'Token is required',
            'invalid-token-format'              => 'Invalid token format',
            'token-not-found-or-expired'        => 'Token not found or has expired',
            'customer-not-found'                => 'Customer not found',
            'customer-account-suspended'        => 'Customer account is suspended',
            'error-verifying-token'             => 'Error verifying token',
            'token-is-valid'                    => 'Token is valid',
        ],

        'forgot-password' => [
            'invalid-operation'                 => 'Invalid operation',
            'invalid-input-data'                => 'Invalid input data',
            'email-required'                    => 'Email is required',
            'reset-link-sent'                   => 'Reset link sent successfully to your email',
            'email-not-found'                   => 'Email address not found',
            'error-sending-reset-link'          => 'An error occurred while sending reset link',
        ],

        'logout' => [
            'invalid-operation'                 => 'Invalid operation',
            'invalid-input-data'                => 'Invalid input data',
            'token-required'                    => 'Token is required',
            'invalid-token-format'              => 'Invalid token format',
            'logged-out-successfully'           => 'Logged out successfully',
            'token-not-found-or-expired'        => 'Token not found or already expired',
            'error-during-logout'               => 'Error during logout',
        ],

        'address' => [
            'deleted-successfully'              => 'Address deleted successfully',
            'authentication-required'           => 'Authentication token is required',
            'invalid-token'                     => 'Invalid or expired token',
            'unknown-operation'                 => 'Unknown operation',
            'address-id-required'               => 'Address ID is required',
            'address-not-found'                 => 'Address not found or does not belong to this customer',
            'retrieved'                         => 'Addresses retrieved successfully',
            'fetch-failed'                      => 'Failed to fetch addresses:',
        ],

        'customer-profile' => [
            'authentication-required'           => 'Authentication token is required. Please provide token in query input',
            'invalid-token'                     => 'Invalid or expired token',
        ],

        'customer' => [
            'password-mismatch'                 => 'Password and confirm password do not match',
            'confirm-password-required'         => 'Confirm password is required when changing password',
            'unauthenticated'                   => 'Unauthenticated. Please login to perform this action',
        ],

        'product-review' => [
            'product-id-required'               => 'Product ID is required',
            'product-not-found'                 => 'Product not found',
            'rating-invalid'                    => 'Rating must be between 1 and 5',
            'title-required'                    => 'Review title is required',
            'comment-required'                  => 'Review comment is required',
        ],

        'product' => [
            'not-found-with-sku'                => 'No product found with SKU',
            'not-found-with-url-key'            => 'No product found with URL key',
            'parameters-required'               => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided'                 => 'No authentication token provided. Please provide token in Authorization header as "Bearer <token>" or in input.token field',
            'invalid-or-expired-token'          => 'Invalid or expired token',
            'request-not-found'                 => 'Request not found in context',
            'token-required'                    => 'Authentication token is required. Please provide the token either in the GraphQL mutation input field or in the Authorization header as "Bearer <token>"',
            'unknown-resource'                  => 'Unknown resource',
            'cannot-update-other-profile'       => 'Unauthorized: Cannot update another customer profile',
        ],

        'upload' => [
            'invalid-base64'                    => 'Invalid base64 encoded image data',
            'size-exceeds-limit'                => 'Image size must not exceed 5MB',
            'invalid-format'                    => 'Invalid image format. Please provide base64 encoded image with data URI scheme (data:image/jpeg;base64,...)',
            'failed'                            => 'Image upload failed',
        ],

        'attribute' => [
            'code-already-exists'               => 'The attribute code already exists',
        ],

        'login' => [
            'invalid-credentials'               => 'Invalid email or password',
            'account-suspended'                 => 'Your account has been suspended',
            'successful'                        => 'You have logged in successfully',
            'invalid-request'                   => 'Invalid login request',
        ],

        'checkout' => [
            'invalid-input'                     => 'Invalid input data for checkout operation',
            'billing-address-required'          => 'Billing address is required',
            'shipping-address-required'         => 'Shipping address is required for shipments',
            'address-save-failed'               => 'Failed to save address',
            'address-saved'                     => 'Address saved successfully',
            'shipping-method-required'          => 'Shipping method is required',
            'invalid-shipping-method'           => 'Invalid or unavailable shipping method',
            'shipping-method-save-failed'       => 'Failed to save shipping method',
            'shipping-method-saved'             => 'Shipping method saved successfully',
            'shipping-method-error'             => 'Error saving shipping method',
            'payment-method-required'           => 'Payment method is required',
            'invalid-payment-method'            => 'Invalid or unavailable payment method',
            'payment-method-save-failed'        => 'Failed to save payment method',
            'payment-method-saved'              => 'Payment method saved successfully',
            'payment-method-error'              => 'Error saving payment method',
            'order-creation-failed'             => 'Order creation failed: Order ID is null or order not persisted',
            'order-retrieval-failed'            => 'Failed to retrieve created order',
            'order-creation-error'              => 'Failed to create order',
            'cart-empty'                        => 'Cart is empty',
            'account-suspended'                 => 'Your account has been suspended. Please contact support.',
            'account-inactive'                  => 'Your account is inactive. Please contact support.',
            'minimum-order-not-met'             => 'Minimum order amount is :amount',
            'email-required'                    => 'Email address is required for order creation',
            'unknown-operation'                 => 'Unknown checkout operation',
        ],

        'customer-addresses' => [
            'token-required'                    => 'Token is required to fetch customer addresses',
            'invalid-or-expired-token'          => 'Invalid or expired token',
            'token-validation-failed'           => 'Token validation failed',
        ],

        'product' => [
            'type'                              => 'Product Type',
            'attribute-family'                  => 'Attribute Family',
            'sku'                               => 'SKU',
            'name'                              => 'Name',
            'description'                       => 'Description',
            'short-description'                 => 'Short Description',
            'status'                            => 'Status',
            'new'                               => 'New',
            'featured'                          => 'Featured',
            'price'                             => 'Price',
            'special-price'                     => 'Special Price',
            'weight'                            => 'Weight',
            'cost'                              => 'Cost',
            'length'                            => 'Length',
            'width'                             => 'Width',
            'height'                            => 'Height',
            'color'                             => 'Color',
            'size'                              => 'Size',
            'brand'                             => 'Brand',
            'super-attributes'                  => 'Super Attributes',
        ],

        'compare-item' => [
            'id-required'                       => 'तुलना आइटम ID आवश्यक है',
            'invalid-id-format'                 => 'अमान्य ID प्रारूप। "/api/shop/compare-items/1" या संख्यात्मक ID जैसे IRI प्रारूप की अपेक्षा है',
            'not-found'                         => 'तुलना आइटम नहीं मिला',
            'product-id-required'               => 'उत्पाद ID आवश्यक है',
            'customer-id-required'              => 'ग्राहक ID आवश्यक है',
            'product-not-found'                 => 'उत्पाद नहीं मिला',
            'customer-not-found'                => 'ग्राहक नहीं मिला',
            'already-exists'                    => 'यह उत्पाद पहले से ही आपकी तुलना सूची में है',
        ],

        'downloadable-product' => [
            'download-link-not-found'           => 'डाउनलोड लिंक नहीं मिला या समाप्त हो गया है',
            'purchased-link-not-found'          => 'खरीदा गया लिंक नहीं मिला',
            'file-not-found'                    => 'फ़ाइल नहीं मिली',
            'download-successful'               => 'फ़ाइल डाउनलोड के लिए तैयार है',
            'token-required'                    => 'डाउनलोड टोकन आवश्यक है',
            'invalid-token'                     => 'डाउनलोड टोकन अमान्य या समाप्त है',
            'token-expired'                     => 'डाउनलोड टोकन समाप्त हो गया है। कृपया एक नया बनाएं',
            'access-denied'                     => 'एक्सेस अस्वीकृत: आपके पास इस फ़ाइल को डाउनलोड करने की अनुमति नहीं है',
            'redirect-external-url'             => 'बाहरी डाउनलोड URL पर पुनः निर्देशन',
            'file-error'                        => 'आपके डाउनलोड अनुरोध को संसाधित करते समय एक त्रुटि हुई',
            'unauthorized-access'               => 'डाउनलोड संसाधन के लिए अनुपलब्ध प्रवेश',
        ],
    ],
];
