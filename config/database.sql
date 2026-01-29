/* ============================================================
   Library DB + Books tábla (MS SQL Server)
   ============================================================ */

-- 1) DB létrehozás (ha még nem létezik)
IF DB_ID(N'Library') IS NULL
BEGIN
    CREATE DATABASE [Library];
END
GO

USE [Library];
GO

-- 2) Tábla létrehozás (ha már létezik, nem csinál semmit)
IF OBJECT_ID(N'dbo.Books', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Books
    (
        ID           INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Books PRIMARY KEY,
        Title        NVARCHAR(200)      NOT NULL,
        Author       NVARCHAR(150)      NOT NULL,
        PublishYear  INT                NULL,
        IsAvailable  BIT                NOT NULL CONSTRAINT DF_Books_IsAvailable DEFAULT (1),

        CreatedAt    DATETIME2(0)       NOT NULL CONSTRAINT DF_Books_CreatedAt DEFAULT (SYSUTCDATETIME()),
        UpdatedAt    DATETIME2(0)       NULL,

        CONSTRAINT CK_Books_PublishYear
            CHECK (PublishYear IS NULL OR (PublishYear BETWEEN 1400 AND YEAR(GETDATE()) + 1))
    );
END
GO

-- 3) Hasznos indexek listázáshoz/kereséshez
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_Books_Author' AND object_id = OBJECT_ID(N'dbo.Books'))
    CREATE INDEX IX_Books_Author ON dbo.Books(Author);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_Books_Title' AND object_id = OBJECT_ID(N'dbo.Books'))
    CREATE INDEX IX_Books_Title ON dbo.Books(Title);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_Books_IsAvailable' AND object_id = OBJECT_ID(N'dbo.Books'))
    CREATE INDEX IX_Books_IsAvailable ON dbo.Books(IsAvailable);
GO

-- 4) UpdatedAt automatikus frissítése UPDATE-nél (trigger)
IF OBJECT_ID(N'dbo.TR_Books_SetUpdatedAt', N'TR') IS NULL
EXEC (N'
CREATE TRIGGER dbo.TR_Books_SetUpdatedAt
ON dbo.Books
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE b
        SET UpdatedAt = SYSUTCDATETIME()
    FROM dbo.Books b
    INNER JOIN inserted i ON i.ID = b.ID;
END
');
GO

-- 5) (Opcionális) Minta adatok
IF NOT EXISTS (SELECT 1 FROM dbo.Books)
BEGIN
    INSERT INTO dbo.Books (Title, Author, PublishYear, IsAvailable)
    VALUES
        (N'A Pál utcai fiúk', N'Molnár Ferenc', 1907, 1),
        (N'Egri csillagok', N'Gárdonyi Géza', 1899, 1),
        (N'1984', N'George Orwell', 1949, 0);
END
GO
