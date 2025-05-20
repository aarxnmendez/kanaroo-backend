<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    /**
     * Get all tags for a specific project.
     * @param Project $project
     * @return Collection<int, Tag>
     */
    public function getAllForProject(Project $project): Collection;

    /**
     * Create a new tag for a specific project.
     * @param array $data
     * @param Project $project
     * @return Tag
     */
    public function create(array $data, Project $project): Tag;

    /**
     * Find a tag by its ID.
     * @param int $id
     * @return Tag|null
     */
    public function findById(int $id): ?Tag;

    /**
     * Update an existing tag.
     * @param Tag $tag
     * @param array $data
     * @return Tag
     */
    public function update(Tag $tag, array $data): Tag;

    /**
     * Delete a tag.
     * @param Tag $tag
     * @return bool
     */
    public function delete(Tag $tag): bool;
}
