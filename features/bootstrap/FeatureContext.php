<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawMinkContext implements Context, KernelAwareContext, SnippetAcceptingContext
{
    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $currentGroupData;

    /**
     * @var array
     */
    private $currentGroupListData;

    /**
     * @var array
     */
    private $currentUserData;

    /**
     * @var array
     */
    private $currentUserListData;

    /**
     * @BeforeScenario
     */
    public function cleanDataBase()
    {
        $this->currentGroupData = null;
        $this->currentUserData = null;
        $this->currentGroupListData = null;
        $this->currentUserListData = null;

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getEntityManager();

        $entityManager->createQuery("DELETE FROM UserManagementBundle:UserGroup")->execute();
        $entityManager->createQuery("DELETE FROM UserManagementBundle:User")->execute();
    }

    /**
     * Get http client
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client $client
     */
    protected function getClient()
    {
        return $this->getSession()->getDriver()->getClient();
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     * @return array|null
     */
    protected function getResponseData($client)
    {
        return json_decode($client->getResponse()->getContent(), true);
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @Given I create user group with name :arg1
     */
    public function iCreateUserGroupWithName($arg1)
    {
        $client = $this->getClient();


        $client->request('POST', '/groups/create', [
            'name' => $arg1
        ]);

        $this->currentGroupData = $this->getResponseData($client);
    }

    /**
     * @Then I should see :arg1
     */
    public function iShouldSee($arg1)
    {
        if (strpos($this->getClient()->getResponse()->getContent(), $arg1) === false)
        {
            throw new \Exception('Error editing user group!');
        }
    }

    /**
     * @Given I get group list
     */
    public function iGetGroupList()
    {
        $client = $this->getClient();

        $client->request('GET', '/groups/fetch');

        $this->currentGroupListData = $this->getResponseData($client);
    }


    /**
     * @Given I modify group to :arg1
     */
    public function iModifyGroupTo($arg1)
    {
        $groupId = $this->currentGroupData['id'];

        $client = $this->getClient();

        $client->request('PUT', '/groups/' . $groupId . '/modify', [
            'name' => $arg1
        ]);

        $this->currentGroupData = $this->getResponseData($client);
    }

    /**
     * @Then I create user with email :arg1, first name :arg2, last name :arg3 and status :arg4 and add them to group
     */
    public function iCreateUserWithEmailFirstNameLastNameAndStatusAndAddThemToGroup($email, $firstName, $lastName, $status)
    {
        $client = $this->getClient();

        $client->request('POST', '/users/create', [
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'state' => $status == 'active' ? true : false,
            'groupId' => $this->currentGroupData['id']
        ]);

        $this->currentUserData = $this->getResponseData($client);
    }

    /**
     * @Then I should have user with email :arg1, first name :arg2, last name :arg3 and status :arg4
     */
    public function iShouldHaveUserWithEmailFirstNameLastNameAndStatus($email, $firstName, $lastName, $status)
    {
        if (!is_array($this->currentUserData))
        {
            throw new \Exception('We have not any user!');
        }

        $isStatusUserCorrect = (($this->currentUserData['state'] === true) && ($status == 'active')) ||
            (($this->currentUserData['state'] === false) && ($status == 'disable'));

        if (
            ($this->currentUserData['email'] != $email) ||
            ($this->currentUserData['first_name'] != $firstName) ||
            ($this->currentUserData['last_name'] != $lastName) ||
            !$isStatusUserCorrect
        )
        {
            throw new \Exception('User data is incorrect');
        }
    }

    /**
     * @Then I should see in user list user with email :arg1
     */
    public function iShouldSeeInUserListUserWithEmail($arg1)
    {
        if (!is_array($this->currentUserListData))
        {
            throw new \Exception('We have not user list');
        }

        $hasUser = false;
        foreach ($this->currentUserListData as $item)
        {
            if ($item['email'] == $arg1)
            {
                $hasUser = true;
                break;
            }
        }

        if (!$hasUser)
        {
            throw new \Exception('User with email "' . $arg1 . '" does not exist in the list');
        }
    }


    /**
     * @Given I get user list
     */
    public function iGetUserList()
    {
        $client = $this->getClient();

        $client->request('GET', '/users/fetch');

        $this->currentUserListData = $this->getResponseData($client);
    }

    /**
     * @Then I modify user with email :arg1 to first name :arg2, last name :arg3 and status :arg4
     */
    public function iModifyUserWithEmailToFirstNameAndLastName($email, $firstName, $lastName, $status)
    {
        $client = $this->getClient();

        $userId = $this->currentUserData['id'];

        $client->request('PUT', '/users/' . $userId . '/modify', [
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'state' => $status == 'active' ? true : false
        ]);

        $this->currentUserData = $this->getResponseData($client);
    }

    /**
     * @Then I add user to group
     */
    public function iAddUserToGroup()
    {
        $client = $this->getClient();

        $userId = $this->currentUserData['id'];
        $groupId = $this->currentGroupData['id'];

        $client->request('POST', '/groups/' . $groupId . '/addUser/' . $userId);
    }


    /**
     * @Given I get user list by group
     */
    public function iGetUserListByGroup()
    {
        $client = $this->getClient();
        $groupUd = $this->currentGroupData['id'];

        $client->request('GET', '/users/fetch/' . $groupUd);
        $this->currentUserListData = $this->getResponseData($client);
    }

    /**
     * @Given I remove user from group
     */
    public function iRemoveUserFromGroup()
    {
        $client = $this->getClient();

        $userId = $this->currentUserData['id'];
        $groupId = $this->currentGroupData['id'];

        $client->request('POST', '/groups/' . $groupId . '/removeUser/' . $userId);
    }

    /**
     * @Then I should not see user with email :arg1 in user list
     */
    public function iShouldNotSeeUserWithEmailInUserList($arg1)
    {
        if (!is_array($this->currentUserListData))
        {
            throw new \Exception('We have not user list');
        }

        $hasUser = false;
        foreach ($this->currentUserListData as $item)
        {
            if ($item['email'] == $arg1)
            {
                $hasUser = true;
                break;
            }
        }

        if ($hasUser)
        {
            throw new \Exception('We have user with email "' . $arg1 . '" in user list!');
        }
    }

    /**
     * @Then I should have group with name :arg1
     */
    public function iShouldHaveGroupWithName($arg1)
    {
        if (
            !is_array($this->currentGroupData) ||
            (!isset($this->currentGroupData['name'])) ||
            ($this->currentGroupData['name'] != $arg1)
        )
        {
            throw new \Exception('User group did not created with name "' . $arg1 . '"');
        }
    }

    /**
     * @Then I should see in group list group with name :arg1
     */
    public function iShouldSeeInGroupListGroupWithName($arg1)
    {
        if (!is_array($this->currentGroupListData)) {
            throw new \Exception('Missing group list');
        }

        $hasGroupWithName = false;
        foreach ($this->currentGroupListData as $item)
        {
            if ($item['name'] == $arg1)
            {
                $hasGroupWithName = true;
                break;
            }
        }

        if (!$hasGroupWithName)
        {
            throw new \Exception('Missing group with name "' . $arg1 . '" in group list');
        }
    }

    /**
     * Sets Kernel instance.
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function setKernel(\Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
}
