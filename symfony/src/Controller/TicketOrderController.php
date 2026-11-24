<?php

namespace App\Controller;

use App\Entity\TicketOrder;
use App\Repository\CustomerRepository;
use App\Repository\ScreeningRepository;
use App\Repository\TicketOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ListQueryService;

#[Route('/orders')]
final class TicketOrderController extends AbstractController
{
    public function __construct(
    private readonly TicketOrderRepository $orderRepository,
    private readonly CustomerRepository $customerRepository,
    private readonly ScreeningRepository $screeningRepository,
    private readonly EntityManagerInterface $em,
    private readonly ListQueryService $listQuery,
) {
}


    #[Route('', name: 'order_index', methods: ['GET'])]
public function index(Request $request): JsonResponse
{
    $result = $this->listQuery->paginate(TicketOrder::class, $request, [
        'id' => ['id', 'eq'],
        'quantity' => ['quantity', 'eq'],
        'created_at' => ['createdAt', 'date'],
        'customer_id' => ['customer', 'eq'],
        'screening_id' => ['screening', 'eq'],
    ]);

    return new JsonResponse([
        'data' => array_map(fn (TicketOrder $o) => $this->toArray($o), $result['items']),
        'meta' => $result['meta'],
    ], Response::HTTP_OK);
}

    #[Route('/{id}', name: 'order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $this->toArray($order)], Response::HTTP_OK);
    }

    #[Route('', name: 'order_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['quantity'], $data['customer_id'], $data['screening_id'])) {
            return $this->badRequest('quantity, customer_id and screening_id are required');
        }

        $customer = $this->customerRepository->find((int) $data['customer_id']);
        $screening = $this->screeningRepository->find((int) $data['screening_id']);

        if (!$customer) {
            return $this->badRequest('Customer not found');
        }
        if (!$screening) {
            return $this->badRequest('Screening not found');
        }
        if ((int) $data['quantity'] < 1) {
            return $this->badRequest('quantity must be at least 1');
        }

        $order = (new TicketOrder())
            ->setQuantity((int) $data['quantity'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCustomer($customer)
            ->setScreening($screening);

        $this->em->persist($order);
        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($order)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'order_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['quantity'])) {
            if ((int) $data['quantity'] < 1) {
                return $this->badRequest('quantity must be at least 1');
            }
            $order->setQuantity((int) $data['quantity']);
        }
        if (isset($data['customer_id'])) {
            $customer = $this->customerRepository->find((int) $data['customer_id']);
            if (!$customer) {
                return $this->badRequest('Customer not found');
            }
            $order->setCustomer($customer);
        }
        if (isset($data['screening_id'])) {
            $screening = $this->screeningRepository->find((int) $data['screening_id']);
            if (!$screening) {
                return $this->badRequest('Screening not found');
            }
            $order->setScreening($screening);
        }

        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($order)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'order_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->notFound($id);
        }

        $this->em->remove($order);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function toArray(TicketOrder $o): array
    {
        return [
            'id' => $o->getId(),
            'quantity' => $o->getQuantity(),
            'created_at' => $o->getCreatedAt()?->format('Y-m-d H:i:s'),
            'customer' => ['id' => $o->getCustomer()?->getId(), 'name' => $o->getCustomer()?->getName()],
            'screening' => [
                'id' => $o->getScreening()?->getId(),
                'starts_at' => $o->getScreening()?->getStartsAt()?->format('Y-m-d H:i:s'),
            ],
        ];
    }

    private function badRequest(string $message): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => $message]], Response::HTTP_BAD_REQUEST);
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => 'Not found order by id ' . $id]], Response::HTTP_NOT_FOUND);
    }
}