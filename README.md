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
    git clone <repository-url> # Replace with your actual repository URL
    cd exchange-rate-application # Navigate into your project's root directory
    ```

3.  **Project Structure:**
    Ensure your project has the following essential structure:

    ```
    exchange-rate-application/
    ├── backend/                 # Contains the PHP backend application files
    │   ├── public/
    │   │   └── index.php
    │   ├── composer.json        # Backend's Composer configuration
    │   └── ... (your PHP application source code)
    ├── docker/                  # Docker-related configurations
    │   ├── docker-compose.yaml  # Main Docker Compose orchestration file
    │   ├── php-fpm-custom/      # Custom Dockerfile for PHP-FPM service
    │   │   └── Dockerfile
    │   └── nginx/               # Nginx server configurations
    │       └── server_block.conf
    └── README.md
    └── LICENSE.md
    ```

4.  **`docker/php-fpm-custom/Dockerfile` Content:**
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
        root /app/public; # IMPORTANT: This path is inside the Docker container and points to your backend's public directory

        location / {
            # Try to serve files directly, then pass the request to index.php for Slim framework routing
            try_files $uri /index.php$is_args$args;
        }

        location ~ \.php {
            # Pass PHP requests to the PHP-FPM service
            try_files $uri =404; # Ensures that only existing PHP files are processed
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param SCRIPT_NAME $fastcgi_script_name;
            fastcgi_index index.php;
            fastcgi_pass phpfpm:9000; # 'phpfpm' is the service name in docker-compose.yaml
        }

        # Deny access to sensitive files (e.g., .env files)
        location ~ /\.env {
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
        driver: bridge # Defines a custom network for inter-service communication

    services:
      phpfpm:
        build: ./php-fpm-custom # Build context: looks for Dockerfile in this directory
        networks:
          - app-tier
        volumes:
          - ../backend:/app # Mounts your local 'backend' directory into '/app' in the container
        environment:
          - PHP_ENABLE_OPCACHE=no           # Disable OPcache for instant code changes in development
          - PHP_DATE_TIMEZONE=America/Sao_Paulo # Set your desired timezone
          - PHP_DISPLAY_ERRORS=On           # Show PHP errors in development (turn off in production!)
          - PHP_EXPOSE_PHP=no               # Hide PHP version in HTTP headers (good practice)
          - PHP_MEMORY_LIMIT=256M           # Increase PHP memory limit if needed

      nginx:
        image: bitnami/nginx:latest
        depends_on:
          - phpfpm # Ensure PHP-FPM starts before Nginx
        networks:
          - app-tier
        ports:
          - "80:80"   # Map host port 80 to container port 80 (HTTP)
          - "443:443" # Map host port 443 to container port 443 (HTTPS)
        volumes:
          # Mount your custom Nginx server block configuration
          - ./nginx/server_block.conf:/opt/bitnami/nginx/conf/server_blocks/yourapp.conf:ro
          - ../backend:/app # Nginx also needs access to your application files to serve PHP

      mysql:
        image: bitnami/mysql:latest
        networks:
          - app-tier
        environment:
          - MYSQL_ROOT_PASSWORD='Sup3r$tron0P4ssw0rd' # !! IMPORTANT: CHANGE THIS PASSWORD IN PRODUCTION !!
          - MYSQL_DATABASE=exchange_rate_db
          - MYSQL_USER=exchange_user
          - MYSQL_PASSWORD='$tron0P4ssw0rd' # !! IMPORTANT: CHANGE THIS PASSWORD IN PRODUCTION !!
        volumes:
          - mysql_data:/bitnami/mysql # Persistent volume for MySQL data to prevent data loss

      redis:
        image: bitnami/redis:latest
        networks:
          - app-tier
        environment:
          - REDIS_PASSWORD='Sup3r$tron0P4ssw0rd' # !! IMPORTANT: CHANGE THIS PASSWORD IN PRODUCTION !!
        volumes:
          - redis_data:/bitnami/redis # Persistent volume for Redis data

    volumes:
      mysql_data: # Define named volume for MySQL persistence
      redis_data: # Define named volume for Redis persistence
    ```

7.  **Build and Run Docker Containers:**
    From your project's root directory (where the `docker` folder is located), execute the following commands to build your custom PHP-FPM image and start all services:

    ```bash
    docker compose -f ./docker/docker-compose.yaml down          # Stop and remove any previously running containers
    docker compose -f ./docker/docker-compose.yaml build phpfpm # Build the custom PHP-FPM image
    docker compose -f ./docker/docker-compose.yaml up -d        # Start all defined services in detached mode
    ```

8.  **Install PHP Dependencies (Composer):**
    Once your `phpfpm` container is running, install your backend's PHP dependencies using Composer:

    ```bash
    docker compose -f ./docker/docker-compose.yaml exec phpfpm composer install -d /app
    ```
    The `-d /app` flag tells Composer to run inside the `/app` directory, which is where your `backend` code is mounted in the `phpfpm` container.

9.  **Access the Backend API:**
    Your backend API should now be accessible via Nginx on your local machine.
    * **Base API Endpoint:** `http://localhost/` (or specific API routes you've defined in Slim).
    * **PHP Info (for verification):** For development purposes, you can create a temporary `phpinfo.php` file in your `backend/public/` directory with the content `<?php phpinfo();`. Then, visit `http://localhost/phpinfo.php` in your browser. **Remember to remove this file immediately after verification for security reasons.**

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
* `backend/vendor/`: Composer's directory for installed third-party dependencies (managed by Docker).

### Dependencies (from `composer.json`):

* `slim/slim: 4.*`
* `slim/psr7: ^1.7`
* **phpredis extension:** Installed via `pecl` in the `php-fpm-custom/Dockerfile` for Redis integration.

## 3. Frontend Application (Planned)

This project is planned to include a dedicated frontend application that will consume the backend API to provide a user interface for exchange rate data.

### Planned Technologies:

* **React:** The core JavaScript library for building user interfaces.
* **UI Framework/Library (Under Consideration):**
    * **TanStack Start:** A full-stack meta-framework for React, offering advanced data fetching, routing, and potential server-side rendering capabilities.
    * *Alternatively, a more traditional **client-side rendering (CSR) React setup** using a lighter router (like TanStack Router only) is also being evaluated based on final project requirements and complexity.*

The frontend will reside in a separate `frontend/` directory at the project root and will be integrated into the Docker Compose setup for consistent development. Further details will be added to this `README.md` once the frontend technology stack is finalized.

---

## License

Take a look at [LICENSE.md](./LICENSE.md) for more details.
