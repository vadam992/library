<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Repositories\BookRepository;

final class BookController
{
    public function __construct(private BookRepository $repo) {}

    public function index(?string $search = null): void
    {
        $books = $this->repo->list($search);
        Response::json(['data' => $books]);
    }

    public function show(int $id): void
    {
        $book = $this->repo->find($id);
        if (!$book) {
            Response::error('Book not found', 404);
        }
        Response::json(['data' => $book]);
    }

    public function store(array $data): void
    {
        $title = trim((string)($data['title'] ?? ''));
        $author = trim((string)($data['author'] ?? ''));
        $yearRaw = $data['publishYear'] ?? null;
        $availRaw = $data['isAvailable'] ?? true;

        if ($title === '' || $author === '') {
            Response::error('Title and Author are required', 422);
        }

        $publishYear = null;
        if ($yearRaw !== null && $yearRaw !== '') {
            if (!is_numeric($yearRaw)) Response::error('PublishYear must be a number', 422);
            $publishYear = (int)$yearRaw;
        }

        $isAvailable = filter_var($availRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isAvailable === null) $isAvailable = true;

        $id = $this->repo->create($title, $author, $publishYear, $isAvailable);
        $created = $this->repo->find($id);

        Response::json(['data' => $created], 201);
    }

    public function update(int $id, array $data): void
    {
        $existing = $this->repo->find($id);
        if (!$existing) Response::error('Book not found', 404);

        $title = trim((string)($data['title'] ?? $existing['Title']));
        $author = trim((string)($data['author'] ?? $existing['Author']));
        $yearRaw = $data['publishYear'] ?? $existing['PublishYear'];
        $availRaw = $data['isAvailable'] ?? $existing['IsAvailable'];

        if ($title === '' || $author === '') {
            Response::error('Title and Author are required', 422);
        }

        $publishYear = null;
        if ($yearRaw !== null && $yearRaw !== '') {
            if (!is_numeric($yearRaw)) Response::error('PublishYear must be a number', 422);
            $publishYear = (int)$yearRaw;
        }

        // IsAvailable DB-ből jöhet 0/1 stringként is
        $isAvailable = filter_var($availRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isAvailable === null) $isAvailable = ((int)$existing['IsAvailable']) === 1;

        $ok = $this->repo->update($id, $title, $author, $publishYear, $isAvailable);
        if (!$ok) Response::error('Nothing updated', 409);

        $updated = $this->repo->find($id);
        Response::json(['data' => $updated]);
    }

    public function destroy(int $id): void
    {
        $ok = $this->repo->delete($id);
        if (!$ok) Response::error('Book not found', 404);
        Response::json(['deleted' => true]);
    }

    // Kiegészítő: borrow/return
    public function borrow(int $id): void
    {
        $book = $this->repo->find($id);
        if (!$book) {
            Response::error('Book not found', 404);
        }

        // Ha már kölcsönzött, nem hibázunk
        if ((int)$book['IsAvailable'] === 0) {
            Response::json([
                'data' => $book,
                'message' => 'Book is already borrowed'
            ], 200);
        }

        $this->repo->setAvailability($id, false);
        $updated = $this->repo->find($id);

        Response::json([
            'data' => $updated,
            'message' => 'Book borrowed'
        ], 200);
    }


    public function giveBack(int $id): void
    {
        $book = $this->repo->find($id);
        if (!$book) Response::error('Book not found', 404);

        if ((int)$book['IsAvailable'] === 1) {
            Response::json(['data' => $book, 'message' => 'Book is already available'], 200);
        }

        $this->repo->setAvailability($id, true);
        Response::json(['data' => $this->repo->find($id), 'message' => 'Book returned'], 200);
    }

}
