<?php

namespace App\Http\Controllers\API\V1\User;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\Repository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


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

class RepositoryController extends Controller
{

    /**
     * Create a new repository.
     *
     * @OA\Post(
     *     path="/api/v1/user/reposistory/add_repository",
     *     summary="Create a new repository",
     *     tags={"Repositories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "no_of_stars"},
     *             @OA\Property(property="name", type="string", example="New Repo"),
     *             @OA\Property(property="description", type="string", example="A description for the repository."),
     *             @OA\Property(property="url", type="string", format="url", example="https://example.com"),
     *             @OA\Property(property="repo_code", type="string", example="ABC123XYZ"),
     *             @OA\Property(property="no_of_stars", type="string", example="50"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Repository created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Repository created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create repository",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create repository"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */



    public function createRepository(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:repositories,name',
            'description' => 'required|string',
            'url' => 'nullable|url',
            'repo_code' => 'nullable|url',
            'no_of_stars'=>'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Generate a random repo code if not provided
            $repoCode = $request->repo_code ?? strtoupper(Str::random(10));

            $repository = Repository::create([
                'name' => $request->name,
                'description' => $request->description,
                'url' => $request->url,
                'no_of_stars' => $request->no_of_stars,
                'repo_code' => $repoCode,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Repository created successfully',
                'data' => $repository
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create repository',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/user/repository/fetch",
     *      operationId="fetchRepositories",
     *      tags={"Repositories"},
     *      summary="Fetch all repositories",
     *      description="Retrieve a list of all repositories stored in the database.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="MyRepo"),
     *                      @OA\Property(property="description", type="string", example="This is a sample repository"),
     *                      @OA\Property(property="url", type="string", example="https://github.com/user/repo"),
     *                      @OA\Property(property="repo_code", type="string", example="ABC123"),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Failed to fetch repositories."),
     *              @OA\Property(property="error", type="string", example="Exception message")
     *          )
     *      )
     * )
     */

   
    public function fetchRepositories(Request $request)
    {
        $perPage = $request->per_page ?? 2;
        $search = $request->filter;

        try {
            $query = Repository::query();

            // Apply search filter if provided
            if (!empty($search)) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            }

            // Paginate results
            $repositories = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $repositories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch repositories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update a repository.
     *
     * @OA\Put(
     *     path="/api/v1/user/reposistory/update/{id}",
     *     summary="Update a repository",
     *     tags={"Repositories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Repository ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "no_of_stars"},
     *             @OA\Property(property="name", type="string", example="Updated Repo"),
     *             @OA\Property(property="description", type="string", example="Updated repository description."),
     *             @OA\Property(property="url", type="string", format="url", example="https://example.com"),
     *             @OA\Property(property="repo_code", type="string", example="ABC123XYZ"),
     *             @OA\Property(property="no_of_stars", type="string", example="100"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Repository updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Repository updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Repository not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Repository not found")
     *         )
     *     )
     * )
     */


    public function updateRepository(Request $request, $id)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:repositories,name,' . $id,
            'description' => 'required|string',
            'url' => 'nullable|url',
            'repo_code' => 'nullable|string',
            'no_of_stars' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Find the repository
            $repository = Repository::findOrFail($id);

            // Update the repository
            $repository->update([
                'name' => $request->name,
                'description' => $request->description,
                'url' => $request->url,
                'repo_code' => $request->repo_code ?? strtoupper(Str::random(10)),
                'no_of_stars' => $request->no_of_stars,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Repository updated successfully',
                'data' => $repository
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update repository',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   

    public function deleteRepository(Request $request, $repo_id)
    {
        DB::beginTransaction();

        try {
            // Find the Base user by id
            $repository = Repository::where('id', $repo_id)->first();
            if ($repository) {
                $repository->forceDelete();
            } else {
                return ResponseBuilder::asError(400)
                    ->withHttpCode(Response::HTTP_NOT_FOUND)
                    ->withMessage('Repository not found')
                    ->build();
            }

            DB::commit();

            return ResponseBuilder::asSuccess()
                ->withHttpCode(Response::HTTP_OK)
                ->withMessage('Repository and related records removed successfully')
                ->build();
        } catch (\Exception $e) {
            DB::rollBack();

            return ResponseBuilder::asError(500)
                ->withMessage('An error occurred while removing user and related records')
                ->build();
        }
    }

}
