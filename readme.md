## Photo Resize Challenge

### Setup Steps

- Clone this project;
- Enter Directory
- $ composer install
- $ cp .env.example .env
- $ php artisan key:generate
- $ php artisan storage:link

### Run Commands:
- $ php artisan photoresizer download - This command get and store photos.
- $ php artisan photoresizer generate - This command photos with others dimensions

### Start server
- $ php artisan serve
- open browser or postman and call the url: http://localhost/api/photos

### Run Tests
- $ vendor/bin/phpunit

### Important test files to evaluate

- app/Console/Commands/PhotoResizer.php
- tests/Feature/PhotoResizerTest.php
- phpunit.xml
- routes/api.php
- config/app.php
- app/Photo.php
- config/database.php