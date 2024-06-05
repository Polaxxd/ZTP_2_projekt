<?php
/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\NoteRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
//    /**
//     * Category repository.
//     */
//    private CategoryRepository $categoryRepository;
//    /**
//     * Task Repository.
//     */
//    private TaskRepository $taskRepository;

    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param CategoryRepository $categoryRepository Category repository
     * @param TaskRepository $taskRepository
     * @param NoteRepository $noteRepository
     * @param PaginatorInterface $paginator Paginator
     */
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TaskRepository $taskRepository,
        private readonly NoteRepository $noteRepository,
        private readonly PaginatorInterface $paginator
    ){
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->categoryRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @param int $id
     * @return Category|null
     */
    public function findOneById(int $id): Category | Null
    {
        $category = $this->categoryRepository->findOneById($id);

        return $category;
    }



//    /**
//     * Save entity.
//     *
//     * @param Category $category Category entity
//     *
//     * @throws ORMException
//     * @throws OptimisticLockException
//     */
    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        $this->categoryRepository->save($category);
    }
    /**
     * Delete entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted($id): bool
    {
        $category = $this->findOneById($id);
        try {
            $taskCount = $this->taskRepository->countByCategory($category);
            $noteCount = $this->noteRepository->countByCategory($category);

            // Check if the category is used in any tasks or notes
            return $taskCount === 0 && $noteCount === 0;
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    /**
     * Does category with this id exist?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function categoryExists($id): bool
    {
        try {
            $categoryCount = $this-> findOneById($id);
            return (!is_null($categoryCount));
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

}
