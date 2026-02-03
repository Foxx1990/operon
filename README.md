# Moduł Symfony: Dopasowywanie Szkół

Projekt to moduł oparty na Symfony 7, służący do dopasowywania nazw szkół wprowadzonych przez użytkownika do zdefiniowanej listy, obsługujący literówki, aliasy i różne warianty zapisu.

## Założenia
- **Źródło Danych**: PostgreSQL (dane początkowe zaimportowane z `schools.json`).
- **Logika Dopasowania**:
    1. Dopasowanie dokładne (Oficjalna nazwa lub Alias).
    2. Dopasowanie dokładne bez względu na wielkość liter (Case-insensitive).
    3. Odległość Levenshteina (Fuzzy match) z dynamicznym progiem (30% długości lub max 3 znaki).
    
- **Środowisko**: Docker z PHP 8.4, PostgreSQL 16 oraz Nginx.

## Jak uruchomić

1. **Uruchom aplikację**:
   ```bash
   docker compose up -d --build
   ```

2. **Zainstaluj zależności**:
   ```bash
   docker compose exec php composer install
   ```

3. **Przygotuj bazę danych i importuj dane**:
   ```bash
   docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
   docker compose exec php bin/console app:import-schools
   ```

4. **Dostęp do API**:
   API jest dostępne pod adresem `http://localhost:8080/api`.

## Użycie API

### 1. Dopasowywanie szkoły (Zoptymalizowane)
**Endpoint**: `POST /api/match-school`
**Headers**: `Content-Type: application/json`

Ten endpoint wykorzystuje dedykowaną logikę rozmytą (Levenshtein) i jest zoptymalizowany pod kątem szybkości.

```bash
curl -X POST http://localhost:8080/api/match-school \
     -H "Content-Type: application/json" \
     -d '{"schoolName": "V LO"}'
```

### 2. Przeglądanie danych (API Platform)
Projekt integruje **API Platform**, co daje dostęp do standardowych endpointów REST oraz dokumentacji.

- **Swagger UI**: [http://localhost:8080/api](http://localhost:8080/api)
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

### Przykładowa odpowiedź (Brak dopasowania)

```json
{
  "matched": false,
  "message": "No matching school found."
}
```

## Podejście
- **Czysta Architektura (Clean Architecture)**: oddzielenie Domeny (Encje, Serwisy, Interfejsy Repozytoriów) od Infrastruktury (Implementacja Repozytorium, Kontroler).
- **Wstrzykiwanie Zależności (DI)**: Wykorzystanie autowiringu Symfony oraz atrybutu `#[AsAlias]` do powiązania interfejsu repozytorium.
- **DTO**: Użycie `SchoolMatchRequest` z `#[MapRequestPayload]` dla bezpiecznej obsługi typów i walidacji danych wejściowych.
- **Testy**: Dodano testy jednostkowe (PHPUnit) pokrywające logikę dopasowania dokładnego, aliasów i fuzzy search oraz testy integracyjne API na rzeczywistych danych.

## Dalszy rozwój i skalowalność

Jeśli liczba szkół znacznie wzrośnie (np. do tysięcy lub milionów), obecne rozwiązanie oparte na pamięci operacyjnej RAM (in-memory) będzie wymagało ewolucji. Poniżej przedstawiono rekomendowaną ścieżkę rozwoju:

### 1. Baza Danych z Wyszukiwaniem Rozmytym (PostgreSQL)
Zamiast iterować przez całą listę w PHP (co obciąża procesor i pamięć), należy przenieść ciężar dopasowania na bazę danych.
- **Technologia**: PostgreSQL z rozszerzeniem `pg_trgm` (trigramy).
- **Implementacja**: Zapytania SQL wykorzystujące funkcje podobieństwa tekstu, np. `WHERE similarity(name, :input) > 0.4`.
- **Korzyść**: Natywna, bardzo wydajna obsługa tysięcy rekordów bez potrzeby zewnętrznych serwisów.

### 2. Dedykowany Silnik Wyszukiwania (Elasticsearch / Meilisearch)
Dla kluczowych wymagań biznesowych ("Core Domain") lub bardzo dużych zbiorów danych.
- **Technologia**: Meilisearch (łatwiejszy w konfiguracji) lub Elasticsearch.
- **Korzyść**: Najlepsza na rynku tolerancja błędów, obsługa synonimów oraz zaawansowane algorytmy rankingu wyników (np. promowanie oficjalnych nazw wyżej niż aliasów).

### 3. Ewolucja Architektury
- **CQRS (Command Query Responsibility Segregation)**: Rozdzielenie modelu zapisu (admin dodaje szkoły do bazy SQL) od modelu odczytu (zoptymalizowany indeks w Redis/Elasticsearch służący tylko do wyszukiwania).
- **Cache**: Najczęstsze zapytania mogą być cache'owane w Redis, aby odciążyć silnik wyszukiwania.

Dzięki zastosowaniu **Wzorca Repozytorium (Repository Pattern)** w tym projekcie (`SchoolRepositoryInterface`), przejście na PostgreSQL lub Elasticsearch wymagałoby jedynie stworzenia nowej klasy implementującej interfejs repozytorium, bez konieczności zmian w Kontrolerze czy logice Domenowej.
