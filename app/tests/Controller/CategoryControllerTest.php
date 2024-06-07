<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Note;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\CategoryServiceInterface;
use App\Service\NoteService;
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
     * Test show single category for non-authorised user.
     */
    public function testShowCategoryNonAuthorizedUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $testCategoryId = 123;
        $expectedCategory = new Category();
        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
        $expectedCategory->setTitle('Test category');
        $expectedCategory->setCreatedAt(new \DateTimeImmutable());
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable());
        $expectedCategory->setSlug('test-category');
        $categoryService = $this->createMock(CategoryServiceInterface::class);
        static::getContainer()->set(CategoryServiceInterface::class, $categoryService);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId());
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
        $this->assertTrue($this->httpClient->getResponse()->isRedirect());
        $this->assertEquals('/login', $this->httpClient->getResponse()->headers->get('Location'));
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
     * Create category.
     */
    protected function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Title');
        $category->setUpdatedAt(new \DateTimeImmutable());
        $category->setCreatedAt(new \DateTimeImmutable());
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }
    /**
     * Create note.
     */
    private function createNote($category, $user): Note
    {
        $note = new Note();
        $note->setTitle('Title');
        $note->setContent('NoteContent');
        $note->setUpdatedAt(new \DateTimeImmutable());
        $note->setCreatedAt(new \DateTimeImmutable());
        $note->setCategory($category);
        $note->setAuthor($user);
        $noteService = self::getContainer()->get(NoteService::class);
        $noteService->save($note);

        return $note;
    }


    /**
     * Test show single category.
     */
    public function testShowCategoryForNonExistantCategory(): void
    {
        // given
        $expectedStatusCode = 404;
        $testCategoryId = 1230;
        $testCategoryId = 1230;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit non-existant category.
     */
    public function testEditCategoryForNonExistantCategory(): void
    {
        // given
        $expectedStatusCode = 302;
        $testCategoryId = 1234;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $route = self::TEST_ROUTE . '/' . $testCategoryId . '/edit';
        $this->httpClient->request('GET', $route);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test delete non-existant category.
     */
    public function testDeleteCategoryForNonExistantCategory(): void
    {
        // given
        $expectedStatusCode = 302;
        $testCategoryId = 1234;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $route = self::TEST_ROUTE . '/' . $testCategoryId . '/delete';
        $this->httpClient->request('GET', $route);
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
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
     * Test create and save category.
     */
    public function testCreateSaveCategory(): void
    {
        // given
        $expectedStatusCode = 302;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $createdCategoryTitle = "createdCat";
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        // when
        $route = self::TEST_ROUTE . '/create';
//        echo $route;
        $this->httpClient->request('GET', $route);
        $this->httpClient->submitForm(
            'Zapisz',
            ['category' =>
                [
                    'title' => $createdCategoryTitle
                ]
            ]
        );


//        echo $this->httpClient->getResponse()->getContent();


        // then
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);

    }


    /**
     * Test edit category.
     */
    public function testEditCategoryWithMock(): void
    {
        // given
        $expectedStatusCode = 200;
        $expectedCategory = $this->createCategory();
        $newCategoryTitle = 'newTitle';
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        // when
        $route = self::TEST_ROUTE . '/' . $expectedCategory->getId() . '/edit';
       $this->httpClient->request('GET', $route);
        $this->httpClient->submitForm(
            'Edytuj',
            ['category' =>
                [
                    'title' => $newCategoryTitle
                ]
            ]
        );
        $this->httpClient->request('GET', $route);
//        echo $this->httpClient->getResponse()->getContent();
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();


        // then
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }

    /**
     * Test edit and save category.
     */
    public function testEditSaveCategoryWithMock(): void
    {
        // given
        $expectedStatusCode = 302;
        $testCategoryId = 120;
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
// Check if the GET request works and the form is present
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="category"]');

        // Submit the form with the updated data
        $editedCategoryTitle = 'newEditedCategory';
        $this->httpClient->submitForm(
            'Edytuj',  // This should be the value attribute of the submit button in your form
            [
                'category[title]' => $editedCategoryTitle
            ],
            'PUT' // Ensure the form submission method is correct
        );

        // Check the response after form submission
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
//        echo $this->httpClient->getResponse()->getContent();
//        $this->assertResponseRedirects('/category/index'); // Ensure it redirects to the correct route
    }



//    /**
//     * Test editing a category with a PUT request.
//     */
//    public function testEditCategoryPostWithMock(): void
//    {
//        // given
//        $expectedStatusCode = 200; // Redirect after successful form submission
//        $testCategoryId = 123;
//        $expectedCategory = new Category();
//        $categoryIdProperty = new \ReflectionProperty(Category::class, 'id');
//        $categoryIdProperty->setValue($expectedCategory, $testCategoryId);
//        $expectedCategory->setTitle('Test category');
//        $expectedCategory->setCreatedAt(new \DateTimeImmutable());
//        $expectedCategory->setUpdatedAt(new \DateTimeImmutable());
//        $expectedCategory->setSlug('test-category');
//
//        $categoryService = $this->createMock(CategoryServiceInterface::class);
//        $categoryService->expects($this->once())
//            ->method('findOneById')
//            ->with($testCategoryId)
//            ->willReturn($expectedCategory);
//        $categoryService->expects($this->once())
//            ->method('categoryExists')
//            ->with($testCategoryId)
//            ->willReturn(true);
//        $categoryService->expects($this->once())
//            ->method('save')
//            ->with($this->isInstanceOf(Category::class));
//
//        static::getContainer()->set(CategoryServiceInterface::class, $categoryService);
//        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
//        $this->httpClient->loginUser($adminUser);
//
//        // when
//        $route = self::TEST_ROUTE . '/' . $expectedCategory->getId() . '/edit';
//        $this->httpClient->request('POST', $route, [
//            'title' => 'Updated category title',
//            '_method' => 'PUT', // Symfony's method override to handle PUT requests
//        ]);
//
//        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
//
//        // then
//        $this->assertEquals($expectedStatusCode, $actualStatusCode);
////        $this->assertResponseRedirects($this->urlGenerator->generate('category_index'));
//
////        // Verify that the category title has been updated
////        $updatedCategory = $categoryService->findOneById($testCategoryId);
////        $this->assertEquals('Updated category title', $updatedCategory->getTitle());
//    }

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
    /**
     * Test delete category when can't be deleted.
     */
    public function testDeleteCategoryCantBeDeleted(): void
    {
        // given
        $expectedStatusCode = 302;
        $testCategoryId = 123;
        $expectedCategory = $this->createCategory();
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
        $createdNote = $this->createNote($expectedCategory, $adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId().'/delete');
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }


    /**
     * Test delete category.
     */
    public function testDeleteCategoryForm(): void
    {
        // given
        $expectedStatusCode = 302;
        $expectedCategory = $this->createCategory();
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);
//        $categoryService = $this->createMock(CategoryServiceInterface::class);
//        $categoryService->expects($this->once())
//            ->method('findOneById')
//            ->with($testCategoryId)
//            ->willReturn($expectedCategory);
//        $categoryService->expects($this->once())
//            ->method('categoryExists')
//            ->with($testCategoryId)
//            ->willReturn(true);
//        $categoryService->expects($this->once())
//            ->method('canBeDeleted')
//            ->with($testCategoryId)
//            ->willReturn(true);
//        static::getContainer()->set(CategoryServiceInterface::class, $categoryService);
        $route = self::TEST_ROUTE . '/' . $expectedCategory->getId() . '/delete';
        $this->httpClient->request('GET', $route);
        $this->httpClient->submitForm(
            'Usuń'
        );
        // then
        $actualStatusCode = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }
}
