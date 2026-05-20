<?php

return [
    'login' => [
        'credentials-required' => 'Email and password are required.',
        'invalid-credentials'  => 'Please check your credentials and try again.',
        'account-inactive'     => 'Your account is yet to be activated, please contact administrator.',
        'successful'           => 'Logged in successfully.',
    ],

    'logout' => [
        'unauthenticated' => 'You are not authenticated.',
        'token-not-found' => 'Token not found or already expired.',
        'success'         => 'Logged out successfully.',
        'all-success'     => 'Logged out from all devices successfully.',
    ],

    'profile' => [
        'unauthenticated'            => 'You are not authenticated.',
        'updated'                    => 'Account updated successfully.',
        'current-password-incorrect' => 'The current password you entered is incorrect.',
        'password-mismatch'          => 'The password and confirm password do not match.',
        'email-taken'                => 'This email address is already in use.',
    ],

    'order' => [
        'not-found' => 'Order not found.',
    ],

    'forgot-password' => [
        'email-required'  => 'Email is required.',
        'reset-link-sent' => 'A password reset link has been sent to your email.',
        'email-not-found' => 'We could not find an admin with that email address.',
        'error'           => 'Something went wrong while sending the reset link.',
    ],
];
