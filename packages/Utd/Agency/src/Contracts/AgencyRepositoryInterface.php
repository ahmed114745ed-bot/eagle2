<?php

namespace Utd\Agency\Contracts;

interface AgencyRepositoryInterface
{
    public function findById($id);

    public function findByOwner($ownerId, $status = null);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getActiveAgency($id, $perPage, $page);

    public function getAllActiveAgency($id);

    public function getByAdditionalInfo();

    public function getByAdditionalInfoPaginate($id, $uuid, $perPage, $page, $status = null, $action = null);

    public function members($agency);

    public function getAgencyByFilter($keyword);

    public function getByAgencyMangerId($agencyMangerId);

    public function getJoinRequests($agencyId);

    public function report($id, $month, $year, $perPage, $page);
}
