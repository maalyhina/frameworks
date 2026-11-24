<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ListQueryService;

#[Route('/customers')]
final class CustomerController extends AbstractController
{
    public function __construct(
    private readonly CustomerRepository $customerRepository,
    private readonly EntityManagerInterface $em,
    private readonly ListQueryService $listQuery,
) {
}

    #[Route('', name: 'customer_index', methods: ['GET'])]
public function index(Request $request): JsonResponse
{
    $result = $this->listQuery->paginate(Customer::class, $request, [
        'id' => ['id', 'eq'],
        'name' => ['name', 'like'],
        'email' => ['email', 'like'],
    ]);

    return new JsonResponse([
        'data' => array_map(fn (Customer $c) => $this->toArray($c), $result['items']),
        'meta' => $result['meta'],
    ], Response::HTTP_OK);
}

    #[Route('/{id}', name: 'customer_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (!$customer) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $this->toArray($customer)], Response::HTTP_OK);
    }

    #[Route('', name: 'customer_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['email'])) {
            return new JsonResponse(['data' => ['error' => 'name and email are required']], Response::HTTP_BAD_REQUEST);
        }

        $customer = (new Customer())->setName($data['name'])->setEmail($data['email']);
        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($customer)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'customer_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (!$customer) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['name'])) {
            $customer->setName($data['name']);
        }
        if (isset($data['email'])) {
            $customer->setEmail($data['email']);
        }

        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($customer)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'customer_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (!$customer) {
            return $this->notFound($id);
        }

        $this->em->remove($customer);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function toArray(Customer $customer): array
    {
        return ['id' => $customer->getId(), 'name' => $customer->getName(), 'email' => $customer->getEmail()];
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $id]], Response::HTTP_NOT_FOUND);
    }
}