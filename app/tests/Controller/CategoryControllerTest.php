<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CategoryServiceInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

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
    public function testIndexRouteNonAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 302;
//        $this->httpClient = static::createClient();
//        $user = $this->createUser([UserRole::ROLE_USER->value]);
//        $this->httpClient->loginUser($user);

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

    /**
     * Test show single category.
     */
    public function testShowCategoryWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $expectedCategory->setCreatedAt(new \DateTimeImmutable());
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable());
        $expectedCategory->setSlug('test-category');
        $categoryService = $this->createMock(CategoryServiceInterface::class);
        $categoryService->expects($this->once())
            ->method('findOneById')
            ->with($testCategoryId)
            ->willReturn($expectedCategory);
        static::getContainer()->set(CategoryServiceInterface::class, $categoryService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $this->assertSelectorTextContains('html h1', '#'.$expectedCategory->getId());
        // ... more assertions...
    }

    /**
     * Test create category.
     */
    public function testCreateCategory(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $route = self::TEST_ROUTE . '/create';
//        echo $route;
        $this->httpClient->request('GET', $route);
//        echo $this->httpClient->getResponse()->getContent();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit category.
     */
    public function testEditCategoryWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $expectedCategory->setCreatedAt(new \DateTimeImmutable());
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable());
        $expectedCategory->setSlug('test-category');
        $categoryService = $this->createMock(CategoryServiceInterface::class);
        $categoryService->expects($this->once())
            ->method('findOneById')
            ->with($testCategoryId)
            ->willReturn($expectedCategory);
        $categoryService->expects($this->once())
            ->method('categoryExists')
            ->with($testCategoryId)
            ->willReturn(true);
        static::getContainer()->set(CategoryServiceInterface::class, $categoryService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $route = self::TEST_ROUTE . '/' . $expectedCategory->getId() . '/edit';
//        echo $route;
        $this->httpClient->request('GET', $route);
//        echo $this->httpClient->getResponse()->getContent();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $this->assertSelectorTextContains('html h1', '#'.$expectedCategory->getId());
        // ... more assertions...
    }

    /**
     * Test delete category.
     */
    public function testDeleteCategoryWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $expectedCategory->setCreatedAt(new \DateTimeImmutable());
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable());
        $expectedCategory->setSlug('test-category');
        $categoryService = $this->createMock(CategoryServiceInterface::class);
        $categoryService->expects($this->once())
            ->method('findOneById')
            ->with($testCategoryId)
            ->willReturn($expectedCategory);
        $categoryService->expects($this->once())
            ->method('categoryExists')
            ->with($testCategoryId)
            ->willReturn(true);
        $categoryService->expects($this->once())
            ->method('canBeDeleted')
            ->with($testCategoryId)
            ->willReturn(true);
        static::getContainer()->set(CategoryServiceInterface::class, $categoryService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId().'/delete');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $this->assertSelectorTextContains('html h1', '#'.$expectedCategory->getId());
        // ... more assertions...
    }
}
