# Pași efectuați pentru generarea codului corespunzător entităților (ORM Eloquent / Laravel)

Acest document descrie pașii necesari pentru generarea modelelor (entităților) din baza de date folosind **Eloquent ORM** (Laravel), ca alternativă la abordarea *Database-First* din Entity Framework Core.

---

## 1. Context și Arhitectură (Eloquent vs Entity Framework)

* **Entity Framework (.NET):** Folosește pattern-ul *Data Mapper*. Comanda `dotnet ef dbcontext scaffold` citește schema SQL și generează atât clasele entitate (modelele), cât și clasa centrală `DbContext`.
* **Eloquent ORM (Laravel):** Folosește pattern-ul *Active Record*. Fiecare clasă model (`Category`, `Listing`) moștenește `Illuminate\Database\Eloquent\Model`, încapsulând atât structura datelor, cât și operațiunile de persistență. Conexiunea la baza de date este administrată de `Illuminate\Database\DatabaseManager` (fațada `DB`) pe baza configurării din `config/database.php`.

Pentru generarea codului corespunzător entităților pornind de la o bază de date relațională existentă (MySQL), se parcurg următorii pași:

---

## 2. Pașii efectuați pentru generarea entităților din baza de date

### Pasul 1: Configurarea conexiunii la baza de date
În fișierul `.env` al proiectului Laravel, se configurează parametrii de conectare la serverul MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=omulpotrivit_db
DB_USERNAME=root
DB_PASSWORD=
```

Acești parametri sunt preluați automat în `config/database.php` în secțiunea conexiunii active `'mysql'`.

---

### Pasul 2: Verificarea existenței tabelelor și a cheilor străine în MySQL
Baza de date conține tabelele relaționate:
* Tabela părinte: `categories` (cheie primară: `id`)
* Tabela copil: `listings` (cheie primară: `id`, cheie străină: `category_id` cu referință la `categories(id)`)

---

### Pasul 3: Generarea automată a modelelor din baza de date (Reverse Engineering)

Pentru a genera automat clasele Eloquent direct prin inspecția bazei de date (echivalentul exact al comenzii `scaffold` din EF Core), se utilizează un generator dedicat de reverse-engineering, de exemplu pachetul `reliese/laravel-model-generator`:

1. **Instalarea generatorului:**
   ```bash
   composer require reliese/laravel-model-generator --dev
   ```

2. **Publicarea fișierului de configurare:**
   ```bash
   php artisan vendor:publish --tag=reliese-models
   ```

3. **Executarea comenzii de generare (Scaffold):**
   ```bash
   php artisan code:models --table=categories,listings
   ```

   **Ce realizează acest pas automat:**
   * Se conectează la baza de date `omulpotrivit_db` și citește metadatele din `information_schema`.
   * Identifică coloanele, tipurile de date, cheile primare și constrângerile de cheie străină.
   * Generează în `app/Models/` clasele PHP corespunzătoare:
     * `Category.php`
     * `Listing.php`
   * Adaugă automat array-ul `$fillable`, mapările de tipuri `$casts`, precum și metodele de relație bazate pe cheia străină (`belongsTo` și `hasMany`).

---

### Pasul 4 (Alternativă standard în Laravel): Generarea scheletului prin CLI Artisan + Migrări

Dacă se utilizează generatorul nativ din CLI-ul Laravel (Artisan):

1. **Generarea modelelor și a fișierelor de migrare prin CLI:**
   ```bash
   php artisan make:model Category -m
   php artisan make:model Listing -m
   ```

2. **Definirea schemei tabelelor și a cheii străine în migrări:**
   În fișierul `database/migrations/xxxx_create_listings_table.php`:
   ```php
   $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
   ```

3. **Aplicarea structurii în baza de date:**
   ```bash
   php artisan migrate
   ```

4. **Definirea relațiilor Eloquent în clasele model:**
   * În `app/Models/Category.php`:
     ```php
     public function listings()
     {
         return $this->hasMany(Listing::class);
     }
     ```
   * În `app/Models/Listing.php`:
     ```php
     public function category()
     {
         return $this->belongsTo(Category::class, 'category_id');
     }
     ```

---

## 3. Rezultatul obținut și consumarea entităților în clasele Accessor

După generarea entităților:
1. `app/Models/Category.php` – Modelul entității părinte.
2. `app/Models/Listing.php` – Modelul entității copil, ce conține cheia străină `category_id`.
3. `app/Accessors/CategoryAccessor.php` și `app/Accessors/ListingAccessor.php` – Clasele din Data Access Layer care abstractizează operațiile CRUD și interogările asupra modelelor generate.
