<?php
/**
 * Task Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\TaskService;
use App\Service\TaskServiceInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TaskControllerTest.
 */
class TaskControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/task';

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
     * Create category.
     */
    protected function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('TTitle');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create task.
     */
    private function createTask($category, $user): Task
    {
        $task = new Task();
        $task->setTitle('Title');
        $task->setUpdatedAt(new \DateTimeImmutable());
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setCategory($category);
        $task->setAuthor($user);
        $taskService = self::getContainer()->get(TaskService::class);
        $taskService->save($task);

        return $task;
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
     * Test show single task.
     */
    public function testShowTaskForNonExistantTask(): void
    {
        // given
        $expectedStatusCode = 302;
        $testTaskId = 1230;

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testTaskId);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test show single task.
     */
    public function testShowTaskWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $testTaskId = 122;
        $expectedTask = new Task();
        $taskIdProperty = new \ReflectionProperty(Task::class, 'id');
        $taskIdProperty->setValue($expectedTask, $testTaskId);
        $expectedTask->setTitle('Test task');
        $expectedTask->setCreatedAt(new \DateTimeImmutable());
        $expectedTask->setUpdatedAt(new \DateTimeImmutable());
        $expectedTask->setCategory($this->createCategory());
        $taskService = $this->createMock(TaskServiceInterface::class);
        $taskService->expects($this->once())
            ->method('findOneById')
            ->with($testTaskId)
            ->willReturn($expectedTask);
        static::getContainer()->set(TaskServiceInterface::class, $taskService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $expectedTask->setAuthor($adminUser);
        $this->httpClient->loginUser($adminUser);
//        $taskRepository = static::getContainer()->get(TaskRepository::class);
//        $taskRepository->save($expectedTask);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedTask->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
//
//        $this->assertSelectorTextContains('html h1', '#'.$expectedTask->getId());
    }
//
    /**
     * Test create task.
     */
    public function testCreateTask(): void
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

//    /**
//     * Test create and save task.
//     */
//    public function testCreateSaveTask(): void
//    {
//        // given
//        $expectedStatusCode = 302;
//        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
//        $this->httpClient->loginUser($adminUser);
//        $createdTaskTitle = "newCreatedTask";
//        $testCategoryId = 123;
//        $expectedCategory = new Category();
//        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
//        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
//        $expectedCategory->setTitle('Test category');
//        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
//        // when
//        $route = self::TEST_ROUTE . '/create';
////        echo $route;
//        $this->httpClient->request('GET', $route);
//        $this->httpClient->submitForm(
//            'Zapisz',
//            ['task' =>
//                [
//                    'title' => $createdTaskTitle,
//                    'category' => ""
//                ]
//            ]
//        );
//
//
////        echo $this->httpClient->getResponse()->getContent();
//
//
//        // then
//        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//    }


//    /**
//     * Test edit task.
//     */
//    public function testEditTaskWithMock(): void
//    {
//        // given
//        $expectedStatusCode = 200;
//        $testTaskId = 123;
//        $expectedTask = new Task();
//        $taskIdProperty = new \ReflectionProperty(Task::class, 'id');
//        $taskIdProperty->setValue($expectedTask, $testTaskId);
//        $expectedTask->setTitle('Test task');
//        $expectedTask->setCreatedAt(new \DateTimeImmutable());
//        $expectedTask->setUpdatedAt(new \DateTimeImmutable());
//        $taskService = $this->createMock(TaskServiceInterface::class);
//        $taskService->expects($this->once())
//            ->method('findOneById')
//            ->with($testTaskId)
//            ->willReturn($expectedTask);
//        static::getContainer()->set(TaskServiceInterface::class, $taskService);
//        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
//        $this->httpClient->loginUser($adminUser);
//        // when
//        $route = self::TEST_ROUTE . '/' . $expectedTask->getId() . '/edit';
////        echo $route;
//        $this->httpClient->request('GET', $route);
////        echo $this->httpClient->getResponse()->getContent();
//        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//
//        // then
//        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        $this->assertSelectorTextContains('html h1', '#'.$expectedTask->getId());
//        // ... more assertions...
//    }

    /**
     * Test edit task.
     */
    public function testEditTaskWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $testTaskId = 122;
        $expectedTask = new Task();
        $taskIdProperty = new \ReflectionProperty(Task::class, 'id');
        $taskIdProperty->setValue($expectedTask, $testTaskId);
        $expectedTask->setTitle('Test task');
        $expectedTask->setCreatedAt(new \DateTimeImmutable());
        $expectedTask->setUpdatedAt(new \DateTimeImmutable());
        $expectedTask->setCategory($this->createCategory());
        $taskService = $this->createMock(TaskServiceInterface::class);
        $taskService->expects($this->once())
            ->method('findOneById')
            ->with($testTaskId)
            ->willReturn($expectedTask);
        static::getContainer()->set(TaskServiceInterface::class, $taskService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $expectedTask->setAuthor($adminUser);
        $this->httpClient->loginUser($adminUser);
//        $taskRepository = static::getContainer()->get(TaskRepository::class);
//        $taskRepository->save($expectedTask);
        // when
        $route = $route = self::TEST_ROUTE . '/' . $expectedTask->getId() . '/edit';
        $this->httpClient->request('GET', $route);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
//
//        $this->assertSelectorTextContains('html h1', '#'.$expectedTask->getId());
    }

    /**
     * Test delete task.
     */
    public function testDeleteTaskWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $testTaskId = 122;
        $expectedTask = new Task();
        $taskIdProperty = new \ReflectionProperty(Task::class, 'id');
        $taskIdProperty->setValue($expectedTask, $testTaskId);
        $expectedTask->setTitle('Test task');
        $expectedTask->setCreatedAt(new \DateTimeImmutable());
        $expectedTask->setUpdatedAt(new \DateTimeImmutable());
        $expectedTask->setCategory($this->createCategory());
        $taskService = $this->createMock(TaskServiceInterface::class);
        $taskService->expects($this->once())
            ->method('findOneById')
            ->with($testTaskId)
            ->willReturn($expectedTask);
        static::getContainer()->set(TaskServiceInterface::class, $taskService);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $expectedTask->setAuthor($adminUser);
        $this->httpClient->loginUser($adminUser);
//        $taskRepository = static::getContainer()->get(TaskRepository::class);
//        $taskRepository->save($expectedTask);
        // when
        $route = $route = self::TEST_ROUTE . '/' . $expectedTask->getId() . '/delete';
        $this->httpClient->request('GET', $route);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        echo $this->httpClient->getResponse()->getContent();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        echo $actualStatusCode = $this->httpClient->getResponse()->getContent();
//
//        $this->assertSelectorTextContains('html h1', '#'.$expectedTask->getId());
    }

    /**
     * Test delete task.
     */
    public function testDeleteTaskForm(): void
    {
        // given
        $expectedStatusCode = 302;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $expectedCategory = $this ->createCategory();
        $expectedTask = $this->createTask($expectedCategory, $adminUser);
        $route = self::TEST_ROUTE . '/' . $expectedTask->getId() . '/delete';
//        echo $route;
        $this->httpClient->request('GET', $route);
        $this->httpClient->submitForm(
            'Usuń'

        );

        // then
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }
}
