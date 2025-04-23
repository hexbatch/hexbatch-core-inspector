<?php

namespace App\OpenApi\User\Password;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use OpenApi\Attributes as OA;

/**
 * Returns the status of sending out the pw email
 */
#[OA\Schema(schema: 'PasswordResetResponse',title: "Response to password reset")]
class PasswordResetResponse
{


    #[OA\Property( title: 'Password reset status', description: 'one of sent|throttled|invalid|unknown', type: 'string', example: 'sent')]
    public string $password_reset_status;


    public function __construct(string $raw_status)
    {
        $this->password_reset_status = match ($raw_status) {
            Password::RESET_LINK_SENT => 'sent',
            Password::RESET_THROTTLED => 'throttled',
            Password::INVALID_USER => 'invalid',
            default => 'unknown'
        };

        if ($this->password_reset_status === 'unknown') {
            Log::warning("password reset sent a status not understood: ". $raw_status);
        }

    }


}
