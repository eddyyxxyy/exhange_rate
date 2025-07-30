# Exchange Rate Application

The **Exchange Rate Application** provides an API for retrieving the latest and historical exchange rates for various currencies. This project is structured to have a robust PHP backend and a dynamic frontend, both running efficiently with Docker.

---

## 1. Infrastructure Overview

The entire development and deployment environment for this application is managed using **Docker Compose**. This ensures all services run in isolated and consistent containers, simplifying setup and preventing conflicts.

### Technologies:

* **Docker:** Containerization platform.
* **Docker Compose:** Tool for defining and running multi-container Docker applications.
* **PHP 8.4.8 (PHP-FPM):** The PHP runtime environment for the backend application.
* **Nginx:** High-performance web server and reverse proxy, serving the PHP backend.
* **MySQL:** Relational database for persistent data storage.
* **Redis:** An in-memory data store, primarily used for caching and high-performance data operations within the backend.

### Setup Instructions:

Follow these steps to get the backend development environment up and running:

1.  **Prerequisites:**
    * **Docker Desktop:** Ensure Docker Desktop is installed and running on your system (Windows, macOS, or Linux).
    * **Git:** To clone the repository.

2.  **Clone the Repository:**
    Navigate to your desired directory in your terminal and clone the project:

    ```bash
    git clone https://github.com/eddyyxxyy/exchange_rate.git
    cd exchange-rate
    ```

3.  **Project Structure:**
    Ensure your project has the following essential structure:

    ```
    exchange-rate-application/
    │
    ├── backend/                 # Contains the PHP backend application files
    │   ├── .env                 # Environment variables used by both Docker and the PHP app
    │   ├── bootstrap/           # Application initialization
    │   │   └── ...
    │   ├── config/
    │   │   └── app.php
    │   ├── public/
    │   │   └── index.php
    │   ├── composer.json        # Backend's Composer configuration
    │   └── src/
    │   │   └── ... (Application source code)
    │
    ├── frontend/                 # Contains the frontend application files
    │   └──  ... (Application source code)
    │
    ├── docker/                  # Docker-related configurations
    │   ├── docker-compose.yaml  # Main Docker Compose orchestration file
    │   ├── php-fpm/      # Custom Dockerfile for PHP-FPM service
    │   │   └── Dockerfile
    │   └── nginx/               # Nginx server configurations
    │       └── server_block.conf
    │
    └── README.md
    └── LICENSE.md
    ```

4.  **`docker/php-fpm/Dockerfile` Content:**
    This custom Dockerfile extends the base Bitnami PHP-FPM image, installing essential build tools and the `phpredis` extension required by the backend.

    ```dockerfile
    FROM bitnami/php-fpm:8.4.8

    # Install build dependencies required to compile PHP extensions (e.g., Redis)
    # These packages provide compilers and development libraries.
    RUN apt-get update && apt-get install -y \
        build-essential \
        pkg-config \
        libssl-dev \
        autoconf \
        gnupg \
        && rm -rf /var/lib/apt/lists/* # Clean up apt cache to keep image size small

    # Install the Redis extension for PHP (phpredis)
    # 'pecl install redis' downloads, compiles, and installs the extension.
    # The 'echo' command then tells PHP to load this new extension.
    RUN pecl install redis \
        && echo "extension=redis.so" > /opt/bitnami/php/etc/conf.d/redis.ini

    # Clean up build dependencies after installation
    # This removes tools and libraries only needed for compilation, reducing the final image size.
    RUN apt-get purge -y --auto-remove \
        build-essential \
        pkg-config \
        libssl-dev \
        autoconf \
        gnupg \
        && rm -rf /var/lib/apt/lists/*
    ```

