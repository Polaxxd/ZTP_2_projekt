<?php
///**
// * Task controller tests.
// */
//
//namespace App\Tests\Controller;
//
//use App\Entity\Enum\UserRole;
//use App\Entity\User;
//use App\Repository\UserRepository;
//use Generator;
//use http\Cookie;
//use Psr\Container\ContainerExceptionInterface;
//use Psr\Container\NotFoundExceptionInterface;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//
///**
// * class TaskControllerTest.
// */
//class TaskControllerTest extends WebTestCase
//{
////    /**
////     * Test '/task' route.
////     */
////    public function testTaskListRoute(): void
////    {
////        // given
////        $client = static::createClient();
////
////        // when
////        $client->request('GET', '/task');
////        $resultHttpStatusCode = $client->getResponse()->getStatusCode();
////
////        // then
////        $this->assertEquals(200, $resultHttpStatusCode);
////    }
//
//    /**
//     * Test route.
//     *
//     * @const string
//     */
//    public const TEST_ROUTE = '/task';
//
//    /**
//     * Set up tests.
//     */
//    public function setUp(): void
//    {
//        $this->httpClient = static::createClient();
//    }
//
//
//    /**
//     * Simulate user log in.
//     *
//     * @param User $user User entity
//     *
//     * @throws ContainerExceptionInterface
//     * @throws NotFoundExceptionInterface
//     */
////    tu powinna być funkcja sprawdzająca logowanie usera
//
//    /**
//     * Create user.
//     *
//     * @return void User entity
//     *
//     * @throws ContainerExceptionInterface
//     * @throws NotFoundExceptionInterface
//     */
//    protected function createAndLoginUser(string $email): void
//    {
//        try {
//            $passwordHasher = static::getContainer()->get('security.password_hasher');
//        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
//        }
//        $user = new User();
//        $user->setEmail($email);
//        $user->setRoles([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
//        $user->setPassword(
//            $passwordHasher->hashPassword(
//                $user,
//                'user1234'
//            )
//        );
//        $userRepository = null;
//        try {
//            $userRepository = static::getContainer()->get(UserRepository::class);
//        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
//        }
//        $userRepository->save($user);
//        $this->logIn($user);
//    }
//
//    /**
//     * Test index route for admin user.
//     */
//    public function testIndexRouteAdminUser(): void
//    {
//        // given
//        $this->createAndLoginUser('user1@example.com');
//        $expectedStatusCode = 200;
//
//        // when
//        $this->httpClient->request('GET', self::TEST_ROUTE);
//        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
//
//        // then
//        $this->assertEquals($expectedStatusCode, $resultStatusCode);
//    }
//
//
//
//
//}
