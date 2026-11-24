<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Repository\HallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ListQueryService;

#[Route('/halls')]
final class HallController extends AbstractController
{
    public function __construct(
    private readonly HallRepository $hallRepository,
    private readonly EntityManagerInterface $em,
    private readonly ListQueryService $listQuery,
) {
}

    #[Route('', name: 'hall_index', methods: ['GET'])]
public function index(Request $request): JsonResponse
{
    $result = $this->listQuery->paginate(Hall::class, $request, [
        'id' => ['id', 'eq'],
        'name' => ['name', 'like'],
        'capacity' => ['capacity', 'eq'],
    ]);

    return new JsonResponse([
        'data' => array_map(fn (Hall $h) => $this->toArray($h), $result['items']),
        'meta' => $result['meta'],
    ], Response::HTTP_OK);
}
    #[Route('/{id}', name: 'hall_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $hall = $this->hallRepository->find($id);

        if (!$hall) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $this->toArray($hall)], Response::HTTP_OK);
    }

    #[Route('', name: 'hall_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || !isset($data['capacity'])) {
            return new JsonResponse(['data' => ['error' => 'name and capacity are required']], Response::HTTP_BAD_REQUEST);
        }

        $hall = (new Hall())->setName($data['name'])->setCapacity((int) $data['capacity']);
        $this->em->persist($hall);
        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($hall)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'hall_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $hall = $this->hallRepository->find($id);

        if (!$hall) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['name'])) {
            $hall->setName($data['name']);
        }
        if (isset($data['capacity'])) {
            $hall->setCapacity((int) $data['capacity']);
        }

        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($hall)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'hall_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $hall = $this->hallRepository->find($id);

        if (!$hall) {
            return $this->notFound($id);
        }

        $this->em->remove($hall);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function toArray(Hall $hall): array
    {
        return ['id' => $hall->getId(), 'name' => $hall->getName(), 'capacity' => $hall->getCapacity()];
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => 'Not found hall by id ' . $id]], Response::HTTP_NOT_FOUND);
    }
}