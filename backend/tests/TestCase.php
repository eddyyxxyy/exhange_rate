<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use PDO;
use Mockery;
use Redis; // Keep Redis imported for the ContainerTest to mock it

/**
 * Base test case for integration tests.
 * This class provides common setup for the DI container and an in-memory SQLite database.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * The Dependency Injection container instance.
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * Sets up the test environment before each test.
     * Initializes the DI container and the in-memory SQLite database.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeContainer();
        $this->setUpInMemoryDatabase();
    }

    /**
     * Cleans up the test environment after each test.
     * Closes Mockery expectations. In-memory SQLite is self-cleaning.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Initializes the Dependency Injection container for tests.
     * Loads application's DI definitions.
     */
    protected function initializeContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(require __DIR__ . '/../bootstrap/container.php');
        $this->container = $containerBuilder->build();
    }

    /**
     * Sets up an in-memory SQLite database and applies the schema.
     * IMPORTANT: Implement `applyDatabaseMigrations()` with your actual schema logic.
     */
    protected function setUpInMemoryDatabase(): void
    {
        try {
            // Retrieve DB configuration from AppConfig (populated by phpunit.xml env vars)
            $appConfig = $this->container->get(\App\Config\AppConfig::class);
            $dbConnection = $appConfig->get('db.connection');
            $dbDatabase = $appConfig->get('db.database');

            // Only proceed if configured for SQLite in-memory
            if ($dbConnection === 'sqlite' && $dbDatabase === ':memory:') {
                $pdo = new PDO('sqlite::memory:');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // --- CALL YOUR MIGRATION/SCHEMA APPLICATION LOGIC HERE ---
                // This method is where your database tables and structure will be created for each test run.
                // When you integrate Eloquent, you'll update this method to run Eloquent migrations.
                $this->applyDatabaseMigrations($pdo);
                // --- END OF MIGRATION LOGIC ---

                // Override the PDO definition in the container with our test PDO instance
                // This ensures your application services get the in-memory DB connection.
                $this->container->set(PDO::class, $pdo);
            } else {
                // If not using in-memory SQLite, skip database setup.
                // This could be for other DB types (e.g., a Dockerized MySQL for specific integration tests).
                $this->markTestSkipped('Database is not configured for in-memory SQLite for testing in phpunit.xml.');
            }
        } catch (\PDOException $e) {
            $this->fail("Failed to set up in-memory SQLite: " . $e->getMessage());
        }
    }

    /**
     * Applies the database schema (migrations) to the provided PDO connection.
     *
     * This method is a placeholder. You MUST implement the actual logic here
     * to create your application's tables for testing.
     *
     * Examples:
     * - Executing SQL from a schema.sql file: `$pdo->exec(file_get_contents(__DIR__ . '/../../database/schema.sql'));`
     * - When using Eloquent in the future: You'd call Eloquent's migration runner here, e.g., `$this->artisan('migrate');`
     * - Manually creating tables for simple apps: `$pdo->exec("CREATE TABLE IF NOT EXISTS my_table (id INTEGER PRIMARY KEY, name TEXT);");`
     *
     * @param PDO $pdo The PDO instance for the in-memory SQLite database.
     */
    protected function applyDatabaseMigrations(PDO $pdo): void
    {
        // --- REPLACE THIS WITH YOUR REAL MIGRATIONS ---
        // This is a placeholder example to ensure no failures if no schema is present yet.
        // For real applications, your actual tables would be created here.
        $pdo->exec("CREATE TABLE IF NOT EXISTS example_data (id INTEGER PRIMARY KEY, value TEXT);");
    }
}