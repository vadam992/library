<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * BookRepository
 *
 * A Books tábla adatbázis-műveleteiért felelős osztály.
 * Feladata:
 * - CRUD műveletek végrehajtása
 * - keresési logika kezelése
 * - adatbázis-specifikus részletek elrejtése a controller elől
 */
final class BookRepository
{
    /**
     * @param PDO $pdo Aktív adatbázis-kapcsolat
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Könyvek listázása.
     *
     * Ha keresési kifejezés van megadva, akkor cím vagy szerző alapján
     * szűrt eredményt ad vissza.
     *
     * @param string|null $search Opcionális keresési kifejezés
     * @return array Könyvek listája
     */
    public function list(?string $search = null): array
    {
        // Keresés cím vagy szerző alapján
        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';

            $stmt = $this->pdo->prepare("
                SELECT ID, Title, Author, PublishYear, IsAvailable
                FROM dbo.Books
                WHERE Title LIKE :q1 OR Author LIKE :q2
                ORDER BY ID DESC
            ");
            $stmt->execute([
                'q1' => $q,
                'q2' => $q,
            ]);

            return $stmt->fetchAll();
        }

        // Teljes lista lekérése
        $stmt = $this->pdo->query("
            SELECT ID, Title, Author, PublishYear, IsAvailable
            FROM dbo.Books
            ORDER BY ID DESC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Egy könyv lekérése azonosító alapján.
     *
     * @param int $id Könyv azonosító
     * @return array|null A könyv adatai vagy null, ha nem létezik
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT ID, Title, Author, PublishYear, IsAvailable
            FROM dbo.Books
            WHERE ID = :id
        ");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Új könyv létrehozása.
     *
     * @param string   $title
     * @param string   $author
     * @param int|null $publishYear Kiadás éve (opcionális)
     * @param bool     $isAvailable Elérhetőség
     * @return int A létrehozott könyv azonosítója
     */
    public function create(string $title, string $author, ?int $publishYear, bool $isAvailable = true): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO dbo.Books (Title, Author, PublishYear, IsAvailable)
            OUTPUT INSERTED.ID
            VALUES (:title, :author, :year, :avail)
        ");

        $stmt->execute([
            'title'  => $title,
            'author' => $author,
            'year'   => $publishYear,
            'avail'  => $isAvailable ? 1 : 0,
        ]);

        $id = (int)$stmt->fetchColumn();

        if ($id <= 0) {
            throw new \RuntimeException('Insert succeeded but failed to retrieve inserted ID.');
        }

        return $id;
    }

    /**
     * Könyv adatainak frissítése.
     *
     * @param int      $id
     * @param string   $title
     * @param string   $author
     * @param int|null $publishYear
     * @param bool     $isAvailable
     * @return bool Igaz, ha történt módosítás
     */
    public function update(
        int $id,
        string $title,
        string $author,
        ?int $publishYear,
        bool $isAvailable
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE dbo.Books
            SET Title = :title,
                Author = :author,
                PublishYear = :year,
                IsAvailable = :avail
            WHERE ID = :id
        ");

        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'author' => $author,
            'year' => $publishYear,
            'avail' => $isAvailable ? 1 : 0,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Könyv törlése azonosító alapján.
     *
     * @param int $id
     * @return bool Igaz, ha történt törlés
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM dbo.Books WHERE ID = :id
        ");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Könyv elérhetőségi státuszának módosítása.
     *
     * Kiegészítő funkció kölcsönzés / visszahozás kezelésére.
     *
     * @param int  $id
     * @param bool $isAvailable
     * @return bool Igaz, ha történt módosítás
     */
    public function setAvailability(int $id, bool $isAvailable): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE dbo.Books
            SET IsAvailable = :avail
            WHERE ID = :id
        ");

        $stmt->execute([
            'id' => $id,
            'avail' => $isAvailable ? 1 : 0
        ]);

        return $stmt->rowCount() > 0;
    }
}
