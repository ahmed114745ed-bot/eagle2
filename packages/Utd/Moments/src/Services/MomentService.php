<?php

namespace Utd\Moments\Services;

use App\Contracts\MomentContract;
use App\Helpers\Common;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Utd\Moments\Entities\Moment;
use Utd\Moments\Repositories\MomentRepository;

class MomentService extends MomentBaseModelService implements MomentContract
{
    public function __construct(Moment $model, public MomentRepository $momentRepository)
    {
        parent::__construct($model);
    }

    public function getMomentsByType($type, $userId, $page, $currentUser)
    {
        switch ($type) {
            case 1:
                return $this->momentRepository->getUserMoments($userId ?? $currentUser, $page);
            case 2:
                return $this->momentRepository->getLikedMoments($currentUser, $page);
            case 3:
                return $this->momentRepository->getFollowedMoments($currentUser, $page);
            case 4:
                return $this->momentRepository->getAllMoments($currentUser, $page);
            case 5:
                return $this->momentRepository->getNewMoments($currentUser);
            case 6:
                return $this->momentRepository->momentUserFollow($currentUser);
            default:
                return null;
        }
    }

    public function deleteMomentAndReport($momentId, $reportId)
    {
        $moment = $this->momentRepository->findMomentById($momentId);
        if (!$moment) {
            return [
                'success' => false,
                'message' => 'Moment not found',
                'status' => 404,
            ];
        }

        $reportMoment = $this->momentRepository->findReportMomentById($reportId);
        if ($reportMoment) {
            $this->momentRepository->deleteReportMoment($reportMoment);
        }

        $this->momentRepository->deleteMoment($moment);

        return [
            'success' => true,
            'message' => 'Moment and report successfully deleted',
            'status' => 200,
        ];
    }

    public function deleteMomentById($id)
    {
        $moment = $this->momentRepository->findMomentById($id);

        if (!$moment) {
            return [
                'success' => false,
                'message' => 'Item not found',
                'status' => 404,
            ];
        }

        $this->momentRepository->deleteMoment($moment);

        return [
            'success' => true,
            'message' => 'Success Deleted',
            'status' => 200,
        ];
    }

    public function createMoment($contacts, $request)
    {
        $userId = Auth::id();

        if (empty($contacts) && !$request->hasFile('multi_image')) {
            return [
                'success' => false,
                'message' => 'Not allowed to post empty content',
            ];
        }

        $moment = $this->momentRepository->createMoment([
            'user_id' => $userId,
            'description' => $contacts,
        ]);

        if (!$moment) {
            return [
                'success' => false,
                'message' => 'Try again',
            ];
        }

        if ($request->hasFile('multi_image')) {
            foreach ($request->file('multi_image') as $file) {
                if ($file && $file->isValid()) {
                    $imagePath = Common::upload('profile', $file);
                    $moment->images()->create([
                        'image' => $imagePath,
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Success',
        ];
    }

    public function getMoment($id, $userId)
    {
        $moment = $this->momentRepository->getMomentById($id, $userId);

        if (!$moment) {
            return [
                'success' => false,
                'message' => __('Moment not found'),
                'data' => null,
                'status' => 402,
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'data' => $moment,
            'status' => 200,
        ];
    }

    public function show(User $user) {}

    public function create(array $data, int $userId) {}

    public function delete(int|Moment $moment)
    {
        if (gettype($moment) == 'integer') {
            $moment = Moment::query()->find($moment);
        }

        $moment->delete();
    }

    /**
     * Methods merged from MomentsService
     */

    public function all($id, $perPage, $page)
    {
        return $this->momentRepository->all($id, $perPage, $page);
    }

    public function createFromRequest($request)
    {
        if ($request->hasFile('img')) {
            $img = Common::upload('images', $request->file('img'));
        }
        $data = [
            'user_id' => $request->user_id,
            'description' => $request->user_id,
            'img' => $img ?? ''
        ];
        $this->momentRepository->create($data);
        return true;
    }

    public function updateFromRequest($id, $request)
    {
        $data = [
            'name' => $request->name,
        ];
        if ($request->hasFile('img')) {
            $data['img'] = Common::upload('images', $request->file('img'));
        }
        $this->momentRepository->update($data, $id);
        return true;
    }

    public function deleteById($id)
    {
        $data = $this->momentRepository->findOrFail($id);
        $data->delete();
        return true;
    }

    public function find($id)
    {
        return $this->momentRepository->find($id);
    }

    public function search($uuid)
    {
        return $this->momentRepository->search($uuid);
    }

    public function getUserMomentsForDashboard($user_id)
    {
        return $this->momentRepository->getUserMomentsForDashboard($user_id);
    }
}
