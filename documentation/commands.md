composer install &&
php artisan migrate &&
chmod -R 755 storage bootstrap/cache &&
php artisan seed:log-subject-types &&
php artisan config:clear &&
php artisan cache:clear &&
php artisan route:clear &&
php artisan view:clear &&
composer dump-autoload && 
php artisan queue:work


composer install &&
php artisan migrate:fresh &&
chmod -R 755 storage bootstrap/cache &&
php artisan seed:log-subject-types
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=PermissionActivityLogSeeder
php artisan db:seed --class=UserSeeder 
