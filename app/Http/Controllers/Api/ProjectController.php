<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Repositories\ProjectRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    protected ProjectRepositoryInterface $projectRepository;

    /**
     * Constructor with dependency injection
     */
    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * GET /projects - list authenticated user's projects
     */
    public function index(): AnonymousResourceCollection
    {
        $projects = $this->projectRepository->getAllForUser(Auth::id());

        return ProjectResource::collection($projects);
    }

    /**
     * POST /projects - create new project
     */
    public function store(StoreProjectRequest $request): ProjectResource
    {
        // Check authorization using policy
        $this->authorize('create', Project::class);

        // Get validated data and create project
        $project = $this->projectRepository->create(
            $request->validated(), // This only gets validated data, not authorization
            Auth::id()
        );

        return new ProjectResource($project);
    }

    /**
     * GET /projects/{id} - show a single project
     */
    public function show(Project $project): ProjectResource
    {
        // Check authorization using policy
        $this->authorize('view', $project);

        $project = $this->projectRepository->loadRelationships($project);
        return new ProjectResource($project);
    }

    /**
     * PUT /projects/{id} - update a project
     */
    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        // Check authorization using policy
        $this->authorize('update', $project);

        // Update with validated data
        $project = $this->projectRepository->update($project, $request->validated());
        return new ProjectResource($project);
    }

    /**
     * DELETE /projects/{id} - delete a project
     */
    public function destroy(Project $project): JsonResponse
    {
        // Check authorization using policy
        $this->authorize('delete', $project);

        $this->projectRepository->delete($project);
        return response()->json(['message' => __('errors.project_deleted')]);
    }
}
