# PPAW — Laborator 10 (Exercițiul 3): Durata de viață a obiectelor injectate (Singleton, Scoped, Transient)

Acest document prezintă modul de configurare și comutare între cele 3 tipuri de durată de viață (*lifetime*) pentru obiectele injectate prin Container-ul IoC din Laravel (`omulpotrivit`), precum și diferențele dintre ele.

---

## 1. Cum se face comutarea între cele 3 valori în Laravel

Configurarea se face în metoda `register()` din `app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    // 1. TRANSIENT — o instanță nouă la fiecare rezolvare / injectare
    $this->app->bind(ListingService::class);

    // 2. SCOPED — o singură instanță per cerere HTTP (sau job/scope)
    $this->app->scoped(ListingService::class);

    // 3. SINGLETON — o singură instanță pe toată durata de viață a aplicației
    $this->app->singleton(ListingService::class);
}
```

> **Notă (comparație cu .NET / Autofac din enunțul laboratorului):**
> - **Transient:** `$this->app->bind(...)` în Laravel ↔ `.InstancePerDependency()` în Autofac (comportamentul implicit).
> - **Scoped:** `$this->app->scoped(...)` în Laravel ↔ `.InstancePerLifetimeScope()` / `.InstancePerRequest()` în Autofac.
> - **Singleton:** `$this->app->singleton(...)` în Laravel ↔ `.SingleInstance()` în Autofac.

Dacă se folosesc interfețe (ex. `ListingServiceInterface`), se pasează perechea `(Interfata::class, Implementare::class)` ca parametri la `bind()`, `scoped()` sau `singleton()`.

---

## 2. Diferențele dintre Transient, Scoped și Singleton

| Caracteristică | Transient (`bind`) | Scoped (`scoped`) | Singleton (`singleton`) |
| :--- | :--- | :--- | :--- |
| **Când se creează instanța** | De **fiecare dată** când serviciul este injectat sau rezolvat cu `app()->make(...)`. | **O singură dată** per cerere HTTP / scope de execuție. | **O singură dată** în ciclul de viață al containerului aplicației (la prima rezolvare). |
| **Partajare în aceeași cerere HTTP** | **Nu** — două clase care injectează același serviciu primesc **instanțe diferite** (`spl_object_id` diferit). | **Da** — toate clasele din aceeași cerere primesc **aceeași instanță**. | **Da** — toate clasele primesc **aceeași instanță**. |
| **Comportament la cerere nouă / scope nou** | Instanță nouă de fiecare dată. | Se creează o **instanță nouă** (instanța veche este eliminată la finalul cererii sau la `forgetScopedInstances()`). | Se păstrează **aceeași instanță** în procesele long-running (Laravel Octane, Queue Workers, teste). |
| **Când se recomandă** | Servicii ușoare, fără stare partajată, sau când fiecare consumator are nevoie de o stare izolată. | Servicii cu stare specifică unei cereri (context utilizator, unit-of-work, cache per-request) care nu trebuie să „scurgă” între cereri. | Servicii *stateless* costisitoare la inițializare (clienți API externi, configurări, pool-uri de conexiuni). Nu stocați date specifice unui utilizator într-un Singleton. |

> **Particularitate PHP / Laravel:** În modul clasic PHP-FPM (`php artisan serve`), fiecare cerere HTTP pornește un proces PHP nou, deci între două cereri HTTP separate `Scoped` și `Singleton` par identice. Diferența devine vizibilă în **aceeași cerere** (vs. `Transient`), respectiv în **procese persistente** (Queue Workers, Laravel Octane) sau la apelarea `app()->forgetScopedInstances()` în teste, unde `Scoped` se resetează, iar `Singleton` rămâne neschimbat.

---

## 3. Verificare practică (PHPUnit)

Testele automate din `tests/Unit/ServiceLifetimeTest.php` verifică toate cele 3 moduri folosind `spl_object_id()` pe `App\Services\CategoryService`:

1. `test_transient_binding_creates_new_instance_every_time` — confirmă că `bind()` produce ID-uri de obiect diferite la două apeluri consecutive.
2. `test_scoped_binding_shares_instance_within_scope_and_resets_across_scopes` — confirmă că `scoped()` returnează același ID în același scope și un ID nou după `$this->app->forgetScopedInstances()`.
3. `test_singleton_binding_shares_instance_across_scopes` — confirmă că `singleton()` păstrează același ID chiar și după `$this->app->forgetScopedInstances()`.

Comanda de rulare:
```powershell
php artisan test --filter=ServiceLifetimeTest
```

Rezultat observat:
- **3 passed (4 assertions)** (`Tests\Unit\ServiceLifetimeTest`).
