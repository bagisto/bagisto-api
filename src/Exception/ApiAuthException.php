<?php

namespace Webkul\BagistoApi\Exception;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class ApiAuthException extends \Exception implements ClientAware, HttpExceptionInterface, ProblemExceptionInterface, ProvidesExtensions
{
    /**
     * The machine-readable code of a request without valid credentials.
     */
    public const CODE_UNAUTHENTICATED = 'unauthenticated';

    /**
     * The machine-readable code of an access token past its expiry.
     */
    public const CODE_ACCESS_TOKEN_EXPIRED = 'access_token_expired';

    /**
     * The machine-readable code of a refresh token that cannot be exchanged.
     */
    public const CODE_REFRESH_TOKEN_INVALID = 'refresh_token_invalid';

    /**
     * The machine-readable code of an API token past its expiry.
     */
    public const CODE_API_TOKEN_EXPIRED = 'api_token_expired';

    /**
     * The machine-readable code of a revoked API token.
     */
    public const CODE_API_TOKEN_REVOKED = 'api_token_revoked';

    /**
     * The machine-readable code of an API token used from a domain it is not bound to.
     */
    public const CODE_API_TOKEN_DOMAIN_MISMATCH = 'api_token_domain_mismatch';

    /**
     * The machine-readable code of an action that needs a login session's access token.
     */
    public const CODE_ACCESS_TOKEN_REQUIRED = 'access_token_required';

    /**
     * The machine-readable code of wrong login credentials.
     */
    public const CODE_INVALID_CREDENTIALS = 'invalid_credentials';

    /**
     * The machine-readable code of a login by an inactive admin or customer.
     */
    public const CODE_ACCOUNT_INACTIVE = 'account_inactive';

    /**
     * The machine-readable code of a wrong two-factor or backup code.
     */
    public const CODE_INVALID_TWO_FACTOR_CODE = 'invalid_two_factor_code';

    /**
     * The machine-readable code of a two-factor challenge that is unknown, used, revoked or expired.
     */
    public const CODE_TWO_FACTOR_CHALLENGE_INVALID = 'two_factor_challenge_invalid';

    /**
     * The machine-readable code of a wrong current password on an account update.
     */
    public const CODE_INVALID_CURRENT_PASSWORD = 'invalid_current_password';

    /**
     * The machine-readable code of a password reset link or token that cannot be used.
     */
    public const CODE_PASSWORD_RESET_TOKEN_INVALID = 'password_reset_token_invalid';

    /**
     * The machine-readable code of too many forgot or reset password requests.
     */
    public const CODE_TOO_MANY_PASSWORD_REQUESTS = 'too_many_password_requests';

    /**
     * The machine-readable code of too many failed logins.
     */
    public const CODE_TOO_MANY_LOGIN_ATTEMPTS = 'too_many_login_attempts';

    /**
     * Create an authentication failure with its status, error code and response headers.
     */
    public function __construct(
        string $message,
        protected string $errorCode = self::CODE_UNAUTHENTICATED,
        protected int $status = 401,
        protected array $headers = [],
    ) {
        parent::__construct($message);
    }

    /**
     * The machine-readable error code returned to the client.
     */
    public function errorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * The REST response body: the message and the error code.
     */
    public function toResponseBody(): array
    {
        return [
            'message' => $this->getMessage(),
            'error' => $this->errorCode,
        ];
    }

    /**
     * Whether client safe.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Get category.
     */
    public function getCategory(): string
    {
        return 'authentication';
    }

    /**
     * The GraphQL error extensions: the error code and the HTTP status.
     */
    public function getExtensions(): ?array
    {
        $extensions = [
            'code' => $this->errorCode,
            'status' => $this->status,
        ];

        if (isset($this->headers['Retry-After'])) {
            $extensions['retryAfter'] = (int) $this->headers['Retry-After'];
        }

        return $extensions;
    }

    /**
     * Get status code.
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Get headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get type.
     */
    public function getType(): string
    {
        return '/errors/'.$this->status;
    }

    /**
     * Get title.
     */
    public function getTitle(): ?string
    {
        return match ($this->status) {
            403 => 'Forbidden',
            422 => 'Unprocessable Content',
            429 => 'Too Many Requests',
            default => 'Unauthorized',
        };
    }

    /**
     * Get status.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Get detail.
     */
    public function getDetail(): ?string
    {
        return $this->message;
    }

    /**
     * Get instance.
     */
    public function getInstance(): ?string
    {
        return null;
    }
}
