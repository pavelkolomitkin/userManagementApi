<?php

namespace UserManagementBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserManagementBundle\Entity\User;
use UserManagementBundle\Form\Type\UserType;

/**
 * Class UserController
 * @package UserManagementBundle\Controller
 */
class UserController extends ApiController
{
    /**
     * Get all user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $users = $this->getDoctrine()->getManager()->getRepository('UserManagementBundle:User')->findAll();

        return $this->getResponse($users);
    }

    /**
     * Get users by group
     *
     * @param Request $request
     * @param $groupId
     * @return JsonResponse
     */
    public function listByGroupAction(Request $request, $groupId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $group = $entityManager->getRepository('UserManagementBundle:UserGroup')->find($groupId);
        if (!$group)
        {
            return $this->getErrorResponse('Group does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $users = $entityManager->getRepository('UserManagementBundle:User')->getUsersByGroup($group);
        return $this->getResponse($users);
    }

    /**
     * Get user by id
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id)
    {
        $user = $this->getDoctrine()->getManager()->getRepository('UserManagementBundle:User')->find($id);
        if (!$user)
        {
            return $this->getErrorResponse('User does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        return $this->getResponse($user);
    }

    /**
     * Create a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $groupId = $request->request->get('groupId');
        $group = $entityManager->getRepository('UserManagementBundle:UserGroup')->find($groupId);
        if (!$group)
        {
            return $this->getErrorResponse('Group does not exist', self::RESPONSE_STATUS_UNPROCESSABLE_ENTITY);
        }

        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->submit($this->filterFormValues($form, $request->request->all()));
        if ($form->isValid())
        {
            /** @var User $user */
            $user = $form->getData();

            $user->addGroup($group);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->getResponse($user);
        }

        return $this->getErrorResponse(
            $this->getFormErrorsAsArray($form),
            self::RESPONSE_STATUS_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Modify user info
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function editAction(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository('UserManagementBundle:User')->find($id);
        if (!$user)
        {
            return $this->getErrorResponse('User does not exist', self::RESPONSE_STATUS_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->submit($this->filterFormValues($form, $request->request->all()));
        if ($form->isValid())
        {
            $user = $form->getData();
            $entityManager->flush();

            return $this->getResponse($user);
        }


        return $this->getErrorResponse(
            $this->getFormErrorsAsArray($form),
            self::RESPONSE_STATUS_UNPROCESSABLE_ENTITY
        );
    }
}