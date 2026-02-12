<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskStreamServiceContract
{
    /**
     * Get paginated list of task streams
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator;

    /**
     * Create a new task stream
     *
     * @return mixed
     */
    public function store();

    /**
     * Join an existing task stream
     *
     * @param array $data
     * @return mixed
     */
    public function join(array $data);

    /**
     * Leave a task stream
     *
     * @param array $data
     * @return mixed
     */
    public function leave(array $data);

    /**
     * Send invitation to join task stream
     *
     * @param array $data
     * @return array
     */
    public function sendInvitation(array $data);

    /**
     * Respond to a task stream invitation
     *
     * @param array $data
     * @return mixed
     */
    public function respondInvitation(array $data);

    /**
     * Get live friends list
     *
     * @return LengthAwarePaginator
     */
    public function liveFriends(): LengthAwarePaginator;
}
