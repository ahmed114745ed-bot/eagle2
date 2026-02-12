<?php

namespace Utd\Gifts\Contracts;

/**
 * GiftRepositoryContract
 *
 * Contract لإدارة عمليات Gift Repository
 */
interface GiftRepositoryContract
{
    /**
     * جلب جميع الهدايا
     *
     * @param  int|null  $type
     * @return mixed
     */
    public function all($type = null);

    /**
     * إنشاء هدية جديدة
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * تحديث هدية
     *
     * @param  int  $id
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * حذف هدية
     *
     * @param  int  $id
     * @return mixed
     */
    public function delete($id);
}