5.  **`docker/nginx/server_block.conf` Content:**
    This Nginx configuration routes all incoming HTTP requests to your PHP-FPM backend service.

    ```nginx
    server {
        listen 80;
        server_name localhost;
        index index.php;
        root /app/public;

        # SPA frontend (React, etc.)
        location / {
            try_files $uri /index.html;
        }

        # Slim PHP
        location = /api {
            return 301 /api/;
        }

        location ^~ /api/ {
            try_files $uri /index.php$is_args$args;
        }

        # PHP handler
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param SCRIPT_NAME $fastcgi_script_name;
            fastcgi_index index.php;
            fastcgi_pass phpfpm:9000;
        }

        # Security
        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
    ```

6.  **`docker/docker-compose.yaml` Content:**
    This file orchestrates all the Docker services required for the backend application.

    ```yaml
    name: exchange-rate-app

    networks:
      app-tier:
        driver: bridge

    services:
      phpfpm:
        build: ./php-fpm
        depends_on:
          - mysql
          - redis
        networks:
          - app-tier
        volumes:
          - ../backend:/app
        env_file: ../backend/.env

      nginx:
        image: bitnami/nginx:1.29.0
        depends_on:
          - phpfpm
        networks:
          - app-tier
        ports:
          - "80:80"
          - "443:443"
        volumes:
          - ./nginx/server_block.conf:/opt/bitnami/nginx/conf/server_blocks/yourapp.conf:ro
          - ../backend:/app

      mysql:
        image: bitnami/mysql:9.3.0
        networks:
          - app-tier
        volumes:
          - mysql_data:/bitnami/mysql
        env_file: ../backend/.env
        environment:
          - MYSQL_ROOT_PASSWORD=Sup3rStron0P4ssw0rd
          - MYSQL_DATABASE=${DB_NAME}
          - MYSQL_USER=${DB_USER}
          - MYSQL_PASSWORD=${DB_PASS}

      redis:
        image: bitnami/redis:8.0.3
        networks:
          - app-tier
        volumes:
          - redis_data:/bitnami/redis
        env_file: ../backend/.env
        environment:
          - REDIS_PASSWORD=${REDIS_AUTH}

    volumes:
      mysql_data:
      redis_data:
    ```

7.  **Build and Run Docker Containers:**
    From your project's root directory (where the `docker` folder is located), execute the following command to build and start all services:

    ```bash
    docker compose --env-file backend/.env -f docker/docker-compose.yaml up --build -d
    ```

8.  **Access the Backend API:**
    Your backend API should now be accessible via Nginx on your local machine.
    * **Base API Endpoint:** `http://localhost/api` (or specific API routes you've defined in Slim).

---

## 2. Backend Application Details

This section provides more specific information about the PHP backend application itself.

### Technologies:

* **PHP 8.4.8**
* **Slim 4 Framework:** A popular PHP micro-framework for building robust web applications and APIs.
* **Composer:** PHP's dependency manager, used for managing project libraries.
* **MySQL:** For structured data storage.
* **Redis:** Utilized for caching and potentially other high-performance data operations.

### Project Structure (Backend Specific):

* `backend/src/`: Contains your application's source code, following the `App\` namespace as defined in `composer.json`.
* `backend/public/`: The web server's document root, containing `index.php` as the entry point for the Slim application.
* `backend/bootstrap/`: The web server's bootstraping used to define all app routes, deps and more.
* `backend/config/`: The web server's configuration directives.
* `backend/vendor/`: Composer's directory for installed third-party dependencies (managed by Docker).

### Dependencies (from `composer.json` and `php`):


* "slim/slim": "4.*",
* "slim/psr7": "^1.7",
* "php-di/slim-bridge": "^3.4",
* "vlucas/phpdotenv": "^5.6"
* **mysqli extension:** Enabled via php.ini.
* **pdo_mysql extension:** Enabled via php.ini.
* **php_redis extension:** Installed via `pecl` in the `php-fpm/Dockerfile` for Redis integration.

## 3. Frontend Application (Planned)

Will be located in /frontend, likely using React + TanStack Router.

---

## License

Take a look at [LICENSE.md](./LICENSE.md) for more details.
