<?php

return [

    'graphql' => [
        'cart' => [
            'authentication-required' => 'Authentication token is required',
            'invalid-token' => 'Invalid or expired authentication token',
            'unauthorized-access' => 'Unauthorized access to cart',
            'authenticated-only' => 'Only authenticated users can fetch their carts',
            'merge-requires-auth' => 'Guest merge requires authentication',
            'unknown-operation' => 'Unknown cart operation',

            'cart-not-found' => 'Cart not found',
            'guest-cart-not-found' => 'Guest cart not found',
            'product-not-found' => 'Product not found',

            'product-id-quantity-required' => 'Product ID and quantity are required',
            'cart-item-id-quantity-required' => 'Cart item ID and quantity are required',

            'file-token-invalid' => 'The uploaded file reference is invalid or has expired. Upload the file again.',
            'file-token-forbidden' => 'This uploaded file does not belong to your cart.',
            'customizable-option-required' => 'The customizable option ":label" is required.',
            'customizable-file-option-invalid' => 'The selected option is not a file option for this product.',
            'customizable-file-invalid' => 'The file is missing, has an unsupported extension, or is too large.',
            'cart-item-id-required' => 'Cart item ID is required',
            'item-ids-required' => 'Item IDs array is required',

            'event-booking-quantity-not-changeable' => 'Event booking product quantity cannot be changed. Quantity is determined by ticket selection.',
            'appointment-booking-quantity-not-changeable' => 'Appointment booking product quantity cannot be changed.',
            'coupon-code-required' => 'Coupon code is required',
            'address-data-required' => 'Country, state, and postcode are required',
            'grouped-qty-required' => 'Grouped product requires selected quantities. Pass groupedQty as JSON string, e.g. {"101":2,"102":1}.',
            'grouped-qty-must-include-all' => 'Grouped product requires quantities for all associated products. Missing IDs: :ids.',
            'grouped-qty-invalid-associated' => 'Grouped product quantities contain invalid associated product IDs: :ids.',
            'grouped-qty-invalid-quantity' => 'Invalid quantity provided for associated product ID :id. Quantity must be a non-negative integer.',
            'bundle-qty-not-changeable' => 'Quantity for bundle option ":option" cannot be changed. The allowed quantity is :qty.',

            'add-product-failed' => 'Failed to add product to cart',
            'update-item-failed' => 'Failed to update cart item',
            'remove-item-failed' => 'Failed to remove cart item',
            'apply-coupon-failed' => 'Failed to apply coupon',
            'remove-coupon-failed' => 'Failed to remove coupon',
            'coupon-applied' => 'Coupon applied successfully',
            'coupon-removed' => 'Coupon removed successfully',
            'move-to-wishlist-failed' => 'Failed to move item to wishlist',
            'estimate-shipping-failed' => 'Failed to estimate shipping',

            'product-added-successfully' => 'Product added to cart successfully',
            'cart-item-updated-successfully' => 'Cart item updated successfully',
            'guest-cart-merged' => 'Guest cart merged successfully',
            'using-authenticated-cart' => 'Using authenticated customer cart',
            'cart-item-not-found' => 'Cart item not found',
            'new-guest-cart-created' => 'New guest cart created with unique session token',
            'select-items-to-remove' => 'Please select items to remove',
            'select-items-to-move-wishlist' => 'Please select items to move to wishlist',
            'invalid-or-expired-token' => 'Cart token is invalid or expired. Please create a new cart.',
            'invalid-token-of-login-user' => 'Login user token is invalid.',
        ],

        'token-verification' => [
            'invalid-operation' => 'Invalid operation',
            'invalid-input-data' => 'Invalid input data',
            'token-required' => 'Token is required',
            'invalid-token-format' => 'Invalid token format',
            'token-not-found-or-expired' => 'Token not found or has expired',
            'customer-not-found' => 'Customer not found',
            'customer-account-suspended' => 'Customer account is suspended',
            'error-verifying-token' => 'Error verifying token',
            'token-is-valid' => 'Token is valid',
            'unauthenticated' => 'Unauthenticated. Please login to perform this action',
        ],

        'forgot-password' => [
            'invalid-operation' => 'Invalid operation',
            'invalid-input-data' => 'Invalid input data',
            'email-required' => 'Email is required',
            'reset-link-sent' => 'Reset link sent successfully to your email',
            'email-not-found' => 'Email address not found',
            'error-sending-reset-link' => 'An error occurred while sending reset link',
        ],

        'logout' => [
            'invalid-operation' => 'Invalid operation',
            'invalid-input-data' => 'Invalid input data',
            'token-required' => 'Token is required',
            'invalid-token-format' => 'Invalid token format',
            'logged-out-successfully' => 'Logged out successfully',
            'token-not-found-or-expired' => 'Token not found or already expired',
            'error-during-logout' => 'Error during logout',
            'unauthenticated' => 'Unauthenticated. Please login to perform this action',
        ],

        'address' => [
            'deleted-successfully' => 'Address deleted successfully',
            'authentication-required' => 'Authentication token is required',
            'invalid-token' => 'Invalid or expired token',
            'unknown-operation' => 'Unknown operation',
            'address-id-required' => 'Address ID is required',
            'address-not-found' => 'Address not found or does not belong to this customer',
            'retrieved' => 'Addresses retrieved successfully',
            'fetch-failed' => 'Failed to fetch addresses:',
        ],

        'customer-profile' => [
            'authentication-required' => 'Authentication token is required. Please provide token in query input',
            'invalid-token' => 'Invalid or expired token',
            'profile-updated' => 'Customer profile updated successfully',
        ],

        'customer' => [
            'password-mismatch' => 'Password and confirm password do not match',
            'confirm-password-required' => 'Confirm password is required when changing password',
            'current-password-required' => 'Current password is required to change your password',
            'current-password-incorrect' => 'The current password you entered is incorrect',
            'unauthenticated' => 'Unauthenticated. Please login to perform this action',
            'id-required' => 'Customer ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/admin/customers/1" or numeric ID',
            'not-found' => 'Customer not found',
            'phone-special-chars-not-allowed' => 'Mobile number can only contain digits. Special characters are not allowed',
            'invalid-gender' => 'Invalid gender value ":gender". Allowed values are: :valid',
        ],

        'product-review' => [
            'id-required' => 'Product review ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/reviews/1" or numeric ID',
            'not-found' => 'Product review with ID ":id" not found',
            'product-id-required' => 'Product ID is required',
            'product-not-found' => 'Product not found',
            'rating-invalid' => 'Rating must be between 1 and 5',
            'title-required' => 'Review title is required',
            'comment-required' => 'Review comment is required',
            'review-disabled' => 'Product reviews are currently disabled',
            'guest-review-disabled' => 'Guest reviews are not allowed. Please login to submit a review',
        ],

        'customer-review' => [
            'id-required' => 'Customer review ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/customer-reviews/1" or numeric ID',
            'not-found' => 'Customer review with ID ":id" not found',
        ],

        'contact-us' => [
            'success' => 'Your inquiry has been submitted successfully. We will get back to you soon',
            'failed' => 'Unable to send your inquiry at this time. Please try again later',
            'invalid-input' => 'Invalid contact form data',
        ],

        'customer-order' => [
            'id-required' => 'Customer order ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/customer-orders/1" or numeric ID',
            'not-found' => 'Customer order with ID ":id" not found',
        ],

        'return' => [
            'not-found' => 'Return request not found.',
            'disabled' => 'Returns are not enabled for this store.',
            'invalid-input' => 'Missing or invalid return details.',
            'invalid-resolution-type' => 'Resolution type must be "return" or "cancel_items".',
            'agreement-required' => 'You must accept the return policy.',
            'invalid-order' => 'The selected order is not valid.',
            'invalid-item' => 'The selected item is not eligible for return.',
            'qty-exceeds' => 'The requested quantity exceeds the returnable quantity.',
            'created' => 'Return request created successfully.',
            'canceled' => 'Return request canceled successfully.',
            'already-canceled' => 'Return request is already canceled.',
            'reopened' => 'Return request reopened successfully.',
            'reopen-not-allowed' => 'This return request cannot be reopened.',
            'closed' => 'Return request closed successfully.',
            'message-sent' => 'Message sent successfully.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Withdrawal declaration not found.',
            'order-not-found' => 'The order could not be found or is not eligible for withdrawal.',
            'created' => 'Withdrawal declaration filed successfully.',
        ],

        'customer-order-shipment' => [
            'id-required' => 'Shipment ID is required',
            'not-found' => 'Shipment with ID ":id" not found or does not belong to this customer',
            'item-not-found' => 'Shipment item with ID ":id" not found or does not belong to this customer',
        ],

        'cancel-order' => [
            'order-id-required' => 'Order ID is required to cancel an order',
            'not-found' => 'Order with ID ":id" not found or does not belong to this customer',
            'cancel-success' => 'Order has been canceled successfully',
            'cancel-failed' => 'Order cannot be canceled. It may have already been processed, shipped, or canceled',
        ],

        'reorder' => [
            'order-id-required' => 'Order ID is required to reorder',
            'not-found' => 'Order with ID ":id" not found or does not belong to this customer',
            'reorder-success' => ':count item(s) from your previous order have been added to your cart',
            'no-items-added' => 'No items could be added to the cart. Products may be out of stock or unavailable',
        ],

        'customer-downloadable-product' => [
            'id-required' => 'Customer downloadable product ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/customer-downloadable-products/1" or numeric ID',
            'not-found' => 'Customer downloadable product with ID ":id" not found',
        ],

        'customer-invoice' => [
            'id-required' => 'Customer invoice ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/customer-invoices/1" or numeric ID',
            'not-found' => 'Customer invoice with ID ":id" not found',
        ],

        'product' => [
            'not-found' => 'Product not found',
            'not-found-with-sku' => 'No product found with SKU',
            'not-found-with-url-key' => 'No product found with URL key',
            'parameters-required' => 'At least one of the following parameters must be provided: "sku", "id", "urlKey"',
        ],

        'auth' => [
            'no-token-provided' => 'No authentication token provided. Please provide token in Authorization header as "Bearer <token>" or in input.token field',
            'invalid-or-expired-token' => 'Invalid or expired token',
            'request-not-found' => 'Request not found in context',
            'token-required' => 'Authentication token is required. Please provide the token in the Authorization header as "Bearer <token>"',
            'unknown-resource' => 'Unknown resource',
            'cannot-update-other-profile' => 'Unauthorized: Cannot update another customer profile',
        ],

        'upload' => [
            'invalid-base64' => 'Invalid base64 encoded image data',
            'size-exceeds-limit' => 'Image size must not exceed 5MB',
            'invalid-format' => 'Invalid image format. Please provide base64 encoded image with data URI scheme (data:image/jpeg;base64,...)',
            'failed' => 'Image upload failed',
        ],

        'attribute' => [
            'code-already-exists' => 'The attribute code already exists',
            'option-id-required' => 'The "attributeId" parameter is required when querying attribute options directly',
            'id-required' => 'Attribute ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/attributes/1" or numeric ID',
            'not-found' => 'Attribute not found',
        ],

        'attribute-option' => [
            'id-required' => 'Attribute option ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/attribute-options/1" or numeric ID',
            'not-found' => 'Attribute option not found',
        ],

        'channel' => [
            'id-required' => 'Channel ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/channels/1" or numeric ID',
            'not-found' => 'Channel not found',
        ],

        'country-state' => [
            'id-required' => 'Country state ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/country-states/1" or numeric ID',
            'not-found' => 'Country state not found',
            'country-id-required' => 'Country ID is required',
        ],

        'country' => [
            'id-required' => 'Country ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/countries/1" or numeric ID',
            'not-found' => 'Country not found',
        ],

        'login' => [
            'invalid-credentials' => 'Invalid email or password',
            'account-suspended' => 'Your account has been suspended',
            'successful' => 'You have logged in successfully',
            'invalid-request' => 'Invalid login request',
        ],

        'checkout' => [
            'invalid-input' => 'Invalid input data for checkout operation',
            'billing-address-required' => 'Billing address is required',
            'shipping-address-required' => 'Shipping address is required for shipments',
            'address-save-failed' => 'Failed to save address',
            'address-saved' => 'Address saved successfully',
            'shipping-method-required' => 'Shipping method is required',
            'invalid-shipping-method' => 'Invalid or unavailable shipping method',
            'shipping-method-save-failed' => 'Failed to save shipping method',
            'shipping-method-saved' => 'Shipping method saved successfully',
            'shipping-method-error' => 'Error saving shipping method',
            'payment-method-required' => 'Payment method is required',
            'invalid-payment-method' => 'Invalid or unavailable payment method',
            'payment-method-save-failed' => 'Failed to save payment method',
            'payment-method-saved' => 'Payment method saved successfully',
            'payment-method-error' => 'Error saving payment method',
            'order-placed' => 'Order placed successfully',
            'order-creation-failed' => 'Order creation failed: Order ID is null or order not persisted',
            'order-retrieval-failed' => 'Failed to retrieve created order',
            'order-creation-error' => 'Failed to create order',
            'cart-empty' => 'Cart is empty',
            'account-suspended' => 'Your account has been suspended. Please contact support.',
            'account-inactive' => 'Your account is inactive. Please contact support.',
            'minimum-order-not-met' => 'Minimum order amount is :amount',
            'email-required' => 'Email address is required for order creation',
            'unknown-operation' => 'Unknown checkout operation',
        ],

        'customer-addresses' => [
            'token-required' => 'Token is required to fetch customer addresses',
            'invalid-or-expired-token' => 'Invalid or expired token',
            'token-validation-failed' => 'Token validation failed',
        ],

        'product' => [
            'type' => 'Product Type',
            'attribute-family' => 'Attribute Family',
            'sku' => 'SKU',
            'name' => 'Name',
            'description' => 'Description',
            'short-description' => 'Short Description',
            'status' => 'Status',
            'new' => 'New',
            'featured' => 'Featured',
            'price' => 'Price',
            'special-price' => 'Special Price',
            'weight' => 'Weight',
            'cost' => 'Cost',
            'length' => 'Length',
            'width' => 'Width',
            'height' => 'Height',
            'color' => 'Color',
            'size' => 'Size',
            'brand' => 'Brand',
            'super-attributes' => 'Super Attributes',
        ],

        'downloadable-product' => [
            'download-link-not-found' => 'Download link not found or expired',
            'purchased-link-not-found' => 'Purchased link not found',
            'file-not-found' => 'File not found',
            'download-successful' => 'File ready for download',
            'token-required' => 'Download token is required',
            'invalid-token' => 'Invalid or expired download token',
            'token-expired' => 'Download token has expired. Please generate a new token',
            'access-denied' => 'Access denied: You do not have permission to download this file',
            'redirect-external-url' => 'Redirecting to external download URL',
            'file-error' => 'An error occurred while processing your download request',
            'unauthorized-access' => 'Unauthorized access to download resource',
        ],

        'locale' => [
            'id-required' => 'Locale ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/locales/1" or numeric ID',
            'not-found' => 'Locale not found',
        ],

        'category' => [
            'id-required' => 'Category ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/categories/1" or numeric ID',
            'not-found' => 'Category not found',
        ],

        'compare-item' => [
            'id-required' => 'Compare item ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/compare-items/1" or numeric ID',
            'not-found' => 'Compare item not found',
            'product-id-required' => 'Product ID is required',
            'customer-id-required' => 'Customer ID is required',
            'product-not-found' => 'Product not found',
            'customer-not-found' => 'Customer not found',
            'already-exists' => 'This product is already in your comparison list',
            'delete-all-success' => 'All compare items have been removed successfully',
        ],

        'wishlist' => [
            'id-required' => 'Wishlist item ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/wishlists/1" or numeric ID',
            'not-found' => 'Wishlist item not found',
            'product-id-required' => 'Product ID is required',
            'customer-id-required' => 'Customer ID is required',
            'product-not-found' => 'Product not found',
            'product-disabled' => 'This product is currently disabled',
            'customer-not-found' => 'Customer not found',
            'already-exists' => 'This product is already in your wishlist',
            'added' => 'Item Successfully Added To Wishlist',
            'removed' => 'Item Successfully Removed From Wishlist',
            'product-removed' => 'Product has been removed',
            'wishlist-item-id-required' => 'Wishlist item ID is required',
            'invalid-quantity' => 'Quantity must be greater than 0',
            'move-to-cart-missing-options' => 'Product has missing required options. Please configure it manually',
            'moved-to-cart-success' => 'Item moved to cart successfully',
            'delete-all-success' => 'All wishlist items have been removed successfully',
        ],

        'gdpr' => [
            'disabled' => 'GDPR data requests are disabled. Please enable GDPR from the admin configuration.',
            'unauthenticated' => 'You must be logged in to manage GDPR data requests.',
            'not-found' => 'GDPR data request not found',
            'type-required' => 'The request type is required',
            'type-invalid' => 'The request type must be either "delete" or "update"',
            'message-required' => 'The message is required',
            'raised' => 'Your GDPR data request has been raised successfully.',
            'revoked' => 'Your GDPR data request has been revoked successfully.',
            'revoke-not-allowed' => 'This GDPR data request can no longer be revoked.',
            'deleted' => 'Your GDPR data request has been deleted successfully.',
        ],

        'theme-customization' => [
            'id-required' => 'Theme Customization ID is required',
            'invalid-id-format' => 'Invalid ID format. Expected IRI format like "/api/shop/theme-customizations/1" or numeric ID',
            'not-found' => 'Theme Customization not found',
        ],

        'install' => [
            'starting' => 'Starting API Platform installation...',
            'completed-success' => '✓ API Platform installation completed successfully!',
            'completed-info' => 'Please run: composer dump-autoload',
            'failed' => '✗ Installation failed: ',

            'provider-already-registered' => 'ℹ Service Provider already registered',
            'provider-registered' => '✓ Service Provider registered in bootstrap/providers.php',
            'provider-file-not-found' => 'File not found: :file',
            'provider-permission-denied' => 'Permission denied: Cannot write to :file. Please check file permissions.',

            'config-already-exists' => 'ℹ Configuration file already exists',
            'config-published' => '✓ API Platform configuration published to config/',
            'config-source-not-found' => 'Source config file not found: :file',
            'config-permission-denied' => 'Permission denied: Cannot write to :directory. Please check directory permissions.',

            'composer-updated' => '✓ Composer autoload configuration updated',
            'composer-file-not-found' => 'File not found: :file',
            'composer-permission-denied' => 'Permission denied: Cannot write to :file. Please check file permissions.',

            'translatable-not-found' => 'ℹ TranslatableModel not found - skipping',
            'translatable-already-abstract' => 'ℹ TranslatableModel already abstract',
            'translatable-made-abstract' => '✓ TranslatableModel made abstract',
            'translatable-permission-denied' => 'Permission denied: Cannot write to :file. Please check file permissions.',

            'providers-already-registered' => 'ℹ API Platform providers already registered',
            'providers-registered' => '✓ API Platform providers registered in bootstrap/app.php',
            'providers-file-not-found' => 'File not found: :file',
            'providers-permission-denied' => 'Permission denied: Cannot write to :file. Please check file permissions.',
            'providers-not-found' => 'Could not find Application creation in bootstrap/app.php',

            'assets-published' => '✓ Package assets published',
            'running-migrations' => 'Running database migrations...',
            'migrations-completed' => '✓ Database migrations completed',
            'clearing-caches' => 'Clearing and optimizing caches...',
            'caches-optimized' => '✓ Caches cleared and optimized',
            'generating-api-key' => 'Generating API key...',
            'api-key-generated' => '✓ API key generated successfully',
            'api-key-already-exists' => 'ℹ Default Store API key already exists - skipping generation',

            'migrations-error' => 'Database migrations failed.',
            'migrations-error-running' => 'Error running database migrations: :error',
            'cache-clear-error' => 'Cache clearing failed.',
            'cache-optimize-error' => 'Optimization failed.',
            'cache-error' => 'Error clearing and optimizing caches: :error',
            'api-key-generation-error' => 'API key generation failed.',
            'api-key-error' => 'Error generating API key: :error',

            'key-management-required' => '--key is required for :action action',
            'key-rotation-error' => 'Error rotating key: :error',
            'key-deactivation-error' => 'Error deactivating key: :error',
            'key-not-found' => 'API key not found: :identifier',

            'maintenance-starting' => '🔄 Starting API Key Maintenance...',
            'maintenance-complete' => '✅ API Key Maintenance Complete',
            'cleanup-expired-keys' => '🧹 Cleaning up expired keys...',
            'cleanup-expired-none' => '   ℹ️ No expired keys to clean up',
            'invalidate-deprecated' => '⚠️ Invalidating deprecated keys...',
            'invalidate-deprecated-none' => '   ℹ️ No deprecated keys to invalidate',
            'notify-expiring' => '📧 Sending expiration notifications...',
            'notify-expiring-none' => '   ℹ️ No keys requiring notifications',

            'key-rotated-success' => '✅ Key rotated successfully!',
            'key-deactivated-success' => '✅ Key deactivated successfully!',
            'deactivation-cancelled' => 'Deactivation cancelled.',
            'cleanup-cancelled' => 'Cleanup cancelled.',

            'key-status-title' => 'Key Status: :name',
            'key-status-active' => 'Active: :status',
            'key-status-usable' => 'Usable: :status',
            'key-status-expired' => 'Expired: :status',
            'key-status-deprecated' => 'Deprecated: :status',
            'key-status-expires-at' => 'Expires At: :expires',
            'key-status-days-until-expiry' => 'Days Until Expiry: :days',
            'key-status-last-used' => 'Last Used: :date',
            'key-status-rotated-from' => 'Rotated From: :key',
            'key-status-rotated-keys' => 'Keys Rotated From This: :count',

            'policy-compliance-summary' => 'API Key Rotation Policy Compliance Summary',
            'available-actions' => 'Available actions: rotate, deactivate, cleanup, status, expiring, unused, summary',

            'asset-linked-success' => '✓ API Platform assets linked successfully',
            'symlink-create-failed' => 'Could not create symlink, copying assets instead...',
            'asset-copy-warning' => 'Warning: Could not link or copy API Platform assets. Manual setup may be required.',
            'asset-copied-success' => '✓ API Platform assets copied successfully',

            'key-name-required' => 'Storefront key name cannot be empty.',
            'key-generated-success' => 'Storefront key generated successfully!',
            'key-details' => 'Key Details:',
            'key-secure-warning' => 'Keep this key secure! It will be used in X-STOREFRONT-KEY header.',
            'key-share-warning' => 'Do not share this key publicly or commit it to version control.',

            'key-already-exists' => 'A storefront key with name \':name\' already exists.',
            'cleanup-success' => '✅ Cleaned up :count expired keys',
            'confirm-cleanup' => 'This will soft-delete all expired keys. Continue?',
            'confirm-deactivate' => 'Are you sure you want to deactivate key: :name?',

            'old-key' => 'Old Key: :name',
            'old-key-id' => 'Old Key ID: :id',
            'deprecation-date' => 'Deprecation Date: :date',
            'new-key' => 'New Key: :name',
            'new-key-id' => 'New Key ID: :id',
            'new-key-value' => 'New Key Value: :key',
            'expires-at' => 'Expires At: :date',
            'invalid-action' => 'Invalid action: :action',
            'key-active' => 'Active: :status',
            'key-usable' => 'Usable: :status',
            'key-expired' => 'Expired: :status',
            'key-deprecated' => 'Deprecated: :status',
            'key-field-id' => 'ID',
            'key-field-name' => 'Name',
            'key-field-key' => 'Key',
            'key-field-rate-limit' => 'Rate Limit',
            'key-field-status' => 'Status',
            'key-days' => ' days',
            'key-never' => 'Never',
            'key-requests-minute' => ' requests/minute',
            'key-unlimited' => 'Unlimited',
            'rate-limit-exceeded' => 'Requested rate limit exceeds maximum allowed :max. Setting to :max requests/minute.',

            'vendor-path-not-found' => 'API Platform vendor path not found at: :path',
            'assets-already-linked' => 'API Platform assets already linked at: :path',
            'no-keys-expiring' => 'No keys expiring in the next :days days',
            'keys-expiring-title' => 'Keys expiring in the next :days days:',
            'key-display-format' => '• :name (ID: :id)',
            'key-expires-display' => '  Expires: :date (:days days left)',
            'no-unused-keys' => 'No unused keys found (> :days days)',
            'unused-keys-title' => 'Unused keys (> :days days):',
            'key-last-used-display' => '  Last Used: :date',
            'total-keys' => 'Total Keys: :count',
            'valid-keys' => 'Valid Keys: :count',
            'expired-keys' => 'Expired Keys: :count',
            'deprecated-keys' => 'Deprecated Keys: :count',
            'keys-expiring-soon' => 'Keys Expiring Soon (7 days): :count',
            'unused-keys-summary' => 'Unused Keys (90 days): :count',
            'recently-rotated' => 'Recently Rotated (30 days): :count',
            'cleanup-success-message' => '   ✅ Cleaned up :count expired keys',
            'invalidate-success-message' => '   ✅ Invalidated :count deprecated keys',
            'notify-success-message' => '   ✅ Sent :count expiration notifications',
            'notify-failed-message' => '   ⚠️ Failed to notify about :key: :error',
            'publish-assets-warning' => 'Warning: Could not publish package assets. :error',
            'env-config-saved' => '✓ API Platform environment configuration saved successfully',
            'api-endpoints' => 'API Endpoints:',
            'api-documentation' => 'API Documentation: :url',
            'api-landing-page' => 'API Landing Page: :url',
            'graphql-playground' => 'GraphQL Playground: :url',
            'rest-api-storefront' => 'Rest API Storefront Swagger: :url',
            'rest-api-admin' => 'Rest API Admin Swagger: :url',
        ],
    ],

    'admin' => [
        // login / logout / forgot-password / profile-update keys removed in the
        // 2026-05-27 auth refactor — admin clients now authenticate via
        // pre-issued integration tokens (Bearer header, AdminApiGuard).
        'profile' => [
            'unauthenticated' => 'You are not authenticated.',
        ],

        'dashboard' => [
            'invalid-type' => 'Unknown dashboard stat type. Valid types: over-all, today, stock-threshold-products, total-sales, total-visitors, top-selling-products, top-customers.',
        ],

        'rma' => [
            'not-found' => 'Return request not found.',
            'invalid-input' => 'Missing or invalid return details.',
            'invalid-order' => 'The selected order is not valid.',
            'invalid-item' => 'The selected item is not eligible for return.',
            'invalid-resolution-type' => 'Resolution type must be "return" or "cancel_items".',
            'qty-exceeds' => 'The requested quantity exceeds the returnable quantity.',
            'created' => 'Return request created successfully.',
            'status-updated' => 'Return status updated successfully.',
            'invalid-status' => 'Invalid return status.',
            'reopened' => 'Return request reopened successfully.',
            'reopen-not-allowed' => 'This return request cannot be reopened.',
            'refund-failed' => 'The refund could not be created for this return.',
            'message-sent' => 'Message sent successfully.',
            'no-permission' => 'You do not have permission to perform this action.',
            'reason-not-found' => 'Return reason not found.',
            'reason-deleted' => 'Return reason deleted successfully.',
            'status-not-found' => 'Return status not found.',
            'status-deleted' => 'Return status deleted successfully.',
            'status-default-undeletable' => 'A default return status cannot be deleted.',
            'rule-not-found' => 'Return rule not found.',
            'rule-deleted' => 'Return rule deleted successfully.',
            'custom-field-not-found' => 'Return custom field not found.',
            'custom-field-deleted' => 'Return custom field deleted successfully.',
            'custom-field-options-required' => 'At least one option is required for this field type.',
            'mass-indices-required' => 'At least one id is required.',
            'mass-delete-success' => 'Selected items deleted.',
            'mass-update-success' => 'Selected items updated.',
            'mass-value-invalid' => 'Status value must be 0 or 1.',
        ],

        'eu-withdrawal' => [
            'not-found' => 'Withdrawal declaration not found.',
            'no-permission' => 'You do not have permission to manage EU withdrawals.',
            'declined' => 'Withdrawal marked as declined.',
            'refunded' => 'Withdrawal marked as refunded.',
            'confirmation-resent' => 'Confirmation email resent successfully.',
            'confirmation-failed' => 'The confirmation email could not be sent.',
        ],

        'reporting' => [
            'invalid-entity' => 'Unknown reporting entity.',
            'invalid-type' => 'Unknown reporting stat type for this entity.',
            'export-format-unsupported' => 'Only the csv, xls and xlsx export formats are supported.',
        ],

        'configuration' => [
            'unauthenticated' => 'You are not authenticated.',
            'no-permission' => 'You do not have permission to update configuration.',
            'slug-required' => 'Query parameter `slug` is required.',
            'slug-not-found' => 'No configuration section is registered for the given slug.',
            'values-required' => 'At least one value is required under `values`.',
            'scope-escape' => 'The key `:key` is outside the requested slug subtree.',
            'unknown-field' => 'The key `:key` is not a registered configuration field.',
            'validation-failed' => 'One or more configuration values failed validation.',
            'file-upload-rest-only' => 'Binary file uploads for the field `:field` are only supported over the REST multipart endpoint, not GraphQL.',
            'custom-view-readonly' => 'The field `:field` is a custom blade-rendered field and cannot be updated via the API.',
            'update-success' => 'Configuration updated successfully.',
        ],

        'customer' => [
            'not-found' => 'Customer not found.',
            'no-permission' => 'You do not have permission to perform this action.',
            'has-active-orders' => 'Customer has pending or processing orders and cannot be deleted.',
            'deleted' => 'Customer deleted successfully.',
            'mass-delete-indices-required' => 'At least one customer id is required.',
            'mass-delete-success' => 'Customers deleted.',
            'mass-update-indices-required' => 'At least one customer id is required.',
            'mass-update-value-invalid' => 'Status value must be 0 or 1.',
            'mass-update-success' => 'Customer status updated.',

            'address' => [
                'not-found' => 'Address not found.',
                'not-owned' => 'This address does not belong to the specified customer.',
                'customer-id-required' => 'customerId is required to identify the address owner.',
                'deleted' => 'Address deleted successfully.',
            ],

            'note' => [
                'empty' => 'Note text is required.',
                'created' => 'Note added successfully.',
            ],

            'impersonate' => [
                'success' => 'Impersonation token issued.',
            ],

            'review' => [
                'not-found' => 'Review not found.',
                'no-permission' => 'You do not have permission to perform this action.',
                'invalid-status' => 'Status must be one of: pending, approved, disapproved.',
                'updated' => 'Review updated successfully.',
                'deleted' => 'Review deleted successfully.',
                'mass-delete-indices-required' => 'At least one review id is required.',
                'mass-delete-success' => 'Reviews deleted.',
                'mass-update-indices-required' => 'At least one review id is required.',
                'mass-update-value-invalid' => 'Status value must be one of: pending, approved, disapproved.',
                'mass-update-success' => 'Review status updated.',
            ],

            'group' => [
                'not-found' => 'Customer group not found.',
                'deleted' => 'Customer group deleted successfully.',
                'is-system' => 'System customer groups cannot be deleted.',
                'has-customers' => 'Customer group has customers attached and cannot be deleted.',
                'system-code-immutable' => 'The code of a system customer group cannot be changed.',
                'system-flag-immutable' => 'The is_user_defined flag of a system customer group cannot be changed.',
                'mass-delete-indices-required' => 'At least one customer group id is required.',
                'mass-delete-success' => 'Customer groups deleted.',
            ],

            'gdpr' => [
                'not-found' => 'GDPR request not found.',
                'no-permission' => 'You do not have permission to perform this action.',
                'deleted' => 'GDPR request deleted successfully.',
                'invalid-status' => 'Status must be one of: pending, processing, declined, approved, revoked.',
                'no-changes' => 'At least one of status or message is required.',
                'already-processed' => 'This GDPR request has already been processed and cannot be re-run.',

                'process' => [
                    'approved' => 'GDPR request approved.',
                    'deleted' => 'GDPR request approved and customer deleted.',
                ],
            ],
        ],

        'order' => [
            'not-found' => 'Order not found.',
            'reorder' => [
                'success' => 'Reorder successful. A new draft cart has been created.',
                'cannot-reorder' => 'This order cannot be reordered (guest order or one or more items are no longer saleable).',
                'failed' => 'Reorder failed. Please try again.',
                'guest-not-supported' => 'Reorder is not supported for guest orders.',
                'items-not-saleable' => 'One or more items in this order are no longer available for purchase.',
                'no-permission' => 'You do not have permission to create orders.',
                'disabled-in-settings' => 'Reorder by admin is disabled in store settings.',
            ],

            'actions' => [
                'cancel' => [
                    'closed' => 'Closed orders cannot be canceled.',
                    'fraud' => 'Fraud orders cannot be canceled.',
                    'nothing-to-cancel' => 'There is nothing to cancel on this order.',
                    'already-processed' => 'This order has already been invoiced or shipped and can no longer be canceled. You can issue a refund instead.',
                    'no-permission' => 'You do not have permission to cancel orders.',
                    'success' => 'Order canceled successfully.',
                    'failed' => 'Could not cancel the order.',
                ],
                'comment' => [
                    'empty' => 'Comment is required.',
                    'success' => 'Comment added to the order.',
                    'failed' => 'Could not add the comment.',
                ],
                'invoice' => [
                    'closed' => 'Closed orders cannot be invoiced.',
                    'fraud' => 'Fraud orders cannot be invoiced.',
                    'nothing-to-invoice' => 'There is nothing to invoice on this order.',
                    'already-invoiced' => 'An invoice has already been generated for this order.',
                    'paypal-standard-blocked' => 'Invoices cannot be created for orders paid through PayPal Standard.',
                    'no-permission' => 'You do not have permission to create invoices.',
                    'items-required' => 'At least one item with a positive quantity is required.',
                    'qty-exceeds' => 'Requested quantity (:requested) exceeds available quantity (:available) for SKU :sku.',
                    'not-found' => 'Invoice not found.',
                    'success' => 'Invoice created successfully.',
                    'failed' => 'Could not create the invoice.',
                    'pdf-failed' => 'Could not generate the invoice PDF.',
                    'email-invalid' => 'A valid recipient email is required.',
                    'duplicate-sent' => 'Invoice email sent to :email.',
                    'mass-status-indices-required' => 'At least one invoice id is required.',
                    'mass-status-value-invalid' => 'Status must be one of: pending, paid, overdue.',
                    'mass-status-success' => 'Invoice status updated successfully.',
                ],
                'shipment' => [
                    'closed' => 'Closed orders cannot be shipped.',
                    'fraud' => 'Fraud orders cannot be shipped.',
                    'nothing-to-ship' => 'There is nothing to ship on this order.',
                    'no-permission' => 'You do not have permission to ship orders.',
                    'source-required' => 'Inventory source is required.',
                    'items-required' => 'At least one item with a positive quantity is required.',
                    'qty-exceeds' => 'Requested quantity (:requested) exceeds available quantity (:available) for SKU :sku.',
                    'inventory-insufficient' => 'Inventory at the selected source is insufficient for SKU :sku.',
                    'not-found' => 'Shipment not found.',
                    'success' => 'Shipment created successfully.',
                    'failed' => 'Could not create the shipment.',
                ],
                'refund' => [
                    'closed' => 'Closed orders cannot be refunded.',
                    'fraud' => 'Fraud orders cannot be refunded.',
                    'nothing-to-refund' => 'There is nothing left to refund on this order.',
                    'no-permission' => 'You do not have permission to create refunds.',
                    'qty-exceeds' => 'Requested quantity (:requested) exceeds available quantity (:available) for SKU :sku.',
                    'amount-zero' => 'The computed refund amount is zero. Adjust quantity, shipping or adjustment values.',
                    'amount-exceeds-max' => 'The refund amount (:amount) exceeds the maximum refundable amount (:max).',
                    'not-found' => 'Refund not found.',
                    'success' => 'Refund created successfully.',
                    'failed' => 'Could not create the refund.',
                ],
            ],
        ],

        'sales' => [
            'no-permission' => 'You do not have permission to view this sales resource.',
            'invoice' => [
                'not-found' => 'Invoice not found.',
                'no-permission' => 'You do not have permission to view invoices.',
            ],
            'shipment' => [
                'not-found' => 'Shipment not found.',
                'no-permission' => 'You do not have permission to view shipments.',
            ],
            'refund' => [
                'not-found' => 'Refund not found.',
                'no-permission' => 'You do not have permission to view refunds.',
                'export-format-unsupported' => 'Only csv, xls and xlsx exports are supported.',
            ],
            'transaction' => [
                'not-found' => 'Transaction not found.',
                'no-permission' => 'You do not have permission to view transactions.',
                'create' => [
                    'invoice-required' => 'The invoiceId field is required.',
                    'payment-method-required' => 'The paymentMethod field is required.',
                    'amount-invalid' => 'The amount field is required and must be numeric.',
                    'invoice-missing' => 'The specified invoice could not be found.',
                    'already-paid' => 'This invoice is already fully paid.',
                    'amount-exceeds' => 'The payment amount exceeds the invoice grand total.',
                    'amount-zero' => 'The payment amount must be greater than zero.',
                ],
            ],
            'booking' => [
                'not-found' => 'Booking not found.',
                'no-permission' => 'You do not have permission to view bookings.',
            ],
            'export' => [
                'format-unsupported' => 'Only csv, xls and xlsx exports are supported.',
            ],
        ],

        'cart' => [
            'not-found' => 'Cart not found.',
            'not-draft' => 'This cart cannot be modified through the admin API.',
            'product-required' => 'productId is required.',
            'product-not-found' => 'Product not found.',
            'booking-unsupported' => 'Booking products cannot be added to an admin draft order. Booking purchases must be made through the customer storefront.',
            'product-not-saleable' => 'This product is not available for sale (out of stock or disabled) and cannot be added to the order.',
            'qty-required' => 'qty is required and must be a non-empty object of itemId => quantity.',
            'cart-item-required' => 'cartItemId is required.',
            'item-added' => 'Item added to cart.',
            'item-add-failed' => 'Could not add the item to the cart.',
            'item-updated' => 'Cart items updated.',
            'item-update-failed' => 'Could not update the cart items.',
            'item-removed' => 'Item removed from cart.',
            'item-remove-failed' => 'Could not remove the cart item.',
            'address-saved' => 'Address saved.',
            'address-required' => 'A billing address is required.',
            'address-incomplete' => 'The :field is required to save the address.',
            'coupon-code-required' => 'A coupon code is required.',
            'coupon-applied' => 'Coupon applied.',
            'coupon-already-applied' => 'Coupon is already applied to this cart.',
            'coupon-not-found' => 'Coupon not found or inactive.',
            'coupon-removed' => 'Coupon removed.',
            'coupon-error' => 'Something went wrong while applying the coupon.',
            'unknown-error' => 'Something went wrong.',
            'draft-created' => 'Draft cart created.',
            'draft-failed' => 'Could not create the draft cart.',
            'addresses-required-for-shipping' => 'Addresses must be saved before selecting a shipping method.',
            'shipping-required-for-payment' => 'Shipping method must be selected before payment method.',
            'shipping-method-required' => 'Shipping method is required.',
            'shipping-method-saved' => 'Shipping method saved.',
            'shipping-method-failed' => 'Could not save the shipping method.',
            'payment-method-required' => 'Payment method is required.',
            'payment-method-saved' => 'Payment method saved.',
            'payment-method-failed' => 'Could not save the payment method.',
            'place-order' => [
                'success' => 'Order placed successfully.',
                'failed' => 'Could not place the order.',
                'error' => 'Could not place the order: cart has errors.',
                'minimum-order-error' => 'The minimum order amount is :amount. Add more items before placing the order.',
                'empty-cart' => 'Cart is empty — add items before placing an order.',
                'addresses-required' => 'Billing and shipping addresses must be saved before placing the order.',
                'shipping-required' => 'A shipping method must be selected before placing the order.',
                'payment-required' => 'A payment method must be selected before placing the order.',
                'payment-method-unsupported' => 'Only cashondelivery and moneytransfer are supported for admin-placed orders.',
            ],
        ],

        'product' => [
            'not-found' => 'Product not found.',

            // Mass actions
            'mass-delete-success' => 'Products deleted successfully.',
            'mass-update-status-success' => 'Products status updated successfully.',
            'indices-required' => 'The indices field is required and must be a non-empty array.',
            'indices-invalid' => 'The indices field must be an array of integer product IDs.',
            'value-invalid' => 'The value field is required and must be 0 or 1.',
            'mass-delete-failed' => 'One or more products could not be deleted.',
            'no-permission' => 'You do not have permission to manage products.',

            // Copy
            'copy-success' => 'Product copied successfully.',
            'copy-failed' => 'Failed to copy product.',
            'copy-variant-not-supported' => 'Variants of configurable products cannot be copied. Copy the parent configurable product instead.',

            // Phases 5.3 — 5.8 + 5.8-booking — Create (all 7 types)
            'create' => [
                'created' => 'Product created successfully.',
                'create-failed' => 'Failed to create product.',
                'sku-required' => 'The sku field is required.',
                'sku-unique' => 'The sku has already been taken.',
                'sku-invalid' => 'The sku may only contain letters, numbers and hyphens (e.g. "sp-001").',
                'attribute-family-required' => 'The attribute_family_id field is required.',
                'attribute-family-not-found' => 'The selected attribute family does not exist.',
                'type-required' => 'The type field is required.',
                'type-not-yet-supported' => 'Product type ":type" is not supported by this API. Allowed types: simple, virtual, downloadable, grouped, bundle, configurable, booking.',
                'super-attributes-required' => 'The super_attributes field is required when type=configurable. Provide a map of attribute code (or id) to option_ids, e.g. { "color": [1,2], "size": [4,5] }.',
                'super-attributes-invalid' => 'The super_attributes field must be a non-empty map of attribute code (or id) to a non-empty list of option_ids.',
                'no-permission' => 'You do not have permission to create products.',
            ],

            // Update (any type)
            'update' => [
                'updated' => 'Product updated successfully.',
                'update-failed' => 'Failed to update product.',
                'id-required' => 'A product id is required.',
                'url-key-required' => 'The url_key field cannot be empty.',
                'url-key-unique' => 'The url_key has already been taken by another product.',
                'special-price-invalid' => 'The special_price must be a number less than price.',
                'special-price-date-range-invalid' => 'The special_price_to must be on or after special_price_from.',
                'boolean-field-invalid' => 'The :field field must be 0 or 1.',
                'categories-invalid' => 'The categories field must be an array of category ids.',
                'channels-invalid' => 'The channels field must be an array of channel ids.',
                'super-attributes-cannot-change' => 'super_attributes cannot be changed after the product is created.',
                'sub-resource-stripped-images' => 'Images must be managed via POST /api/admin/catalog/products/{id}/images. The images field was ignored.',
                'sub-resource-stripped-inventories' => 'Inventories must be managed via PUT /api/admin/catalog/products/{id}/inventories. The inventories field was ignored.',
                'sub-resource-stripped-customer-group-prices' => 'Customer-group prices must be managed via /api/admin/catalog/products/{id}/customer-group-prices. The customer_group_prices field was ignored.',
                'translations-single-locale' => 'Only the requested locale was updated. To update locale(s) :locales, send a separate request with ?locale=<code> for each.',
            ],

            // Delete
            'delete' => [
                'deleted' => 'Product deleted successfully.',
                'delete-failed' => 'Failed to delete product.',
            ],

            // Images (upload / reorder / delete)
            'image' => [
                'uploaded' => 'Product image uploaded successfully.',
                'reordered' => 'Product images reordered successfully.',
                'deleted' => 'Product image deleted successfully.',
                'image-required' => 'An image file is required (multipart field "image").',
                'image-invalid-type' => 'Image type must be one of: bmp, jpeg, jpg, png, webp.',
                'image-too-large' => 'Image is too large. Maximum allowed size is 4 MB.',
                'not-found' => 'Product image not found.',
                'no-permission' => 'You do not have permission to manage product images.',
                'upload-failed' => 'Failed to upload product image.',
                'reorder-failed' => 'Failed to reorder product images.',
                'delete-failed' => 'Failed to delete product image.',
                'order-required' => 'The "order" array is required and must not be empty.',
                'order-invalid' => 'Each entry in "order" must be an object with id and position.',
                'id-not-on-product' => 'Image :id does not belong to this product.',
                'graphql-upload-unsupported' => 'Image upload over GraphQL is not supported. Use POST /api/admin/catalog/products/{productId}/images with multipart/form-data.',
            ],
            'downloadable' => [
                'file-required' => 'A file is required (multipart field "file").',
                'file-too-large' => 'File is too large. Maximum allowed size is 128 MB.',
                'upload-failed' => 'Failed to upload the downloadable file.',
                'file-not-found' => 'No stored file found for this product and attribute.',
                'no-permission' => 'You do not have permission to manage downloadable product files.',
            ],
            'video' => [
                'uploaded' => 'Product video uploaded successfully.',
                'deleted' => 'Product video deleted successfully.',
                'video-required' => 'A video file is required (multipart field "video").',
                'video-invalid-type' => 'Video type must be one of: mp4, webm, ogg, mov.',
                'video-too-large' => 'Video is too large. Maximum allowed size is 100 MB.',
                'not-found' => 'Product video not found.',
                'no-permission' => 'You do not have permission to manage product videos.',
                'upload-failed' => 'Failed to upload product video.',
                'delete-failed' => 'Failed to delete product video.',
                'graphql-upload-unsupported' => 'Video upload over GraphQL is not supported. Use POST /api/admin/catalog/products/{productId}/videos with multipart/form-data.',
            ],

            // Inventory (per-source qty updates)
            'inventory' => [
                'updated' => 'Product inventories saved successfully.',
                'inventories-required' => 'The inventories field is required and must be a non-empty map of inventory_source_id to quantity.',
                'inventories-invalid' => 'The inventories field must be a map of inventory_source_id (positive integer) to quantity (non-negative integer).',
                'source-not-found' => 'Inventory source :id does not exist.',
                'qty-invalid' => 'Each quantity must be a non-negative integer.',
                'no-permission' => 'You do not have permission to manage product inventories.',
                'not-found' => 'Product not found.',
            ],

            // Customer-group prices CRUD
            'customer-group-price' => [
                'created' => 'Customer-group price added successfully.',
                'updated' => 'Customer-group price updated successfully.',
                'deleted' => 'Customer-group price deleted successfully.',
                'qty-required' => 'The qty field is required.',
                'qty-invalid' => 'The qty field must be an integer greater than or equal to 1.',
                'value-type-required' => 'The value_type field is required.',
                'value-type-invalid' => 'The value_type field must be either "fixed" or "discount".',
                'value-required' => 'The value field is required.',
                'value-invalid' => 'The value field must be a non-negative number.',
                'customer-group-not-found' => 'The selected customer group does not exist.',
                'duplicate-qty-group' => 'A customer-group price for this (qty, customer_group_id) combination already exists for the product.',
                'not-found' => 'Customer-group price not found.',
                'no-permission' => 'You do not have permission to manage product customer-group prices.',
            ],
        ],

        'category' => [
            'not-found' => 'Category not found.',

            // CRUD
            'created' => 'Category created successfully.',
            'updated' => 'Category updated successfully.',
            'deleted' => 'Category deleted successfully.',
            'slug-required' => 'The slug field is required.',
            'slug-unique' => 'The slug has already been taken.',
            'name-required' => 'The name field is required.',
            'position-required' => 'The position field is required.',
            'attributes-required' => 'At least one filterable attribute is required.',
            'description-required-for-mode' => 'A description is required for the chosen display mode.',
            'cannot-delete-root' => 'Root and channel-root categories cannot be deleted.',
            'cannot-delete-channel-root' => 'This category is a channel root and cannot be deleted.',
            'delete-failed' => 'Category could not be deleted.',
            'no-permission' => 'You do not have permission to manage categories.',
            'mass-delete-success' => 'Categories deleted successfully.',
            'mass-update-status-success' => 'Categories status updated successfully.',
            'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
            'mass-update-status-value-required' => 'The value field is required and must be 0 or 1.',
        ],

        'attribute' => [
            'not-found' => 'Attribute not found.',

            // CRUD
            'create-success' => 'Attribute created successfully.',
            'update-success' => 'Attribute updated successfully.',
            'delete-success' => 'Attribute deleted successfully.',
            'delete-failed' => 'Attribute could not be deleted.',
            'mass-delete-success' => 'Attributes deleted successfully.',
            'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',

            'code-immutable' => 'Attribute code cannot be changed once the attribute is created.',
            'type-immutable' => 'Attribute type cannot be changed while it has product values.',
            'locale-scope-immutable' => 'Cannot change locale-scope while attribute has product values.',
            'system-attribute' => 'System attributes cannot be deleted.',
            'in-use-family' => 'Attribute is part of one or more attribute families (group IDs: :ids). Remove it from those families first.',

            // Option sub-resource
            'option-not-found' => 'Attribute option not found.',
            'option-not-supported' => 'Attribute type ":type" does not support options. Only select, multiselect, and checkbox attributes can have options.',
            'option-in-use' => 'This option is used by :count product(s) and cannot be deleted.',
            'option-delete-success' => 'Attribute option deleted successfully.',
        ],

        'family' => [
            'not-found' => 'Attribute family not found.',

            // CRUD
            'created' => 'Attribute family created successfully.',
            'updated' => 'Attribute family updated successfully.',
            'deleted' => 'Attribute family deleted successfully.',
            'code-required' => 'The family code is required.',
            'code-unique' => 'The family code must be unique.',
            'name-required' => 'The family name is required.',
            'group-code-required' => 'Each attribute group must have a code.',
            'group-name-required' => 'Each attribute group must have a name.',
            'group-column-invalid' => 'Each attribute group column must be 1 or 2.',
            'last-delete-error' => 'At least one attribute family is required.',
            'attribute-product-error' => 'This attribute family is in use by one or more products. Reassign those products before deleting it.',
            'delete-failed' => 'Attribute family could not be deleted.',
            'no-permission' => 'You do not have permission to manage attribute families.',
        ],

        'settings' => [
            'locale' => [
                'not-found' => 'Locale not found.',
                'created' => 'Locale created successfully.',
                'updated' => 'Locale updated successfully.',
                'deleted' => 'Locale deleted successfully.',
                'cannot-delete-last' => 'At least one locale is required — cannot delete the last remaining locale.',
                'cannot-delete-channel-default' => 'This locale is the default locale of one or more channels and cannot be deleted.',
                'delete-failed' => 'Locale could not be deleted.',
                'no-permission' => 'You do not have permission to manage locales.',
                'mass-delete-success' => 'Locales deleted successfully.',
                'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
            ],

            'theme' => [
                'not-found' => 'Theme customization not found.',
                'created' => 'Theme customization created successfully.',
                'updated' => 'Theme customization updated successfully.',
                'deleted' => 'Theme customization deleted successfully.',
                'delete-failed' => 'Theme customization could not be deleted.',
                'no-permission' => 'You do not have permission to manage theme customizations.',
                'mass-delete-success' => 'Theme customizations deleted successfully.',
                'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
                'mass-update-success' => 'Theme customizations updated successfully.',
                'mass-update-indices-required' => 'The indices field is required and must be a non-empty array.',
                'mass-update-value-invalid' => 'The value field must be 0 or 1.',
            ],

            'inventory-source' => [
                'not-found' => 'Inventory source not found.',
                'created' => 'Inventory source created successfully.',
                'updated' => 'Inventory source updated successfully.',
                'deleted' => 'Inventory source deleted successfully.',
                'code-unique' => 'An inventory source with this code already exists.',
                'last-delete-error' => 'At least one inventory source is required — cannot delete the last remaining source.',
                'in-use' => 'This inventory source is referenced by product inventories and cannot be deleted.',
                'delete-failed' => 'Inventory source could not be deleted.',
                'no-permission' => 'You do not have permission to manage inventory sources.',
                'mass-delete-success' => 'Inventory sources deleted successfully.',
                'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
            ],

            'exchange-rate' => [
                'not-found' => 'Exchange rate not found.',
                'created' => 'Exchange rate created successfully.',
                'updated' => 'Exchange rate updated successfully.',
                'deleted' => 'Exchange rate deleted successfully.',
                'duplicate-pair' => 'An exchange rate for this target currency already exists. Update the existing row or delete it first.',
                'delete-failed' => 'Exchange rate could not be deleted.',
                'no-permission' => 'You do not have permission to manage exchange rates.',
                'mass-delete-success' => 'Exchange rates deleted successfully.',
                'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
                'update-rates-success' => 'Exchange rates updated successfully.',
                'update-rates-failed' => 'Exchange rates could not be updated from the external provider.',
                'provider-not-configured' => 'No external exchange-rate provider is configured.',
            ],

            'tax-rate' => [
                'not-found' => 'Tax rate not found.',
                'created' => 'Tax rate created successfully.',
                'updated' => 'Tax rate updated successfully.',
                'deleted' => 'Tax rate deleted successfully.',
                'delete-failed' => 'Tax rate could not be deleted.',
                'no-permission' => 'You do not have permission to manage tax rates.',
                'zip-code-required' => 'The zip_code field is required when is_zip is false.',
                'zip-range-required' => 'Both zip_from and zip_to are required when is_zip is true.',
            ],

            'tax-category' => [
                'not-found' => 'Tax category not found.',
                'created' => 'Tax category created successfully.',
                'updated' => 'Tax category updated successfully.',
                'deleted' => 'Tax category deleted successfully.',
                'code-unique' => 'The code has already been taken.',
                'cannot-delete-in-use' => 'This tax category still has tax rates attached and cannot be deleted.',
                'delete-failed' => 'Tax category could not be deleted.',
                'no-permission' => 'You do not have permission to manage tax categories.',
            ],

            'role' => [
                'not-found' => 'Role not found.',
                'created' => 'Role created successfully.',
                'updated' => 'Role updated successfully.',
                'deleted' => 'Role deleted successfully.',
                'permissions-required' => 'The permissions field is required when permission_type is custom.',
                'cannot-delete-in-use' => 'This role is assigned to one or more admins and cannot be deleted.',
                'cannot-delete-last-role' => 'At least one role is required — cannot delete the last remaining role.',
                'delete-failed' => 'Role could not be deleted.',
                'no-permission' => 'You do not have permission to manage roles.',
            ],

            'currency' => [
                // Read
                'not-found' => 'Currency not found.',

                // Create / Update / Delete
                'created' => 'Currency created successfully.',
                'updated' => 'Currency updated successfully.',
                'deleted' => 'Currency deleted successfully.',
                'code-required' => 'The code field is required.',
                'code-size' => 'The code must be exactly 3 alphabetical characters.',
                'code-unique' => 'The code has already been taken.',
                'name-required' => 'The name field is required.',
                'cannot-delete-last' => 'At least one currency is required — cannot delete the last remaining currency.',
                'cannot-delete-channel-base' => 'This currency is the base currency of one or more channels and cannot be deleted.',
                'delete-failed' => 'Currency could not be deleted.',
                'no-permission' => 'You do not have permission to manage currencies.',

                // Mass delete
                'mass-delete-success' => 'Currencies deleted successfully.',
                'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
            ],

            'channel' => [
                // Read
                'not-found' => 'Channel not found.',

                // Create / Update / Delete
                'created' => 'Channel created successfully.',
                'updated' => 'Channel updated successfully.',
                'deleted' => 'Channel deleted successfully.',
                'code-unique' => 'The code has already been taken.',
                'hostname-unique' => 'The hostname has already been taken.',
                'default-locale-mismatch' => 'The default_locale_id must appear in the locales list.',
                'base-currency-mismatch' => 'The base_currency_id must appear in the currencies list.',
                'unknown-locale' => 'One or more locale ids do not exist.',
                'unknown-currency' => 'One or more currency ids do not exist.',
                'unknown-inventory-source' => 'One or more inventory_source ids do not exist.',
                'unknown-root-category' => 'The root_category_id does not exist.',
                'cannot-delete-last' => 'At least one channel is required — cannot delete the last remaining channel.',
                'cannot-delete-default' => 'Cannot delete the default application channel.',
                'delete-failed' => 'Channel could not be deleted.',
                'no-permission' => 'You do not have permission to manage channels.',
            ],

            'user' => [
                'not-found' => 'Admin user not found.',
                'created' => 'Admin user created successfully.',
                'updated' => 'Admin user updated successfully.',
                'deleted' => 'Admin user deleted successfully.',
                'name-required' => 'The name field is required.',
                'email-required' => 'The email field is required.',
                'email-invalid' => 'The email must be a valid email address.',
                'email-unique' => 'The email has already been taken.',
                'password-required' => 'The password field is required.',
                'password-min' => 'The password must be at least 6 characters.',
                'role-required' => 'The role_id field is required.',
                'role-not-found' => 'The selected role does not exist.',
                'status-invalid' => 'The status must be 0 or 1.',
                'cannot-delete-self' => 'You cannot delete your own admin account.',
                'cannot-delete-last-admin' => 'At least one admin user is required — cannot delete the last remaining admin.',
                'delete-failed' => 'Admin user could not be deleted.',
                'no-permission' => 'You do not have permission to manage admin users.',
                'self-delete-password-required' => 'The password field is required to delete your own account.',
                'self-delete-incorrect-password' => 'The password is incorrect.',
                'self-deleted' => 'Your admin account has been deleted.',
            ],

            'data-transfer' => [
                'import' => [
                    'not-found' => 'Import not found.',
                    'deleted' => 'Import deleted successfully.',
                    'cancelled' => 'Import cancelled successfully.',
                    'cannot-cancel' => 'Only pending or processing imports can be cancelled. Current state: :state.',
                    'no-permission' => 'You do not have permission to manage data-transfer imports.',
                    'delete-failed' => 'Import could not be deleted.',
                    'created' => 'Import created successfully.',
                    'updated' => 'Import updated successfully.',
                    'type-invalid' => 'The selected type is invalid.',
                    'action-invalid' => 'The action must be one of: append, delete.',
                    'validation-strategy-invalid' => 'The validation strategy must be one of: stop-on-errors, skip-errors.',
                    'allowed-errors-invalid' => 'The allowed errors must be an integer of 0 or more.',
                    'field-separator-required' => 'The field separator is required.',
                    'file-required' => 'A file is required.',
                    'file-invalid-type' => 'The file must be one of: csv, xls, xlsx, xml.',
                    'nothing-to-import' => 'There is nothing to import.',
                    'not-valid' => 'The import is not valid.',
                    'setup-queue-error' => 'The queue connection is set to sync; configure a real queue to process imports in the background.',
                    'file-not-found' => 'No file is available for download.',
                    'error-report-not-found' => 'No error report is available for download.',
                    'sample-not-found' => 'No sample file is available for the requested type and format.',
                    'sample-format-invalid' => 'The sample format must be one of: csv, xls, xlsx, xml.',
                ],
            ],
        ],

        'cms' => [
            'page' => [
                // Read
                'not-found' => 'CMS page not found.',

                // Create
                'created' => 'CMS page created successfully.',
                'url-key-required' => 'The url_key field is required.',
                'url-key-unique' => 'The url_key has already been taken.',
                'url-key-invalid' => 'The url_key format is invalid. Use lowercase letters, numbers and hyphens only.',
                'page-title-required' => 'The page_title field is required.',
                'html-content-required' => 'The html_content field is required.',
                'channels-required' => 'At least one channel must be selected.',
                'channels-invalid' => 'One or more channels are invalid.',

                // Update
                'updated' => 'CMS page updated successfully.',

                // Delete
                'deleted' => 'CMS page deleted successfully.',
                'delete-failed' => 'CMS page could not be deleted.',

                // Mass delete
                'mass-deleted' => 'CMS pages deleted successfully.',
                'indices-required' => 'The indices field is required and must be a non-empty array.',
                'indices-invalid' => 'The indices field must be an array of CMS page IDs.',

                // Common
                'no-permission' => 'You do not have permission to manage CMS pages.',
            ],
        ],

        'marketing' => [
            'catalog-rule' => [
                'not-found' => 'Catalog rule not found.',
                'created' => 'Catalog rule created successfully.',
                'updated' => 'Catalog rule updated successfully.',
                'deleted' => 'Catalog rule deleted successfully.',
                'delete-failed' => 'Catalog rule could not be deleted.',
                'mass-delete-indices-required' => 'No catalog rule IDs supplied for mass-delete.',
                'mass-delete-success' => 'Selected catalog rules deleted successfully.',
                'no-permission' => 'You do not have permission to manage catalog rules.',
            ],

            'campaign' => [
                'not-found' => 'Campaign not found.',
                'created' => 'Campaign created successfully.',
                'updated' => 'Campaign updated successfully.',
                'deleted' => 'Campaign deleted successfully.',
                'delete-failed' => 'Campaign could not be deleted.',
                'no-permission' => 'You do not have permission to manage campaigns.',
                'send' => [
                    'id-required' => 'Campaign id is required.',
                    'inactive' => 'Cannot send an inactive campaign. Enable it first.',
                    'queued' => 'Campaign queued for :count recipient(s).',
                ],
            ],

            'sitemap' => [
                'not-found' => 'Sitemap not found.',
                'created' => 'Sitemap created successfully.',
                'updated' => 'Sitemap updated successfully.',
                'deleted' => 'Sitemap deleted successfully.',
                'delete-failed' => 'Sitemap could not be deleted.',
                'no-permission' => 'You do not have permission to manage sitemaps.',
                'generate' => [
                    'id-required' => 'Sitemap id is required.',
                    'success' => 'Sitemap regenerated successfully.',
                    'failed' => 'Sitemap generation failed: :message',
                ],
            ],

            'cart-rule' => [
                'not-found' => 'Cart rule not found.',
                'id-required' => 'A cart rule id is required.',
                'copy-of' => 'Copy of :value',
                'copied' => 'Cart rule copied successfully.',
                'created' => 'Cart rule created successfully.',
                'updated' => 'Cart rule updated successfully.',
                'deleted' => 'Cart rule deleted successfully.',
                'delete-failed' => 'Cart rule could not be deleted.',
                'no-permission' => 'You do not have permission to manage cart rules.',
                'mass-delete-success' => 'Cart rules deleted successfully.',
                'mass-delete-indices-required' => 'The indices field is required and must be a non-empty array.',
                'action-type-invalid' => 'The action_type must be one of: by_percent, by_fixed, cart_fixed, buy_x_get_y.',
                'coupon-code-required' => 'A coupon_code is required when coupon_type=1 and use_auto_generation=0.',
                'coupon-code-unique' => 'This coupon code is already in use.',
            ],

            // Marketing → Email Templates
            'template' => [
                'not-found' => 'Email template not found.',
                'created' => 'Email template created successfully.',
                'updated' => 'Email template updated successfully.',
                'deleted' => 'Email template deleted successfully.',
                'delete-failed' => 'Email template could not be deleted.',
                'status-invalid' => 'The status must be one of: active, inactive, draft.',
                'no-permission' => 'You do not have permission to manage email templates.',
            ],

            // Marketing → Events
            'event' => [
                'not-found' => 'Marketing event not found.',
                'created' => 'Marketing event created successfully.',
                'updated' => 'Marketing event updated successfully.',
                'deleted' => 'Marketing event deleted successfully.',
                'delete-failed' => 'Marketing event could not be deleted.',
                'no-permission' => 'You do not have permission to manage marketing events.',
            ],

            // Marketing → Search Synonyms
            'search-synonym' => [
                'not-found' => 'Search synonym not found.',
                'created' => 'Search synonym created successfully.',
                'updated' => 'Search synonym updated successfully.',
                'deleted' => 'Search synonym deleted successfully.',
                'mass-deleted' => 'Search synonyms deleted successfully.',
                'delete-failed' => 'Search synonym could not be deleted.',
                'indices-required' => 'The indices field is required and must be a non-empty array of search synonym IDs.',
                'no-permission' => 'You do not have permission to manage search synonyms.',
            ],

            // Marketing → URL Rewrites
            'url-rewrite' => [
                'not-found' => 'URL rewrite not found.',
                'created' => 'URL rewrite created successfully.',
                'updated' => 'URL rewrite updated successfully.',
                'deleted' => 'URL rewrite deleted successfully.',
                'delete-failed' => 'URL rewrite could not be deleted.',
                'mass-deleted' => 'URL rewrites deleted successfully.',
                'indices-required' => 'At least one URL rewrite id is required.',
                'no-permission' => 'You do not have permission to manage URL rewrites.',
            ],

            // Cart Rule Coupons (sub-resource of Cart Rules)
            'cart-rule-coupon' => [
                'cart-rule-not-found' => 'Cart rule not found.',
                'not-found' => 'Coupon not found.',
                'code-taken' => 'A coupon with this code already exists.',
                'format-invalid' => 'Format must be one of: alphabetic, alphanumeric, numeric.',
                'indices-required' => 'The indices field is required and must be a non-empty array of coupon IDs.',
                'created' => 'Coupon created successfully.',
                'generated' => 'Generated :count coupon(s) successfully.',
                'deleted' => 'Coupon deleted successfully.',
                'mass-deleted' => 'Deleted :count coupon(s) successfully.',
                'no-permission' => 'You do not have permission to manage cart rule coupons.',
            ],

            // Newsletter Subscribers
            'subscriber' => [
                'not-found' => 'Newsletter subscriber not found.',
                'updated' => 'Subscription updated successfully.',
                'deleted' => 'Subscription deleted successfully.',
                'is-subscribed-required' => 'The is_subscribed field is required.',
                'no-permission' => 'You do not have permission to manage newsletter subscribers.',
            ],

            // Search Terms
            'search-term' => [
                'not-found' => 'Search term not found.',
                'updated' => 'Search term updated successfully.',
                'deleted' => 'Search term deleted successfully.',
                'mass-deleted' => 'Search terms deleted successfully.',
                'term-required' => 'The term field is required.',
                'redirect-url-invalid' => 'The redirect_url must be a valid URL.',
                'indices-required' => 'At least one ID must be provided in indices.',
                'no-permission' => 'You do not have permission to manage search terms.',
            ],
        ],
    ],

    'integration' => [
        'menu' => [
            'title' => 'Integration',
            'tokens' => 'Tokens',
        ],

        'history' => [
            'menu' => [
                'title' => 'History',
            ],

            'acl' => [
                'title' => 'API Change History',
                'delete' => 'Delete History',
            ],

            'index' => [
                'title' => 'API Change History',
                'info' => 'Every create, update and delete made through the admin API, with who did it, which token, and what changed.',
                'cleanup-btn' => 'Delete older logs',
                'cleanup-days' => 'Delete logs older than this many days',
                'cleanup-confirm' => 'Delete all history older than the given number of days? This cannot be undone.',
            ],

            'view' => [
                'title' => 'Change',
                'back-btn' => 'Back',
                'admin' => 'Admin',
                'token' => 'Token',
                'action' => 'Action',
                'resource' => 'Resource',
                'method' => 'Method',
                'ip' => 'IP Address',
                'date' => 'Date',
                'version' => 'Version',
                'url' => 'Endpoint',
                'request-details' => 'Request Details',
                'changes' => 'Changes',
                'field' => 'Field',
                'old' => 'Old value',
                'new' => 'New value',
                'no-field-changes' => 'No field-level changes were recorded for this entry.',
                'same-request' => 'Other changes in the same request',
                'version-chain' => 'Version history of this record',
            ],

            'datagrid' => [
                'id' => 'ID',
                'date' => 'Date',
                'admin' => 'Admin',
                'token' => 'Token',
                'action' => 'Action',
                'operation' => 'Operation',
                'resource' => 'Resource',
                'version' => 'Version',
                'method' => 'Method',
                'ip' => 'IP',
                'view' => 'View',
                'delete' => 'Delete',
            ],

            'events' => [
                'created' => 'Created',
                'updated' => 'Updated',
                'deleted' => 'Deleted',
            ],

            'deleted' => ':count history record(s) deleted.',
            'cleanup-input-required' => 'Provide a number of days or a date to clean up.',
        ],

        'acl' => [
            'title' => 'Integration',
            'create' => 'Create Integration',
            'edit' => 'Edit Integration',
            'delete' => 'Revoke Integration Token',
            'generate' => 'Generate Integration Token',
            'regenerate' => 'Regenerate Integration Token',
        ],

        'index' => [
            'title' => 'Integrations',
            'create-btn' => 'Create Integration',
        ],

        'create' => [
            'title' => 'Create Integration',
            'save-btn' => 'Save',
            'back-btn' => 'Back',
        ],

        'edit' => [
            'title' => 'Edit Integration',
            'save-btn' => 'Save',
            'back-btn' => 'Back',
            'generate-btn' => 'Generate Token',
            'regenerate-btn' => 'Regenerate Token',
            'revoke-btn' => 'Revoke Token',
            'copy-btn' => 'Copy',
            'token-warning' => 'Save this token now — it will not be shown again.',
            'token-label' => 'Token',
            'not-generated' => 'Not generated yet',
            'masked' => '(Stored — only shown once at generation)',
            'history-banner' => 'This token is no longer active.',
        ],

        'fields' => [
            'name' => 'Name',
            'description' => 'Description',
            'assign-user' => 'Assign User',
            'permission-type' => 'Permission Type',
            'access-control' => 'Access Control',
            'general' => 'General',
            'token-settings' => 'Token Settings',
            'valid-till' => 'Valid Till',
            'rate-limit-per-minute' => 'Rate Limit (per minute)',
            'rate-limit-per-day' => 'Rate Limit (per day)',
            'never-expires' => 'Never expires',
            'expires-on' => 'Expires on',
            'unlimited' => 'Unlimited',
            'limit-to' => 'Limit to',
            'requests-per-minute' => 'requests / minute',
            'requests-per-day' => 'requests / day',
            'select-admin' => 'Select an admin',
            'no-available-admins' => 'No admins available — every admin already has an active token.',
            'same-as-web-hint' => 'Token will mirror the assigned admin\'s current role permissions live.',
            'ip-allowlist' => 'IP Allowlist',
            'ip-any' => 'Any IP (default)',
            'ip-restricted' => 'Restricted to specific IPs',
            'ip-list-hint' => 'One entry per line. Supports IPv4, IPv6 and CIDR (e.g. 10.0.0.0/24 or 2001:db8::/32). Leave blank to allow all IPs.',
        ],

        'permission_type' => [
            'all' => 'All',
            'custom' => 'Custom',
            'same_as_web' => 'Same as Web Permission',
        ],

        'status' => [
            'draft' => 'Draft',
            'active' => 'Active',
            'revoked' => 'Revoked',
            'regenerated' => 'Regenerated',
        ],

        'datagrid' => [
            'id' => 'ID',
            'name' => 'Name',
            'admin' => 'Admin',
            'token' => 'Token',
            'status' => 'Status',
            'permission-type' => 'Permission Type',
            'expires-at' => 'Valid Till',
            'last-used-at' => 'Last Used',
            'created-at' => 'Created At',
            'edit' => 'Edit',
            'revoke' => 'Revoke',
        ],

        'messages' => [
            'draft-created' => 'Integration created. Generate the token to start using it.',
            'updated' => 'Integration updated successfully.',
            'generated' => 'Token generated. Copy it now — it will not be shown again.',
            'regenerated' => 'Token regenerated. Copy the new token now — it will not be shown again.',
            'revoked' => 'Token revoked successfully.',
            'generate-only-draft' => 'Only draft integrations can have their token generated.',
            'regenerate-only-active' => 'Only active tokens can be regenerated.',
            'cannot-edit-historic' => 'Revoked or regenerated tokens cannot be edited.',
            'already-inactive' => 'This token is already inactive.',
        ],

        'errors' => [
            'admin-has-token' => 'Selected admin already has an active integration token.',
        ],

        'validation' => [
            'ip-invalid' => 'Each allowed IP must be a valid IPv4 or IPv6 address (CIDR notation supported).',
            'cidr-prefix-invalid' => 'The CIDR prefix is invalid for the given IP version.',
        ],

        'configuration' => [
            'api' => [
                'title' => 'API',
                'info' => 'Settings for the Bagisto API and its admin modules.',
            ],
            'integration' => [
                'title' => 'Integration',
                'info' => 'Manage the API Integration plugin used to issue admin API tokens.',
            ],
            'settings' => [
                'title' => 'Module Settings',
                'info' => 'Enable or disable the API Integration plugin. When disabled, its sidebar menu is hidden and its pages return 404.',
                'enable' => 'Enable API Integration Module',
            ],
        ],

        'emails' => [
            'generated' => [
                'subject' => 'A new API token was generated: :name',
                'greeting' => 'An API integration token named ":name" was just generated on your account.',
            ],
            'regenerated' => [
                'subject' => 'Your API token was regenerated: :name',
                'greeting' => 'The API integration token named ":name" was just regenerated. The previous token has stopped working — only the new one is valid.',
            ],
            'revoked' => [
                'subject' => 'Your API token was revoked: :name',
                'greeting' => 'The API integration token named ":name" was revoked. Any client using it has lost access.',
            ],

            'details' => [
                'name' => 'Token Name',
                'date' => 'Date',
                'ip' => 'From IP',
            ],

            'revoke-hint' => 'If you did not expect this, revoke the token immediately using the button below.',
            'revoke-btn' => 'Revoke This Token',
            'revoke-expiry' => 'This revoke link is valid for 7 days. After that, sign in to the admin panel to manage the token.',
            'no-action' => 'No action is needed — this email is only a confirmation.',
        ],

        'revoke-confirmation' => [
            'title' => 'Revoke API Token',
            'success-title' => 'Token Revoked',
            'success-message' => 'The token ":name" has been revoked. Any client using it has lost access immediately.',
            'already-inactive-title' => 'Token Already Inactive',
            'already-inactive-message' => 'The token ":name" was already revoked or regenerated. No further action is needed.',
        ],

        'confirm' => [
            'generate' => [
                'title' => 'Generate Token',
                'message' => 'Generate the token now? The plaintext will be shown only once — copy it before leaving the page.',
            ],
            'regenerate' => [
                'title' => 'Regenerate Token',
                'message' => 'Regenerate the token? The old token will stop working immediately and the new plaintext will be shown only once.',
            ],
            'revoke' => [
                'title' => 'Revoke Token',
                'message' => 'Revoke this token? Any client using it will lose access immediately. This action cannot be undone.',
            ],
        ],
    ],
];
