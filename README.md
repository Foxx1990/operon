# Moduł Symfony: Dopasowywanie Szkół

Projekt to moduł oparty na Symfony 7, służący do dopasowywania nazw szkół wprowadzonych przez użytkownika do bazy danych, obsługujący literówki, aliasy i różne warianty zapisu.

## Założenia
- **Baza Danych**: PostgreSQL (wszystkie szkoły są przechowywane i indeksowane w bazie).
- **Logika Dopasowania**:
    1. **Dopasowanie dokładne** (Oficjalna nazwa lub Alias).
    2. **Dopasowanie dokładne bez względu na wielkość liter** (Case-insensitive).
    3. **Odległość Levenshteina** (Fuzzy match) z dynamicznym progiem (30% długości lub max 3 znaki).
    
- **Środowisko**: Docker (PHP 8.4, PostgreSQL 16, Nginx).
- **Standard**: API Platform integration dla pełnej dokumentacji i standardu REST.

## Jak uruchomić

1. **Uruchom kontenery**:
   ```bash
   docker compose up -d --build
   ```

2. **Zainstaluj zależności PHP**:
   ```bash
   docker compose exec php composer install
   ```

3. **Przygotuj bazę danych i importuj dane**:
   ```bash
   docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
   docker compose exec php bin/console app:import-schools
   ```

3. **Uruchom testy**:
   ```bash
   docker compose exec php bin/phpunit
   ```

## Użycie API

### 1. Dokumentacja interaktywna (Swagger UI)
Pełna lista endpointów i możliwość testowania "na żywo":
**URL**: [http://localhost:8080/api](http://localhost:8080/api)

### 2. Dopasowywanie szkoły (Zoptymalizowane)
To jest główny punkt wejścia dla logiki rozmytej.
**Endpoint**: `POST /api/schools/match`
**Headers**: `Content-Type: application/json`

```bash
curl -X POST http://localhost:8080/api/schools/match \
     -H "Content-Type: application/json" \
     -d '{"schoolName": "V LO"}'
```

*(Dostępny jest również alias pod adresem `POST /api/match-school`)*

### 3. Przeglądanie danych (API Platform)
Standardowe endpointy REST do zarządzania listą szkół.

- **Lista szkół**: `GET /api/schools`
- **Szczegóły szkoły**: `GET /api/schools/{id}`

Przykład pobrania listy:
```bash
curl -H "Accept: application/ld+json" http://localhost:8080/api/schools
```

### Przykładowa odpowiedź (Dopasowanie)

```json
{
  "matched": true,
  "school": {
    "name": "Liceum Ogólnokształcące nr 5 im. Józefa Wybickiego",
    "aliases": ["V LO", "Piąte LO", "Wybicki", "LO 5"],
    "city": "Kraków",
    "type": "liceum"
  }
}
```

## Architektura
- **Clean Architecture / DDD**: Logika biznesowa jest odizolowana w `src/Domain`, niezależnie od frameworka i bazy danych.
- **Wzorzec Repozytorium**: `SchoolRepositoryInterface` pozwala na łatwą zmianę źródła danych (np. migracja z pliku JSON na SQL została wykonana bez zmiany logiki domenowej).
- **Optymalizacja wydajności (Level 1)**:
    - Normalizacja danych przy imporcie (pole `search_terms` w JSONB).
    - Early exit dla dopasowań dokładnych.
    - Filtrowanie długości przed kosztownymi operacjami `levenshtein()`.

## Dalszy rozwój i skalowalność

Obecne rozwiązanie oparte na PostgreSQL jest bardzo wydajne dla tysięcy rekordów. W przypadku skali globalnej:

1. **Silnik Full-Text Search**: Integracja z Elasticsearch lub Meilisearch dla jeszcze lepszej tolerancji błędów i rankingu (np. promowanie oficjalnych nazw nad aliasami).
2. **CQRS**: Rozdzielenie modelu zapisu od szybkich modeli odczytu zoptymalizowanych pod wyszukiwanie.
3. **Caching**: Wykorzystanie Redis do przechowywania wyników najpopularniejszych zapytań użytkowników.

