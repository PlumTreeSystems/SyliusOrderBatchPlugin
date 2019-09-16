<?php


namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Setup;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Sylius\Behat\Page\Shop\Account\DashboardPageInterface;
use Sylius\Behat\Service\SharedStorage;
use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Core\Model\ShopUser;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReferralContext implements Context
{

    /**
     * @var DashboardPageInterface
     */
    private $dashboardPage;

    /**
     * @var SharedStorage
     */
    private $sharedStorage;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /** @var FactoryInterface */
    private $adminUserFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var RepositoryInterface */
    private $customerGroupRepository;

    /** @var FactoryInterface */
    private $customerFactory;

    /** @var FactoryInterface */
    private $shopUserFactory;

    /**
     * ReferralContext constructor.
     * @param DashboardPageInterface $homePage
     * @param SharedStorage $sharedStorage
     * @param UrlGeneratorInterface $router
     * @param FactoryInterface $adminUserFactory
     * @param EntityManager $entityManager
     * @param RepositoryInterface $customerGroupRepository
     * @param FactoryInterface $customerFactory
     * @param FactoryInterface $shopUserFactory
     */
    public function __construct(DashboardPageInterface $homePage,
                                SharedStorage $sharedStorage,
                                UrlGeneratorInterface $router,
                                FactoryInterface $adminUserFactory,
                                EntityManager $entityManager,
                                RepositoryInterface $customerGroupRepository,
                                FactoryInterface $customerFactory,
                                FactoryInterface $shopUserFactory
    ) {

        $this->dashboardPage = $homePage;
        $this->sharedStorage = $sharedStorage;
        $this->router = $router;
        $this->adminUserFactory = $adminUserFactory;
        $this->entityManager = $entityManager;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerFactory = $customerFactory;
        $this->shopUserFactory = $shopUserFactory;
    }

    /**
     * @Given the store has a user, with role :role, with name :name, with email :email and with :password password
     * @param $role
     * @param $name
     * @param $email
     * @param $password
     * @param $invited
     */
    public function theStoreHasAUserWithRoleWithNameAndWithEmail($role, $name, $email, $password)
    {
        $this->createUser($role, $name, $email, $password);
    }

    private function createUser($role, $name, $email, $password)
    {
        $user = null;
        $name = explode(' ', $name);
        switch ($role) {
            case 'admin': {
                /** @var AdminUser $user */
                $user = $this->adminUserFactory->createNew();
                $user->addRole(AdminUser::DEFAULT_ADMIN_ROLE);
                $user->setFirstName($name[0]);
                $user->setLastName($name[1]);
                $user->setEmail($email);
                $user->setPlainPassword($password);
                $user->setEnabled(true);
            }
                break;
            default: {
                $group = $this->customerGroupRepository->findOneBy(['code' => $role]);
                if (!$group) {
                    throw new PendingException();
                }
                /** @var Customer $customer */
                $customer = $this->customerFactory->createNew();
                $customer->setGroup($group);
                $customer->setFirstName($name[0]);
                $customer->setLastName($name[1]);
                $customer->setEmail($email);
                $this->sharedStorage->set("customer", $customer);
                /** @var ShopUser $user */
                $user = $this->shopUserFactory->createNew();
                $user->setCustomer($customer);
                $user->setEmail($email);
                $user->setPlainPassword($password);
                $user->setEnabled(true);
                $user->addRole(ShopUser::DEFAULT_ROLE);
                $this->entityManager->persist($customer);
            }
        }
        $this->sharedStorage->set("user", $user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

}
