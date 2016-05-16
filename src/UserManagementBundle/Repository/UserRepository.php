<?php

namespace UserManagementBundle\Repository;
use UserManagementBundle\Entity\UserGroup;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Get users by group
     *
     * @param UserGroup $group
     * @return array
     */
    public function getUsersByGroup(UserGroup $group)
    {
        $result = $this
            ->createQueryBuilder('user')
            ->innerJoin('user.groups', 'groups')
            ->where('groups.id = :groupId')
            ->setParameter(':groupId', $group->getId())
            ->getQuery()
            ->getResult();

        return $result;
    }
}