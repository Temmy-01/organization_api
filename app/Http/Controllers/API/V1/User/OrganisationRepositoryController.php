<?php

namespace App\Http\Controllers\API\V1\User;

use App\Http\Controllers\Controller;
use App\Enums\MediaCollection;
use App\Models\Account;
use App\Models\OrganisationRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Repository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrganisationRepositoryController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/user/org_repository/add_repository",
     *     summary="Create a new GitHub repository",
     *     description="Creates a new repository on GitHub (either personal or organization) and stores the details in the database.",
     *     operationId="createOrgRepository",
     *     tags={"Repositories"},
     *     security={{"sanctum": {}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "visibility"},
     *             @OA\Property(property="name", type="string", example="my-new-repo"),
     *             @OA\Property(property="description", type="string", example="A sample repository"),
     *             @OA\Property(property="visibility", type="string", enum={"public", "private"}, example="public"),
     *             @OA\Property(property="org", type="string", nullable=true, example="my-organization"),
     *             @OA\Property(property="language", type="string", example="PHP"),
     *             @OA\Property(property="license", type="string", example="MIT"),
     *             @OA\Property(property="forks_count", type="integer", example=10),
     *             @OA\Property(property="open_issues_count", type="integer", example=5),
     *             @OA\Property(property="watchers_count", type="integer", example=20),
     *             @OA\Property(property="default_branch", type="string", example="main"),
     *             @OA\Property(property="topics", type="array", @OA\Items(type="string"), example={"laravel", "vue", "devops"})
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=201,
     *         description="Repository created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Repository created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Permission denied")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Organization not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Organization not found")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create repository"),
     *             @OA\Property(property="error", type="string", example="Exception details")
     *         )
     *     )
     * )
     */


    // public function createOrgRepository(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'github_repo_url' => 'required|url',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Invalid input',
    //             'errors' => $validator->errors()
    //         ], 400);
    //     }

    //     try {
    //         // Extract repo name from URL
    //         $repoPath = str_replace("https://github.com/", "", $request->github_repo_url);

    //         // Make request to GitHub API
    //         $response = Http::withToken(env('GITHUB_TOKEN'))
    //             ->get("https://api.github.com/repos/{$repoPath}");

    //         if ($response->failed()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'GitHub repository not found'
    //             ], 404);
    //         }

    //         $data = $response->json();
    //         $topics = !empty($data['topics']) ? $data['topics'] : ($request->topics ?? []);
    //         // Save to database
    // $repository = OrganisationRepository::create([
    //     'name' => $data['name'],
    //     'description' => $data['description'] ?? $request->description,
    //     'url' => $data['html_url'] ?? $request->github_repo_url,
    //     'visibility' => $data['visibility'] ?? $request->visibility,
    //     'language' => $data['language'] ?? $request->language,
    //     'license' => $data['license']['name'] ?? $request->license,
    //     'forks_count' => ($data['forks_count'] == 0) ? ($request->forks_count ?? 0) : $data['forks_count'],
    //     'open_issues_count' => ($data['open_issues_count'] == 0) ? ($request->open_issues_count ?? 0) : $data['open_issues_count'],
    //     'watchers_count' => ($data['watchers_count'] == 0) ? ($request->watchers_count ?? 0) : $data['watchers_count'],
    //     'default_branch' => $data['default_branch'] ?? $request->default_branch,
    //     // 'topics' => json_encode($data['topics'] ?? $request->topics),
    //     'topics' => json_encode($topics),
    // ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Repository created successfully',
    //             'data' => $repository
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to create repository',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function createOrgRepository(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'visibility' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // $githubToken = env('GITHUB_TOKEN');
            $githubToken = config('services.github.token');

            if (!$githubToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GitHub token not found in .env'
                ], 500);
            }

            // Determine API URL (for personal or organization repository)
            $url = $request->org
                ? "https://api.github.com/orgs/{$request->org}/repos"
                : "https://api.github.com/user/repos";

            // Make request to GitHub API
            $response = Http::withToken($githubToken)->post($url, [
                'name' => $request->name,
                'description' => $request->description,
                'visibility' => $request->visibility, // true for private repo, false for public
            ]);

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GitHub repository creation failed',
                    'error' => $response->json()
                ], $response->status());
            }

            $data = $response->json();

            // Save to database
            $repository = OrganisationRepository::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? $request->description,
                'url' => $data['html_url'] ?? $request->github_repo_url,
                'visibility' => $data['visibility'] ?? $request->visibility,
                'language' => $data['language'] ?? $request->language,
                'license' => $data['license']['name'] ?? $request->license,
                'forks_count' => ($data['forks_count'] == 0) ? ($request->forks_count ?? 0) : $data['forks_count'],
                'open_issues_count' => ($data['open_issues_count'] == 0) ? ($request->open_issues_count ?? 0) : $data['open_issues_count'],
                'watchers_count' => ($data['watchers_count'] == 0) ? ($request->watchers_count ?? 0) : $data['watchers_count'],
                'default_branch' => $data['default_branch'] ?? $request->default_branch,

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
     *     path="/api/v1/user/org_repository/fetch",
     *     summary="Fetch organization repositories",
     *     description="Retrieves a paginated list of repositories, optionally filtered by name or description.",
     *     operationId="fetchOrganisationRepo",
     *     tags={"Repositories"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of repositories per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     * 
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Search query to filter repositories by name or description",
     *         required=false,
     *         @OA\Schema(type="string", example="laravel")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of repositories fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to fetch repositories."),
     *             @OA\Property(property="error", type="string", example="Exception details")
     *         )
     *     )
     * )
     */
    public function fetchOrganisationRepo(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->filter;

        try {
            $query = OrganisationRepository::query();

            if (!empty($search)) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            }

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
     * @OA\Put(
     *     path="/api/v1/user/org_repository/update/{id}",
     *     summary="Update an organization repository",
     *     description="Updates the details of an existing repository using GitHub API data and user-provided values.",
     *     operationId="updateOrgRepository",
     *     tags={"Repositories"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the repository to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_repo_url"},
     *             @OA\Property(property="github_repo_url", type="string", format="url", example="https://github.com/user/repo"),
     *             @OA\Property(property="description", type="string", example="Updated repository description"),
     *             @OA\Property(property="visibility", type="string", example="public"),
     *             @OA\Property(property="language", type="string", example="PHP"),
     *             @OA\Property(property="license", type="string", example="MIT"),
     *             @OA\Property(property="forks_count", type="integer", example=5),
     *             @OA\Property(property="open_issues_count", type="integer", example=2),
     *             @OA\Property(property="watchers_count", type="integer", example=8),
     *             @OA\Property(property="default_branch", type="string", example="main"),
     *             @OA\Property(property="topics", type="array", @OA\Items(type="string"), example={"laravel", "vue", "devops"})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Repository updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Repository updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="GitHub repository not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="GitHub repository not found"),
     *             @OA\Property(property="details", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to update repository"),
     *             @OA\Property(property="error", type="string", example="Exception details")
     *         )
     *     )
     * )
     */
    public function updateOrgRepository(Request $request, $id)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'github_repo_url' => 'required|url',
            'forks_count' => 'nullable|integer|min:0',
            'open_issues_count' => 'nullable|integer|min:0',
            'watchers_count' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Find the existing repository
            $repository = OrganisationRepository::findOrFail($id);

            // Extract repo name properly
            $repoPath = trim(parse_url($request->github_repo_url, PHP_URL_PATH), "/");
            $repoPath = str_replace(".git", "", $repoPath);

            // Fetch repository details from GitHub API
            $response = Http::withToken(env('GITHUB_TOKEN'))
                ->get("https://api.github.com/repos/{$repoPath}");

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GitHub repository not found',
                    'details' => $response->json()
                ], 404);
            }

            $data = $response->json();

            // Determine topics: use GitHub API topics if available, otherwise use request data
            $topics = !empty($data['topics']) ? $data['topics'] : ($request->topics ?? []);

            // Update repository in the database
            $repository->update([
                'name' => $data['name'] ?? $repository->name,
                'description' => $data['description'] ?? $request->description ?? $repository->description,
                'url' => $data['html_url'] ?? $request->github_repo_url ?? $repository->url,
                'visibility' => $data['visibility'] ?? $request->visibility ?? $repository->visibility,
                'language' => $data['language'] ?? $request->language ?? $repository->language,
                'license' => $data['license']['name'] ?? $request->license ?? $repository->license,
                'forks_count' => ($data['forks_count'] == 0) ? ($request->forks_count ?? $repository->forks_count) : $data['forks_count'],
                'open_issues_count' => ($data['open_issues_count'] == 0) ? ($request->open_issues_count ?? $repository->open_issues_count) : $data['open_issues_count'],
                'watchers_count' => ($data['watchers_count'] == 0) ? ($request->watchers_count ?? $repository->watchers_count) : $data['watchers_count'],
                'default_branch' => $data['default_branch'] ?? $request->default_branch ?? $repository->default_branch,
                'topics' => json_encode($topics),
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



    /**
     * @OA\Delete(
     *     path="/api/v1/user/org_repository/delete/{id}",
     *     summary="Delete an organization repository",
     *     description="Permanently deletes an organization repository from the database.",
     *     operationId="deleteRepository",
     *     tags={"Repositories"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the repository to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Repository deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Repository and related records removed successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Repository not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Repository not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while removing repository and related records")
     *         )
     *     )
     * )
     */
    public function deleteRepository(Request $request, $repo_id)
    {
        DB::beginTransaction();

        try {
            $repository = OrganisationRepository::where('id', $repo_id)->first();
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
