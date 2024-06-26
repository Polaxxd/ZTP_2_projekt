<?php
/**
 * Note service.
 */

namespace App\Service;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NoteService.
 */
class NoteService implements NoteServiceInterface
{
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
     * Note repository.
     */
    private NoteRepository $noteRepository;

    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param NoteRepository           $noteRepository  Note repository
     * @param PaginatorInterface       $paginator       Paginator
     */
    public function __construct(
        CategoryServiceInterface $categoryService,
        PaginatorInterface $paginator,
        NoteRepository $noteRepository
    ) {
        $this->categoryService = $categoryService;
        $this->noteRepository = $noteRepository;
        $this->paginator = $paginator;
    }


    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->noteRepository->queryByAuthor($author),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void
    {
        $this->noteRepository->save($note);
    }

    /**
     * Delete entity.
     *
     * @param Note $note Note entity
     */
    public function delete(Note $note): void
    {
        $this->noteRepository->delete($note);
    }

    /**
     * Find one by id
     * @param int $id
     * @return Note|null
     */
    public function findOneById(int $id): ?Note
    {
        return $this -> noteRepository->findOneById($id);
    }


//    /**
//     * Prepare filters for the notes list.
//     *
//     * @param array<string, int> $filters Raw filters from request
//     *
//     * @return array<string, object> Result array of filters
//     *
//     * @throws NonUniqueResultException
//     */
//    public function prepareFilters(array $filters): array
//    {
//        $resultFilters = [];
//        if (!empty($filters['category_id'])) {
//            $category = $this->categoryService->findOneById($filters['category_id']);
//            if (null !== $category) {
//                $resultFilters['category'] = $category;
//            }
//        }
//
//        return $resultFilters;
//    }
}