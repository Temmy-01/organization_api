<?php

namespace App\Http\Controllers\API\V1\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\User\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class LoginController extends Controller
{

    /**
     * @OA\Info(
     *      version="1.0.0",
     *      x={
     *          "logo": {
     *              "url": "https://via.placeholder.com/190x90.png?text=L5-Swagger"
     *          }
     *      },
     *      title="Organisation_Rest_api",
     *      description="Organisation",
     *      @OA\Contact(
     *          email="dev@organisation.net"
     *      ),
     *     @OA\License(
     *         name="Apache 2.0",
     *         url="https://www.apache.org/licenses/LICENSE-2.0.html"
     *     )
     * )
     */

    /**
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     */


    /**
     * Login existing users to the application.
     *
     * @param RegisterRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */


    /**
     * @OA\Post(
     *     path="/api/v1/user/auth/login",
     *     summary="Login a user and create an access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="admin@boilerplate.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="remember_me", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login with redirection if 2FA is enabled",
     *         @OA\JsonContent(
     *             @OA\Property(property="accessToken", type="string", example="11|nwmC2LXUlgVOrR67GsJfLJHJXBmgTRo9whmB3QPX7b236605"),
     *             @OA\Property(property="userAbilityRules", type="array", @OA\Items(type="string", example={"view_dashboard", "edit_profile"})),
     *             @OA\Property(property="userData", type="object", @OA\Property(property="id", type="integer", example=1), @OA\Property(property="fname", type="string", example="John"), @OA\Property(property="lname", type="string", example="Doe")),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid email or password",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", @OA\Property(property="email", type="array", @OA\Items(type="string", example="Invalid email or password")))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (e.g., incorrect OTP or email verification required)",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", @OA\Property(property="token", type="array", @OA\Items(type="string", example="Invalid Token Supplied")))
     *         )
     *     )
     * )
     */
    
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
            return ResponseBuilder::asError(Response::HTTP_UNAUTHORIZED)
            ->withMessage('Invalid login details')
            ->build();
        }

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken($request->ip())->plainTextToken;
        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_OK)
            ->withMessage('User login was successful.')
            ->withData([
                'user' => $user,
                'token' => $token
            ])
            ->build();
    }

    /**
     * Log user out from current device.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */

    /**
     * @OA\Post(
     *     path="/api/v1/user/auth/logout",
     *     summary="Logout a user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout was successful.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No token provided or invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseBuilder::asSuccess()
            ->withMessage('Logout was successful.')
            ->build();
    }
}
