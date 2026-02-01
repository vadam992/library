<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class BookRepository
{
    public function __construct(private PDO $pdo) {}

    public function list(?string $search = null): array
    {
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

        $stmt = $this->pdo->query("
            SELECT ID, Title, Author, PublishYear, IsAvailable
            FROM dbo.Books
            ORDER BY ID DESC
        ");
        return $stmt->fetchAll();
    }

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

    public function create(string $title, string $author, ?int $publishYear, bool $isAvailable = true): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO dbo.Books (Title, Author, PublishYear, IsAvailable)
            VALUES (:title, :author, :year, :avail)
        ");

        $stmt->execute([
            'title' => $title,
            'author' => $author,
            'year' => $publishYear,              // lehet null
            'avail' => $isAvailable ? 1 : 0,
        ]);

        // SQL Serverben a legbiztosabb:
        $id = (int)$this->pdo->query("SELECT SCOPE_IDENTITY() AS id")->fetch()['id'];
        return $id;
    }

    public function update(int $id, string $title, string $author, ?int $publishYear, bool $isAvailable): bool
    {
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

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM dbo.Books WHERE ID = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Kiegészítő: “kölcsönzés” – csak státuszt állít
    public function setAvailability(int $id, bool $isAvailable): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE dbo.Books
            SET IsAvailable = :avail
            WHERE ID = :id
        ");
        $stmt->execute(['id' => $id, 'avail' => $isAvailable ? 1 : 0]);
        return $stmt->rowCount() > 0;
    }
}
