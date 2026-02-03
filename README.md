# Library – Könyvtári nyilvántartó rendszer

Egyszerű könyvtári nyilvántartó rendszer PHP backenddel, Microsoft SQL Server adatbázissal és JavaScript-alapú frontenddel.  
A projekt célja egy CRUD-alapú REST API és egy reszponzív webes felület megvalósítása, megfelelő dokumentációval és automatizált API tesztekkel.

---

## Funkciók

- Könyvek listázása
- Új könyv hozzáadása
- Könyv adatainak szerkesztése
- Könyv törlése
- Könyvek keresése cím és szerző alapján
- Könyv kölcsönzése és visszahozása
- REST API + AJAX kommunikáció
- Automatizált API tesztelés Postmannel

---

## Rendszerarchitektúra

A projekt framework nélküli, **réteges architektúrát** alkalmaz.

```

HTTP Request
↓
Controller
↓
Repository
↓
PDO (SQL Server)
↓
Database

```

### Főbb rétegek

- **Controller** – HTTP kérések kezelése, validáció, válaszok
- **Repository** – adatbázis-műveletek (CRUD)
- **Database** – PDO-alapú SQL Server kapcsolat
- **Frontend** – HTML, CSS, JavaScript (AJAX)

---

## Projekt struktúra

```

library/
├── public/
│   ├── index.php
│   ├── app.html
│   └── assets/
│       ├── styles.css
│       └── app.js
│
├── src/
│   ├── Controllers/
│   │   └── BookController.php
│   ├── Database/
│   │   └── Db.php
│   ├── Http/
│   │   ├── Request.php
│   │   └── Response.php
│   └── Repositories/
│       └── BookRepository.php
│
├── config/
│   ├── config.php
│   ├── database.sql
│   └── Env.php
│
├── test/
│   └── postman_library_collection.json
│
├── .env
└── README.md

```

---

## Adatbázis

- **Adatbázis:** Microsoft SQL Server
- **Adatbázis neve:** `Library`
- **Tábla:** `Books`

### Books tábla mezők

| Mező        | Típus          | Leírás           |
| ----------- | -------------- | ---------------- |
| ID          | INT (IDENTITY) | Elsődleges kulcs |
| Title       | NVARCHAR       | Könyv címe       |
| Author      | NVARCHAR       | Szerző           |
| PublishYear | INT            | Kiadás éve       |
| IsAvailable | BIT            | Elérhetőség      |

---

## Konfiguráció (.env)

```env
APP_ENV=local
APP_DEBUG=1

DB_HOST=localhost
DB_PORT=
DB_NAME=Library
DB_USER=sa
DB_PASS=YourStrongPassword

DB_ENCRYPT=0
DB_TRUST_SERVER_CERT=1
```

⚠️ A `.env` fájl nem kerül feltöltésre verziókezelésbe.

---

## Telepítés és futtatás (Local)

### Követelmények

- Visual Studio Code
- PHP 8.x
- Microsoft SQL Server
- PDO SQLSRV driver
- XAMPP
- Postman

### Local futtatás XAMPP + VirtualHost segítségével

A projekt XAMPP környezetben, Apache webszerveren, VirtualHost használatával lett futtatva.

### Hosts fájl módosítása

Windows alatt a következő sort kell hozzáadni a hosts fájlhoz:

```
C:\Windows\System32\drivers\etc\hosts
```

```
127.0.0.1   library.local
```

### Apache VirtualHost konfiguráció

Az alábbi VirtualHost konfiguráció került hozzáadásra az Apache beállításaihoz
(pl. C:\xampp\apache\conf\extra\httpd-vhosts.conf):

```
<VirtualHost *:80>
    ServerName library.local
    DocumentRoot "C:/xampp/htdocs/library/public"

    <Directory "C:/xampp/htdocs/library/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Magyarázat:

- ServerName: helyi domain név

- DocumentRoot: a projekt public mappája

- AllowOverride All: .htaccess engedélyezése

- Require all granted: hozzáférés engedélyezése

### Apache újraindítása

A módosítások után az Apache webszervert újra kell indítani a XAMPP Control Panelben.

### Frontend elérése

```
http://library.local/app.html
```

### API elérés

```
http://library.local/api/books
```

### Health check

```
http://library.local/health
```

---

## API végpontok

| Method | Endpoint               | Leírás            |
| ------ | ---------------------- | ----------------- |
| GET    | /health                | API állapot       |
| GET    | /api/books             | Könyvek listázása |
| GET    | /api/books?search=     | Keresés           |
| POST   | /api/books             | Új könyv          |
| GET    | /api/books/{id}        | Könyv lekérése    |
| PUT    | /api/books/{id}        | Könyv frissítése  |
| DELETE | /api/books/{id}        | Könyv törlése     |
| POST   | /api/books/{id}/borrow | Kölcsönzés        |
| POST   | /api/books/{id}/return | Visszahozás       |

---

## API tesztelés

A projekt tartalmaz egy Postman kollekciót:

- **Fájl:** `postman_library_collection.json`
- **Kollekció neve:** `Library API (Books CRUD) - Local`

A kollekció:

- automatizált teszteket tartalmaz
- egymásra épülő CRUD lépéseket futtat
- a létrehozott könyv ID-ját dinamikusan kezeli

A `baseUrl` változó módosítható a helyi környezetnek megfelelően.
