<?php

namespace UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserManagementBundle\Entity\UserGroup;
use UserManagementBundle\Form\Type\UserGroupType;

class GroupController extends ApiController
{
    /**
     * Get group list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $groups = $this->getDoctrine()->getManager()->getRepository('UserManagementBundle:UserGroup')->findAll();

        return $this->getResponse($groups);
    }

    /**
     * Create a new group
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $group = new UserGroup();

        $form = $this->createForm(UserGroupType::class, $group);

        $form->submit($this->filterFormValues($form, $request->request->all()));
        if ($form->isValid())
        {
            $group = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($group);
            $entityManager->flush();

            return $this->getResponse($group);
        }

        $errors = $this->getFormErrorsAsArray($form);
        return $this->getErrorResponse($errors, self::RESPONSE_STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * Get group by id
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('UserManagementBundle:UserGroup');

        $group = $repository->find($id);
        if (!$group)
        {
            return $this->getErrorResponse('Group does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        return $this->getResponse($group);
    }

    /**
     * Modify group
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function editAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('UserManagementBundle:UserGroup');

        $group = $repository->find($id);
        if (!$group)
        {
            return $this->getErrorResponse('Group does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $form = $this->createForm(UserGroupType::class, $group);

        $form->submit($this->filterFormValues($form, $request->request->all()));
        if ($form->isValid())
        {
            $group = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->getResponse($group);
        }

        $errors = $this->getFormErrorsAsArray($form);
        return $this->getErrorResponse($errors, self::RESPONSE_STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * Add user to group
     *
     * @param $groupId
     * @param $userId
     * @return JsonResponse
     */
    public function addUserAction($groupId, $userId)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $groupRepository = $entityManager->getRepository('UserManagementBundle:UserGroup');
        $group = $groupRepository->find($groupId);
        if (!$group)
        {
            return $this->getErrorResponse('Group does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $userRepository = $entityManager->getRepository('UserManagementBundle:User');
        $user = $userRepository->find($userId);
        if (!$user)
        {
            return $this->getErrorResponse('User does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $user->addGroup($group);
        $entityManager->flush();

        return $this->getResponse('OK');
    }

    /**
     * Remove user from group
     *
     * @param $groupId
     * @param $userId
     * @return JsonResponse
     */
    public function removeUserAction($groupId, $userId)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $groupRepository = $entityManager->getRepository('UserManagementBundle:UserGroup');
        $group = $groupRepository->find($groupId);
        if (!$group)
        {
            return $this->getErrorResponse('Group does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $userRepository = $entityManager->getRepository('UserManagementBundle:User');
        $user = $userRepository->find($userId);
        if (!$user)
        {
            return $this->getErrorResponse('User does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $user->removeGroup($group);
        $entityManager->flush();

        return $this->getResponse('OK');
    }
}