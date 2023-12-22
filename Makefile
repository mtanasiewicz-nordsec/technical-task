fixer:
	docker-compose exec php bash -c "./vendor/bin/php-cs-fixer fix"

phpstan:
	docker-compose exec php bash -c "./vendor/bin/phpstan analyze"

up:
	docker compose up -d

down:
	docker compose down

dsud:
	docker-compose exec php bash -c "bin/console d:s:u --dump-sql"

migration:
	docker-compose exec php bash -c "bin/console make:migration"

dmm:
	docker-compose exec php bash -c "bin/console d:m:m"

dmmp:
	docker-compose exec php bash -c "bin/console d:m:m prev"

invalidate:
	docker-compose exec php bash -c "bin/console app:invalidate-coordinates-cache"

phpunit:
	docker-compose exec php bash -c "php bin/phpunit"

cc:
	docker-compose exec php bash -c "bin/console cache:clear"
