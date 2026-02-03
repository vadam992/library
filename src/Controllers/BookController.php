<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Repositories\BookRepository;

/**
 * BookController
 *
 * A könyvekhez tartozó HTTP kérések kezelése.
 * Feladata:
 * - request adatok validálása
 * - repository hívása
 * - JSON válaszok és HTTP státuszkódok visszaadása
 */
final class BookController
{
    public function __construct(private BookRepository $repo) {}

    /**
     * Könyvek listázása.
     * Opcionálisan keresési paramétert is fogad (cím vagy szerző alapján).
     *
     * GET /api/books
     * GET /api/books?search=...
     */
    public function index(?string $search = null): void
    {
        $books = $this->repo->list($search);
        Response::json(['data' => $books]);
    }

    /**
     * Egy könyv lekérdezése azonosító alapján.
     *
     * GET /api/books/{id}
     */
    public function show(int $id): void
    {
        $book = $this->repo->find($id);

        // Ha nem létezik a könyv, 404-es hibát adunk vissza
        if (!$book) {
            Response::error('Book not found', 404);
        }

        Response::json(['data' => $book]);
    }

    /**
     * Új könyv létrehozása.
     *
     * POST /api/books
     * Kötelező mezők: title, author
     */
    public function store(array $data): void
    {
        // Beérkező adatok normalizálása
        $title = trim((string)($data['title'] ?? ''));
        $author = trim((string)($data['author'] ?? ''));
        $yearRaw = $data['publishYear'] ?? null;
        $availRaw = $data['isAvailable'] ?? true;

        // Kötelező mezők ellenőrzése
        if ($title === '' || $author === '') {
            Response::error('Title and Author are required', 422);
        }

        // PublishYear validálása (ha meg van adva)
        $publishYear = null;
        if ($yearRaw !== null && $yearRaw !== '') {
            if (!is_numeric($yearRaw)) {
                Response::error('PublishYear must be a number', 422);
            }
            $publishYear = (int)$yearRaw;
        }

        // IsAvailable boolean értékké alakítása
        $isAvailable = filter_var($availRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isAvailable === null) {
            $isAvailable = true;
        }

        // Könyv létrehozása
        $id = $this->repo->create($title, $author, $publishYear, $isAvailable);
        $created = $this->repo->find($id);

        Response::json(['data' => $created], 201);
    }

    /**
     * Könyv adatainak frissítése.
     *
     * PUT /api/books/{id}
     * Csak a megadott mezők módosulnak.
     */
    public function update(int $id, array $data): void
    {
        // Meglévő könyv ellenőrzése
        $existing = $this->repo->find($id);
        if (!$existing) {
            Response::error('Book not found', 404);
        }

        // Ha egy mező nincs megadva, a régi érték marad
        $title = trim((string)($data['title'] ?? $existing['Title']));
        $author = trim((string)($data['author'] ?? $existing['Author']));
        $yearRaw = $data['publishYear'] ?? $existing['PublishYear'];
        $availRaw = $data['isAvailable'] ?? $existing['IsAvailable'];

        if ($title === '' || $author === '') {
            Response::error('Title and Author are required', 422);
        }

        // PublishYear validálása
        $publishYear = null;
        if ($yearRaw !== null && $yearRaw !== '') {
            if (!is_numeric($yearRaw)) {
                Response::error('PublishYear must be a number', 422);
            }
            $publishYear = (int)$yearRaw;
        }

        // IsAvailable érték konvertálása booleanre
        $isAvailable = filter_var($availRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isAvailable === null) {
            $isAvailable = ((int)$existing['IsAvailable']) === 1;
        }

        // Adatbázis frissítés
        $ok = $this->repo->update($id, $title, $author, $publishYear, $isAvailable);
        if (!$ok) {
            Response::error('Nothing updated', 409);
        }

        $updated = $this->repo->find($id);
        Response::json(['data' => $updated]);
    }

    /**
     * Könyv törlése.
     *
     * DELETE /api/books/{id}
     */
    public function destroy(int $id): void
    {
        $ok = $this->repo->delete($id);

        // Nem létező könyv esetén 404
        if (!$ok) {
            Response::error('Book not found', 404);
        }

        Response::json(['deleted' => true]);
    }

    /**
     * Könyv kölcsönzése.
     *
     * POST /api/books/{id}/borrow
     * Idempotens működés: ha már kölcsönzött, nem hibázik.
     */
    public function borrow(int $id): void
    {
        $book = $this->repo->find($id);
        if (!$book) {
            Response::error('Book not found', 404);
        }

        // Ha már kölcsönzött, visszaadjuk az aktuális állapotot
        if ((int)$book['IsAvailable'] === 0) {
            Response::json([
                'data' => $book,
                'message' => 'Book is already borrowed'
            ], 200);
        }

        // Elérhetőség frissítése
        $this->repo->setAvailability($id, false);
        $updated = $this->repo->find($id);

        Response::json([
            'data' => $updated,
            'message' => 'Book borrowed'
        ], 200);
    }

    /**
     * Könyv visszahozása.
     *
     * POST /api/books/{id}/return
     * Szintén idempotens működésű.
     */
    public function giveBack(int $id): void
    {
        $book = $this->repo->find($id);
        if (!$book) {
            Response::error('Book not found', 404);
        }

        // Ha már elérhető, nincs további teendő
        if ((int)$book['IsAvailable'] === 1) {
            Response::json([
                'data' => $book,
                'message' => 'Book is already available'
            ], 200);
        }

        // Elérhetőség visszaállítása
        $this->repo->setAvailability($id, true);

        Response::json([
            'data' => $this->repo->find($id),
            'message' => 'Book returned'
        ], 200);
    }

}
