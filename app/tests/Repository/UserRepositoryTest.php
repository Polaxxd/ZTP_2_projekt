<?php
///**
// * User Repository test.
// */
//
//namespace App\Tests\Repository;
//
//use App\Entity\User;
//use App\Repository\UserRepository;
//use Doctrine\Persistence\ManagerRegistry;
//use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\ORM\Mapping\ClassMetadata;
//use PHPUnit\Framework\TestCase;
//use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
//use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
//
//class UserRepositoryTest extends TestCase
//{
//    private $entityManager;
//    private $classMetadata;
//    private $registry;
//    private $userRepository;
//
//    protected function setUp(): void
//    {
//        // Create the mock EntityManager
//        $this->entityManager = $this->createMock(EntityManagerInterface::class);
//
//        // Create the ClassMetadata instance
//        $this->classMetadata = new ClassMetadata(User::class);
//
//        // Mock the ManagerRegistry
//        $this->registry = $this->createMock(ManagerRegistry::class);
//        $this->registry->method('getManager')->willReturn($this->entityManager);
//        $this->registry->method('getManagerForClass')->with(User::class)->willReturn($this->entityManager);
//
//        // Instantiate the UserRepository with the mocked ManagerRegistry and ClassMetadata
//        $this->userRepository = new UserRepository($this->registry, $this->classMetadata);
//    }
//
//    public function testUpgradePasswordThrowsExceptionForUnsupportedUser(): void
//    {
//        $this->expectException(UnsupportedUserException::class);
//
//        $nonUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
//        $this->userRepository->upgradePassword($nonUser, 'newHashedPassword');
//    }
//
//    public function testUpgradePasswordSuccessfully(): void
//    {
//        $user = new User();
//        $newHashedPassword = 'newHashedPassword';
//
//        // Set expectations for the entity manager
//        $this->entityManager->expects($this->once())
//            ->method('persist')
//            ->with($user);
//        $this->entityManager->expects($this->once())
//            ->method('flush');
//
//        // Call the method under test
//        $this->userRepository->upgradePassword($user, $newHashedPassword);
//
//        // Assert that the password was set correctly
//        $this->assertEquals($newHashedPassword, $user->getPassword());
//    }
//}
//
//
//
//
