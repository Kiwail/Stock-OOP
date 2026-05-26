# In Stock — noliktavas uzskaites sistēma

Laravel tīmekļa lietotne noliktavas uzskaitei. Projekts paredzēts objektorientētās programmēšanas kursam un demonstrē darbu ar modeļiem, kontrolieriem, servisiem, enum klasēm, middleware, relācijām un testiem.

Sistēma ļauj pārvaldīt preces, firmas noliktavas, atlikumus, dokumentus un preču kustības starp noliktavām.

## Galvenās iespējas

- Lietotāju reģistrācija un pieslēgšanās
- Firmas konteksts katram lietotājam
- Lomas: administrators un noliktavas darbinieks
- Preču katalogs ar iepirkuma un realizācijas cenām
- Noliktavu saraksts katrai firmai
- Atlikumu uzskaite pa noliktavām, FIFO partijām un zonām
- Noliktavas dokumenti:
  - saņemšana
  - norakstīšana
  - pārvietošana
  - realizācija
- Dokumentu melnraksti
- Melnrakstu labošana un dzēšana
- Dokumentu grāmatošana
- Grāmatotu dokumentu atcelšana administratoram
- Preču kustību vēsture no grāmatojumu žurnāla
- CSV eksports precēm, atlikumiem, dokumentiem un kustībām
- Drukājams dokumenta skats
- Dashboard ar kopsavilkumu un analītiku

## Demo konti

| Loma | E-pasts | Parole |
|------|---------|--------|
| Administrators | admin@instock.lv | password |
| Noliktavas darbinieks | operators@instock.lv | password |

Jauns reģistrēts lietotājs automātiski izveido savu firmu un kļūst par šīs firmas administratoru.

## Lomas un tiesības

**Administrators** var:

- pievienot, labot un dzēst preces
- pievienot, labot un dzēst noliktavas
- veidot un grāmatot dokumentus
- atcelt grāmatotus dokumentus
- skatīt atlikumus, dokumentus, kustības un eksportēt CSV

**Noliktavas darbinieks** var:

- skatīt preces un noliktavas
- veidot un grāmatot dokumentus
- skatīt atlikumus, dokumentus un kustības
- eksportēt CSV
- nevar labot preču/noliktavu katalogus
- nevar atcelt grāmatotus dokumentus

## Moduļi

### Sākumlapa

Publiska lapa ar noliktavas sistēmas aprakstu un demo firmas datu pārskatu.

### Dashboard

Pēc pieslēgšanās rāda:

- kopējo preču daudzumu uzskaitē
- krājumu vērtību
- atvērtos dokumentus
- noliktavu skaitu
- zema atlikuma preču skaitu
- atvērtos dokumentus pēc tipa
- top preces pēc vērtības
- pēdējos apstiprinātos dokumentus

### Produkti

Preču katalogs ar:

- nosaukumu
- iepirkuma cenu
- realizācijas cenu
- mērvienību
- CSV eksportu

Preču pievienošana, labošana un dzēšana pieejama tikai administratoram.

### Noliktavas

Firmas noliktavu saraksts. Noliktavu pievienošana, labošana un dzēšana pieejama tikai administratoram.

### Atlikumi

Atlikumu pārskats pa:

- noliktavu
- preci
- zonu
- FIFO partiju
- daudzumu
- cenu

Pieejami filtri:

- noliktava
- prece
- zona
- partija
- tikai zemi atlikumi

Pieejams CSV eksports.

### Dokumenti

Dokumentu sadaļā var:

- izveidot dokumenta melnrakstu
- labot melnrakstu
- dzēst melnrakstu
- grāmatot dokumentu
- skatīt dokumenta detaļas
- drukāt dokumentu
- atcelt grāmatotu dokumentu
- eksportēt dokumentu sarakstu CSV

Pieejami dokumentu tipi:

- saņemšana
- norakstīšana
- pārvietošana
- realizācija

Pieejami filtri:

- dokumenta tips
- statuss: melnraksts, apstiprināts, atcelts
- avota noliktava
- mērķa noliktava
- operators
- datuma intervāls
- komentāra meklēšana

### Kustību vēsture

Preču kustību vēsture tiek veidota no `stock_document_ledger` tabulas. Tā rāda katru grāmatojuma izmaiņu:

- dokumentu
- dokumenta tipu
- datumu
- preci
- noliktavu
- zonu
- partiju
- daudzuma izmaiņu

Pieejami filtri un CSV eksports.

## Datu bāzes struktūras ideja

Svarīgākās tabulas:

- `users` — lietotāji
- `firma` — firmas
- `firma_user` — lietotāja piesaiste firmai un loma
- `product` — preču katalogs
- `stock` — firmas noliktavas
- `stock_document` — noliktavas dokumenta galvene
- `stock_document_product` — dokumenta rindas
- `product_stock` — faktiskie atlikumi pa partijām un zonām
- `stock_document_ledger` — grāmatoto kustību žurnāls

Preces ir kopīgs katalogs, bet atlikumi, noliktavas un dokumenti ir piesaistīti firmai.

## OOP struktūra

Projektā izmantotas vairākas objektorientētas daļas:

- **Modeļi**: `Product`, `Stock`, `StockDocument`, `ProductStock`, `Firma`, `User`
- **Servisi**:
  - `StockDocumentService` — dokumentu grāmatošana, FIFO atlikumu maiņa, atcelšana
  - `StockOverviewService` — dashboard un pārskatu dati
  - `CsvExportService` — CSV failu ģenerēšana
- **Enum klases**:
  - `DocumentType`
  - `UserRole`
- **Middleware**:
  - `EnsureFirma`
  - `EnsureAdmin`
- **Kontrolieri**:
  - `DocumentController`
  - `BalanceController`
  - `MovementController`
  - `ProductController`
  - `WarehouseController`
  - `DashboardController`
  - `AuthController`

## Palaišana

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Atveriet:

```text
http://127.0.0.1:8000
```

Laravel Herd vidē projektu var atvērt arī caur `.test` domēnu, piemēram:

```text
http://noliktavassistema.test
```

## Datu bāze

Projekts var strādāt ar SQLite, MySQL vai MariaDB, atkarībā no `.env` konfigurācijas.

Lokālai MariaDB konfigurācijai piemērs:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stock_oop
DB_USERNAME=root
DB_PASSWORD=
```

Pēc `.env` izmaiņām:

```bash
php artisan config:clear
php artisan migrate --seed
```

Ja vajag pilnībā pārbūvēt lokālo datu bāzi:

```bash
php artisan migrate:fresh --seed
```

## Testēšana

Projektā ir PHPUnit testi galvenajai funkcionalitātei.

Palaist testus:

```bash
php artisan test
```

Testi pārbauda:

- sākumlapas darbību
- firmas konteksta validāciju
- dokumentu validāciju
- dzēstu preču aizliegšanu dokumentos
- dokumentu filtrus un eksportu lapu pieejamību
- melnrakstu labošanu un dzēšanu
- saņemšanas dokumenta grāmatošanu
- norakstīšanu un atcelšanu
- pārvietošanu starp noliktavām
- realizāciju
- operatora piekļuves ierobežojumus administratora darbībām

## Pēc koda atjaunināšanas

```bash
git pull origin Dev
composer install
php artisan migrate --seed
npm install
npm run build
php artisan test
```

## Tehnoloģijas

- PHP 8.2+
- Laravel 12
- Blade
- Vite
- Tailwind CSS
- MySQL / MariaDB / SQLite
- PHPUnit
