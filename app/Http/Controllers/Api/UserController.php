<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HexbatchTextException;
use App\Helpers\Utilities;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\OpenApi\ErrorResponse;
use App\OpenApi\User\Login\LoginParams;
use App\OpenApi\User\Login\LoginResponse;
use App\OpenApi\User\UserResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\Response as CodeOf;

class UserController extends Controller
{

    #[OA\Post(
        path: '/api/v1/users/login',
        operationId: 'api.users.login',
        description: 'This returns a token to use as basic auth in the other api calls',
        summary: 'Logs the user in',
        requestBody: new OA\RequestBody( required: true,content: new JsonContent(type: LoginParams::class) ),
        tags: ['user'],
        responses: [
            new OA\Response( response: CodeOf::HTTP_OK, description: 'Login returns a token',content: new JsonContent(ref: LoginResponse::class)),

            new OA\Response( response: CodeOf::HTTP_UNPROCESSABLE_ENTITY, description: 'Bad credentials',
                content: new JsonContent(ref: ErrorResponse::class, example: ["status"=>422,"message"=>"These credentials do not match our records"] ))
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {

        try {
            $request->authenticate();

            $user = Utilities::get_logged_user();

            $user->tokens()->delete(); //remove all the other tokens

            $token = $user->createToken($request->username)->plainTextToken;


            return response()->json(new LoginResponse(auth_token: $token), CodeOf::HTTP_OK);
        }
        catch (ValidationException $e) {
            throw new HexbatchTextException($e->getMessage(),$e->status,$e);
        }
        catch (\Exception $e) {
            $out_code = $e->getCode();
            if (!$e->getCode() || !ctype_digit($e->getCode())) { $out_code = CodeOf::HTTP_INTERNAL_SERVER_ERROR;}
            throw new HexbatchTextException($e->getMessage(),$out_code,$e);
        }
    }


    #[OA\Get(
        path: '/api/v1/users/me',
        operationId: 'api.users.me',
        description: 'This returns user data about the person this authentication token is for',
        summary: 'Returns logged-in user information',
        security: [['bearerAuth' => []]],
        tags: ['user'],
        responses: [
            new OA\Response( response: CodeOf::HTTP_OK, description: 'Gives information about the logged in user',content: new JsonContent(ref: UserResponse::class)),

            new OA\Response( response: CodeOf::HTTP_BAD_REQUEST, description: 'When not logged in',
                content: new JsonContent(ref: ErrorResponse::class, example: ["status"=>400,"message"=>"Unauthenticated."]))
        ]
    )]
    public function me() : JsonResponse
    {


        try {
            $user = Utilities::get_logged_user();

            return response()->json(new UserResponse(user: $user) , CodeOf::HTTP_OK);
        }  catch (\Exception $e) {
            $out_code = $e->getCode();
            if (!$e->getCode() || !ctype_digit($e->getCode())) { $out_code = CodeOf::HTTP_INTERNAL_SERVER_ERROR;}
            throw new HexbatchTextException($e->getMessage(),$out_code,$e);
        }
    }

}
