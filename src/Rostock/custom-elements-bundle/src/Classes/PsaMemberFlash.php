<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Symfony\Component\HttpFoundation\RequestStack;

final class PsaMemberFlash
{
    public const SESSION_KEY = 'psa_member_flash';

    public const TYPE_REGISTRATION_PENDING = 'registration_pending';

    public const TYPE_ACCOUNT_ACTIVATED = 'account_activated';

    public const TYPE_PASSWORD_RESET_SENT = 'password_reset_sent';

    public const TYPE_PASSWORD_CHANGED = 'password_changed';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function set(string $type): void
    {
        $session = $this->requestStack->getSession();
        $session->set(self::SESSION_KEY, $type);
    }

    public function consume(): ?array
    {
        $session = $this->requestStack->getSession();

        if (!$session->has(self::SESSION_KEY)) {
            return null;
        }

        $type = (string) $session->get(self::SESSION_KEY);
        $session->remove(self::SESSION_KEY);

        $message = $GLOBALS['TL_LANG']['PSA']['member_flash'][$type] ?? '';

        if ($message === '') {
            return null;
        }

        return [
            'type' => str_contains($type, 'error') ? 'error' : 'success',
            'message' => $message,
        ];
    }
}
