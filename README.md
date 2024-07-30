# System Guide

Access Database

```bash
docker exec -it adikasi.mysql mysql -u root -p
```

Running Test

```bash
docker exec adikasi.php php artisan test --env=testing
```

Runing Migration

```
docker exec adikasi.php php artisan migrate --env=testing
```

Install Setup

1. Run command `docker exec adikasi.php composer install`
2. Run command `docker exec adikasi.php php artisan key:generate`
3. Run command `docker exec adikasi.php php artisan jwt:secret`
4. copy `.env.example` to `.env`
5. Run command `docker exec adikasi.php php artisan migrate`
