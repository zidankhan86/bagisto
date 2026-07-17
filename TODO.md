# TODO

- [ ] Update `database/seeders/CategorySeeder.php`:
  - [ ] Expand category set (1 root + 6 main + 12 sub)
  - [ ] Randomly assign images from `public/img/category/*.webp` to `logo_path`
  - [ ] Update `category_translations` for all new categories
  - [ ] Keep proper nested-set fields (`_lft`, `_rgt`) coherent
- [ ] Run seeder: `php artisan db:seed --class=DatabaseSeeder`
- [ ] Clear caches: `php artisan optimize:clear`
- [ ] Verify home category carousel shows images (no fallback placeholder)

