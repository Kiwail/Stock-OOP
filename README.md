# In Stock — Noliktavas sistēma

Tīmekļa lietotne noliktavas uzskaitei (Laravel): preces, noliktavas, atlikumi, dokumenti (saņemšana, norakstīšana, pārvietošana, realizācija).

## Palaišana

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Atveriet [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Demo konti

| Lomа | E-pasts | Parole |
|------|---------|--------|
| Administrators | admin@instock.lv | password |
| Operators | operators@instock.lv | password |

## Jaunas iespējas

- Sākumlapa rāda **reālus** atlikumus un dokumentus (demo firma)
- **Glabāšanas zonas** (A-12, B-04) saņemšanas dokumentos
- **Dokumenta atcelšana** (tikai administrators, atgriež atlikumus)

Pēc koda atjaunināšanas: `php artisan migrate` (vai `migrate:fresh --seed`).

## Moduļi

- **Sākumlapa** — prezentācija ar dzīviem datiem
- **Panelis** — kopsavilkums pēc pieslēgšanās
- **Produkti** — katalogs (admin: pievienošana/labošana)
- **Noliktavas** — firmas noliktavas (admin)
- **Atlikumi** — daudzumi pa partijām (FIFO)
- **Dokumenti** — melnraksts un apstiprināšana (grāmatošana)

## Tehnoloģijas

PHP, Laravel, Blade, MySQL/SQLite
