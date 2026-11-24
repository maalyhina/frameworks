<?php

namespace App\Controller;

use App\Entity\Screening;
use App\Repository\HallRepository;
use App\Repository\MovieRepository;
use App\Repository\ScreeningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ListQueryService;

#[Route('/screenings')]
final class ScreeningController extends AbstractController
{
    public function __construct(
    private readonly ScreeningRepository $screeningRepository,
    private readonly MovieRepository $movieRepository,
    private readonly HallRepository $hallRepository,
    private readonly EntityManagerInterface $em,
    private readonly ListQueryService $listQuery,
) {
}

    #[Route('', name: 'screening_index', methods: ['GET'])]
public function index(Request $request): JsonResponse
{
    $result = $this->listQuery->paginate(Screening::class, $request, [
        'id' => ['id', 'eq'],
        'starts_at' => ['startsAt', 'date'],
        'movie_id' => ['movie', 'eq'],
        'hall_id' => ['hall', 'eq'],
    ]);

    return new JsonResponse([
        'data' => array_map(fn (Screening $s) => $this->toArray($s), $result['items']),
        'meta' => $result['meta'],
    ], Response::HTTP_OK);
}

    #[Route('/{id}', name: 'screening_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $screening = $this->screeningRepository->find($id);

        if (!$screening) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $this->toArray($screening)], Response::HTTP_OK);
    }

    #[Route('', name: 'screening_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['starts_at'], $data['movie_id'], $data['hall_id'])) {
            return $this->badRequest('starts_at, movie_id and hall_id are required');
        }

        $startsAt = $this->parseDate($data['starts_at']);
        $movie = $this->movieRepository->find((int) $data['movie_id']);
        $hall = $this->hallRepository->find((int) $data['hall_id']);

        if (!$startsAt) {
            return $this->badRequest('Invalid starts_at');
        }
        if (!$movie) {
            return $this->badRequest('Movie not found');
        }
        if (!$hall) {
            return $this->badRequest('Hall not found');
        }

        $screening = (new Screening())->setStartsAt($startsAt)->setMovie($movie)->setHall($hall);
        $this->em->persist($screening);
        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($screening)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'screening_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $screening = $this->screeningRepository->find($id);

        if (!$screening) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['starts_at'])) {
            $startsAt = $this->parseDate($data['starts_at']);
            if (!$startsAt) {
                return $this->badRequest('Invalid starts_at');
            }
            $screening->setStartsAt($startsAt);
        }
        if (isset($data['movie_id'])) {
            $movie = $this->movieRepository->find((int) $data['movie_id']);
            if (!$movie) {
                return $this->badRequest('Movie not found');
            }
            $screening->setMovie($movie);
        }
        if (isset($data['hall_id'])) {
            $hall = $this->hallRepository->find((int) $data['hall_id']);
            if (!$hall) {
                return $this->badRequest('Hall not found');
            }
            $screening->setHall($hall);
        }

        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($screening)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'screening_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $screening = $this->screeningRepository->find($id);

        if (!$screening) {
            return $this->notFound($id);
        }

        $this->em->remove($screening);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function toArray(Screening $s): array
    {
        return [
            'id' => $s->getId(),
            'starts_at' => $s->getStartsAt()?->format('Y-m-d H:i:s'),
            'movie' => ['id' => $s->getMovie()?->getId(), 'title' => $s->getMovie()?->getTitle()],
            'hall' => ['id' => $s->getHall()?->getId(), 'name' => $s->getHall()?->getName()],
        ];
    }

    private function badRequest(string $message): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => $message]], Response::HTTP_BAD_REQUEST);
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => 'Not found screening by id ' . $id]], Response::HTTP_NOT_FOUND);
    }
}