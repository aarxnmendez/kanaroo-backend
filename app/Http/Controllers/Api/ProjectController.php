<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Repositories\ProjectRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Projects\AddProjectMemberRequest;
use App\Http\Requests\Projects\UpdateProjectMemberRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

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
     * Display a listing of the user's projects.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);
        $projects = $this->projectRepository->getAllForUser(Auth::id());
        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created project.
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
     * Display the specified project.
     */
    public function show(Project $project): ProjectResource
    {
        // Check authorization using policy
        $this->authorize('view', $project);

        // Ensure all necessary relationships are loaded for the resource.
        $projectWithRelations = $this->projectRepository->loadRelationships($project);
        return new ProjectResource($projectWithRelations);
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        // Check authorization using policy
        $this->authorize('update', $project);

        // Update with validated data
        $updatedProject = $this->projectRepository->update($project, $request->validated());
        return new ProjectResource($updatedProject);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): Response
    {
        // Check authorization using policy
        $this->authorize('delete', $project);

        $this->projectRepository->delete($project);
        return response()->noContent(); // Return 204 No Content for successful deletion.
    }

    /**
     * Add a member to the project.
     * POST /projects/{project}/members
     */
    public function addMember(AddProjectMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('addMember', $project);

        $validatedData = $request->validated();
        $result = $this->projectRepository->addMember($project, $validatedData['user_id'], $validatedData['role']);

        if ($result) {
            // Reload the project with updated members to return in response
            $projectWithRelations = $this->projectRepository->loadRelationships($project->fresh());
            return response()->json(new ProjectResource($projectWithRelations), Response::HTTP_OK);
        }
        return response()->json(['message' => __('api.project_member.add_conflict')], Response::HTTP_CONFLICT);
    }

    /**
     * Update a member's role in the project.
     * PATCH /projects/{project}/members/{user}
     */
    public function updateMemberRole(UpdateProjectMemberRoleRequest $request, Project $project, User $user): JsonResponse
    {
        $this->authorize('updateMemberRole', [$project, $user]);

        $validatedData = $request->validated();
        $result = $this->projectRepository->updateMemberRole($project, $user->id, $validatedData['role']);

        if ($result) {
            $projectWithRelations = $this->projectRepository->loadRelationships($project->fresh());
            return response()->json(new ProjectResource($projectWithRelations), Response::HTTP_OK);
        }
        return response()->json(['message' => __('api.project_member.update_role_failed')], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove a member from the project.
     * DELETE /projects/{project}/members/{user}
     */
    public function removeMember(Project $project, User $user): JsonResponse
    {
        $this->authorize('removeMember', [$project, $user]);

        $result = $this->projectRepository->removeMember($project, $user->id);

        if ($result) {
            $projectWithRelations = $this->projectRepository->loadRelationships($project->fresh());
            return response()->json(new ProjectResource($projectWithRelations), Response::HTTP_OK);
        }
        return response()->json(['message' => __('api.project_member.remove_failed')], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Allows the authenticated user to leave the specified project.
     * DELETE /projects/{project}/leave
     */
    public function leave(Project $project): JsonResponse
    {
        $this->authorize('leave', $project);

        $result = $this->projectRepository->userLeaveProject($project, Auth::id());

        if ($result) {
            return response()->json(['message' => __('User successfully left the project.')], Response::HTTP_OK);
            // Or, if you prefer not to return content:
            // return response()->noContent(); 
        }

        // Although the policy should prevent most failures,
        // there might be a case where detach fails for some unexpected reason.
        return response()->json(['message' => __('Failed to leave the project.')], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
