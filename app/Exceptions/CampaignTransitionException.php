<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a campaign cannot move to the requested state.
 *
 * Messages are written for the person using the app, so they are safe to show.
 */
class CampaignTransitionException extends RuntimeException
{
    public static function notAllowed(string $action): self
    {
        return new self("This campaign cannot be {$action} from its current status.");
    }

    public static function invalid(string $reason): self
    {
        return new self($reason);
    }
}
