<?php

namespace Webkul\BagistoApi\Admin\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;
use Webkul\Admin\Mail\Mailable;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;

/**
 * Lifecycle notification for an admin integration token.
 *
 * Sent to the token owner whenever their token is generated, regenerated, or
 * revoked. The email NEVER carries the plaintext token — the plaintext is shown
 * only once in the browser at generation time. For generate/regenerate events
 * the email includes a signed, time-limited one-click revoke link so the owner
 * can immediately kill a token they did not expect.
 */
class AdminTokenNotification extends Mailable
{
    public const EVENT_GENERATED = 'generated';

    public const EVENT_REGENERATED = 'regenerated';

    public const EVENT_REVOKED = 'revoked';

    /**
     * Signed, 7-day revoke URL. Empty for the "revoked" event.
     */
    public string $revokeUrl = '';

    /**
     * Create a new mailable instance.
     */
    public function __construct(
        public AdminPersonalAccessToken $token,
        public string $event,
        public ?string $ipAddress = null,
    ) {
        if (in_array($event, [self::EVENT_GENERATED, self::EVENT_REGENERATED], true)) {
            $this->revokeUrl = URL::temporarySignedRoute(
                'admin.integration.revoke-via-email',
                now()->addDays(7),
                ['id' => $token->id]
            );
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address($this->token->admin->email, $this->token->admin->name),
            ],
            subject: trans('bagistoapi::app.integration.emails.'.$this->event.'.subject', [
                'name' => $this->token->name,
            ]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'bagistoapi::emails.token-notification',
        );
    }
}
