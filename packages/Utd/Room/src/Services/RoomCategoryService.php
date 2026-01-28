<?php

namespace Utd\Room\Services;

use Utd\Room\Repositories\RoomCategoryRepository;

class RoomCategoryService
{
    public function __construct(
        protected RoomCategoryRepository $roomCategoryRepository
    ) {
    }

    /**
     * Get all room categories (parent categories)
     */
    public function index()
    {
        return $this->roomCategoryRepository->getRoomCategory();
    }

    /**
     * Find category by ID
     */
    public function findById($id)
    {
        return $this->roomCategoryRepository->findById($id);
    }

    /**
     * Get categories by type (child categories)
     */
    public function getByType()
    {
        return $this->roomCategoryRepository->getTypeRoomCategory();
    }

    /**
     * Create new category
     */
    public function create(array $data)
    {
        return $this->roomCategoryRepository->create($data);
    }

    /**
     * Update category
     */
    public function update($id, array $data)
    {
        return $this->roomCategoryRepository->update($data, $id);
    }

    /**
     * Delete category
     */
    public function delete($id)
    {
        return $this->roomCategoryRepository->delete($id);
    }
}
