<?php
/**
 * User Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Enum\UserRole;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\UserServiceInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/user';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }
    /**
     * Test index route for non-authorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }

    public function testIndexRouteNonAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteNonAdminUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $normalUser = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($normalUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single non-existent user.
     */
    public function testShowUserForNonExistentUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $testUserId = 1230;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUserId);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test show single user for non-admin.
     */
    public function testShowUserWithMockNonAdmin(): void
    {
        // given
        $expectedStatusCode = 302;
        $testUserId = 122;
        $expectedUser = new User();
        $userIdProperty = new \ReflectionProperty(User::class, 'id');
        $userIdProperty->setValue($expectedUser, $testUserId);
        $expectedUser->setEmail('u@e.pl');
        $expectedUser->setPassword('u123');
        $expectedUser->setRoles([UserRole::ROLE_USER->value]);
        $normalUser = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($normalUser);
//        $userRepository = static::getContainer()->get(UserRepository::class);
//        $userRepository->save($expectedUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedUser->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
//
//        $this->assertSelectorTextContains('html h1', '#'.$expectedUser->getId());
    }

    /**
     * Test show single user.
     */
    public function testShowUserWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testUserId = 122;
        $expectedUser = new User();
        $userIdProperty = new \ReflectionProperty(User::class, 'id');
        $userIdProperty->setValue($expectedUser, $testUserId);
        $expectedUser->setEmail('u@e.pl');
        $expectedUser->setPassword('u123');
        $expectedUser->setRoles([UserRole::ROLE_USER->value]);
        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects($this->once())
            ->method('findOneById')
            ->with($testUserId)
            ->willReturn($expectedUser);
        static::getContainer()->set(UserServiceInterface::class, $userService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
//        $userRepository = static::getContainer()->get(UserRepository::class);
//        $userRepository->save($expectedUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedUser->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
//
//        $this->assertSelectorTextContains('html h1', '#'.$expectedUser->getId());
    }

}
