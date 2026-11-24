<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class ListQueryService
{
    private const DEFAULT_ITEMS_PER_PAGE = 10;
    private const MAX_ITEMS_PER_PAGE = 100;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param class-string $entityClass
     * @param array<string, array{0: string, 1: string}> $filters  параметр запиту => [поле сутності, тип: like|eq|date]
     *
     * @return array{items: array, meta: array}
     */
    public function paginate(string $entityClass, Request $request, array $filters): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->orderBy('e.id', 'ASC');

        foreach ($filters as $param => [$field, $type]) {
            $value = $request->query->get($param);

            if ($value === null || $value === '') {
                continue;
            }

            $key = 'p_' . $param;

            switch ($type) {
                case 'like':
                    $qb->andWhere("e.$field LIKE :$key")->setParameter($key, '%' . $value . '%');
                    break;

                case 'date':
                    try {
                        $from = (new \DateTimeImmutable((string) $value))->setTime(0, 0);
                    } catch (\Exception) {
                        break;
                    }
                    $qb->andWhere("e.$field >= :{$key}_from AND e.$field < :{$key}_to")
                        ->setParameter($key . '_from', $from)
                        ->setParameter($key . '_to', $from->modify('+1 day'));
                    break;

                default: // eq
                    $qb->andWhere("e.$field = :$key")->setParameter($key, $value);
            }
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = (int) $request->query->get('itemsPerPage', self::DEFAULT_ITEMS_PER_PAGE);
        $perPage = min(self::MAX_ITEMS_PER_PAGE, max(1, $perPage));

        $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);

        $paginator = new Paginator($qb);
        $total = count($paginator);

        return [
            'items' => iterator_to_array($paginator),
            'meta' => [
                'page' => $page,
                'itemsPerPage' => $perPage,
                'totalItems' => $total,
                'totalPages' => (int) ceil($total / $perPage),
            ],
        ];
    }
}