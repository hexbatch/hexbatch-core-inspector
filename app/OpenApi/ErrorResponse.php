<?php

namespace App\OpenApi;


use OpenApi\Attributes as OA;

/**
 * All errors will have this format
 */
#[OA\Schema(schema: 'ErrorResponse',title: "Error",example: ["status"=>401,"message"=>"Logged in user is not assigned to the project"])]

class ErrorResponse
{
    #[OA\Property(  title: 'The status of the error ',description: 'This is normally a http code', example: 400)]
    protected int $status;

    #[OA\Property(  title: 'The error message ',description: 'Describes the problem', example: 'You have not been assigned to this project')]
    protected string $message;

}
