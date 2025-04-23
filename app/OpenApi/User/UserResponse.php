<?php

namespace App\OpenApi\User;

use App\Models\User;

use Carbon\Carbon;
use OpenApi\Attributes as OA;

/**
 * This can describe a user or employee or someone who is both
 */
#[OA\Schema(schema: 'UserResponse',title: "User")]
class UserResponse
{

    #[OA\Property( title: 'User Id',example: 58)]
    public ?int $user_id = null;


    #[OA\Property( title: 'Name',example: "Will")]
    public ?string $name = null;

    #[OA\Property( title: 'Username',example: "Will")]
    public ?string $username = null;


    #[OA\Property( title: 'Email', format: 'email',example: "willtornweed@yarn.org")]
    public ?string $email = null;

    #[OA\Property( title: 'Tags')]
    public array $user_tags ;

    #[OA\Property( title: 'Registration Date', format: 'date-time',example: "2015-08-12T00:00:00-05:00")]
    public ?string $registration_date = null;

    public function __construct(User $user)
    {
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username;
        $this->user_tags = $user->getTags();
        $this->registration_date = Carbon::parse($user->created_at)->timezone(config('app.timezone'))->toIso8601String();

    }


}
