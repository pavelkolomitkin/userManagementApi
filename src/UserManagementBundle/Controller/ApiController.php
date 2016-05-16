<?php

namespace UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    const RESPONSE_STATUS_OK = 200;
    const RESPONSE_STATUS_UNPROCESSABLE_ENTITY = 422;
    const RESPONSE_STATUS_NOT_FOUND = 404;

    protected function getResponse($data, $status = 200, $headers = [])
    {
        $serializer = $this->get('jms_serializer');

        $normalizedData = $data;
        if (!is_scalar($normalizedData))
        {
            $normalizedData = $serializer->toArray($normalizedData);
        }

        return new JsonResponse($normalizedData, $status, $headers);
    }

    protected function getErrorResponse($messages, $statusCode)
    {
        $messages = is_array($messages) ? $messages : [$messages];

        return $this->getResponse(['errors' => $messages], $statusCode);
    }

    /**
     * Get all children form error messages as array ['formName' => [string]]
     *
     * @param Form $form
     * @return array
     */
    protected function getFormErrorsAsArray(Form $form)
    {
        $result = [];

        /** @var Form $child */
        foreach ($form->all() as $child)
        {
            $errors = $child->getErrors();
            if (count($errors) > 0)
            {
                $messages = [];
                foreach ($errors as $error)
                {
                    $messages[] = $error->getMessage();
                }
                $result[$child->getName()] = $messages;
            }
        }

        return $result;
    }

    protected function filterFormValues(Form $form, array $data)
    {
        $result = array_intersect_key($data, array_flip(array_keys($form->all())));

        return $result;
    }
}