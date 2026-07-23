<?php

namespace BrilliantMind\MPesa\Config;

use BrilliantMind\MPesa\Exceptions\InvalidEnvironmentException;
use BrilliantMind\MPesa\Exceptions\MissingConfigurationException;

class Config
{
    public const ENV_DEVELOPMENT = 'development';

    public const ENV_PRODUCTION = 'production';

    /**
     * Ports used by each M-Pesa operation.
     */
    public const DEFAULT_PORTS = [
        'c2b' => '18352',
        'b2b' => '18349',
        'b2c' => '18345',
        'status' => '18353',
        'reversal' => '18354',
    ];

    /**
     * Accepted aliases for each environment.
     *
     * @var array<string, string>
     */
    protected const ENVIRONMENT_ALIASES = [
        'development' => self::ENV_DEVELOPMENT,
        'dev' => self::ENV_DEVELOPMENT,
        'sandbox' => self::ENV_DEVELOPMENT,
        'test' => self::ENV_DEVELOPMENT,
        'testing' => self::ENV_DEVELOPMENT,
        'local' => self::ENV_DEVELOPMENT,
        'production' => self::ENV_PRODUCTION,
        'prod' => self::ENV_PRODUCTION,
        'live' => self::ENV_PRODUCTION,
    ];

    /**
     * Every typed property below carries a default on purpose: without it PHP throws
     * "Typed static property ... must not be accessed before initialization" the first
     * time the package is touched before being fully configured.
     */
    protected static string $environment = self::ENV_DEVELOPMENT;

    protected static string $hostDeveloper = 'api.sandbox.vm.co.mz';

    protected static string $hostProduction = 'api.vm.co.mz';

    protected static string $origin = 'developer.mpesa.vm.co.mz';

    protected static string $api_key = '';

    protected static string $public_key = '';

    protected static string $host = '';

    protected static string $service_provider_code = '171717';

    protected static string $initiatorIdentifier = '';

    protected static string $securityCredential = '';

    protected static string $port = '';

    /**
     * @var array<string, string>
     */
    protected static array $ports = self::DEFAULT_PORTS;

    protected static float $timeout = 90.0;

    protected static float $connectTimeout = 30.0;

    protected static bool $verifySsl = true;

    /**
     * True once config() was called by hand; from then on manual values win.
     */
    protected static bool $configured = false;

    /**
     * True once the values were successfully pulled from the Laravel config repository.
     */
    protected static bool $hydrated = false;

    /**
     * Guards against hydrateFromLaravel() -> config() -> hydrateIfNeeded() recursion.
     */
    protected static bool $hydrating = false;

    /**
     * Configure the package manually. Every argument is optional so partial
     * reconfiguration (for example, switching only the environment) is safe.
     */
    public static function config(
        ?string $api_key = null,
        ?string $public_key = null,
        ?string $environment = null,
        ?string $service_provider_code = null,
        ?string $origin = null,
        ?string $initiatorIdentifier = null,
        ?string $securityCredential = null,
        ?string $host = null,
        ?string $port = null,
        ?array $ports = null,
        ?float $timeout = null,
        ?float $connectTimeout = null,
        ?bool $verifySsl = null
    ): void {
        // Pull the Laravel defaults in first so a partial manual call only overrides
        // what it was actually given, instead of being wiped by a later hydration.
        if (! static::$hydrating) {
            static::hydrateIfNeeded();
            static::$configured = true;
        }

        if ($environment !== null && $environment !== '') {
            static::$environment = static::normalizeEnvironment($environment);
        }

        if ($api_key !== null) {
            static::$api_key = trim($api_key);
        }

        if ($public_key !== null) {
            static::$public_key = static::normalizePublicKey($public_key);
        }

        if ($host !== null) {
            static::$host = trim($host);
        }

        if ($port !== null) {
            static::$port = trim($port);
        }

        if ($origin !== null && $origin !== '') {
            static::$origin = trim($origin);
        }

        if ($service_provider_code !== null && $service_provider_code !== '') {
            static::$service_provider_code = trim($service_provider_code);
        }

        if ($initiatorIdentifier !== null) {
            static::$initiatorIdentifier = trim($initiatorIdentifier);
        }

        if ($securityCredential !== null) {
            static::$securityCredential = trim($securityCredential);
        }

        if ($ports !== null) {
            static::$ports = array_map('strval', $ports) + static::DEFAULT_PORTS;
        }

        if ($timeout !== null) {
            static::$timeout = $timeout;
        }

        if ($connectTimeout !== null) {
            static::$connectTimeout = $connectTimeout;
        }

        if ($verifySsl !== null) {
            static::$verifySsl = $verifySsl;
        }
    }

    /**
     * Pull the values from the Laravel config repository (config/mpesa.php).
     *
     * Runs lazily on first use, so the package never depends on service provider
     * boot order and always sees what a cached config or a queue worker has.
     */
    public static function hydrateFromLaravel(): bool
    {
        $values = static::laravelConfig();

        if ($values === null) {
            return false;
        }

        static::$hydrating = true;

        $publicKey = (string) ($values['public_key'] ?? '');
        $publicKeyPath = (string) ($values['public_key_path'] ?? '');

        if ($publicKey === '' && $publicKeyPath !== '' && is_readable($publicKeyPath)) {
            $publicKey = (string) file_get_contents($publicKeyPath);
        }

        static::config(
            api_key: (string) ($values['api_key'] ?? ''),
            public_key: $publicKey,
            environment: (string) ($values['environment'] ?? ''),
            service_provider_code: (string) ($values['service_provider_code'] ?? ''),
            origin: (string) ($values['origin'] ?? ''),
            initiatorIdentifier: (string) ($values['initiatorIdentifier'] ?? $values['initiator_identifier'] ?? ''),
            securityCredential: (string) ($values['securityCredential'] ?? $values['security_credential'] ?? ''),
            host: (string) ($values['host'] ?? ''),
            port: (string) ($values['port'] ?? ''),
            ports: is_array($values['ports'] ?? null) ? $values['ports'] : null,
            timeout: isset($values['timeout']) ? (float) $values['timeout'] : null,
            connectTimeout: isset($values['connect_timeout']) ? (float) $values['connect_timeout'] : null,
            verifySsl: isset($values['verify_ssl']) ? (bool) $values['verify_ssl'] : null,
        );

        static::$hydrating = false;

        return static::$hydrated = true;
    }

    /**
     * Guarantee the package has everything it needs before talking to the gateway.
     *
     * @throws MissingConfigurationException
     */
    public static function ensureConfigured(): void
    {
        static::hydrateIfNeeded();

        $missing = [];

        if (static::$api_key === '') {
            $missing[] = 'MPESA_API_KEY';
        }

        if (static::$public_key === '') {
            $missing[] = 'MPESA_PUBLIC_KEY';
        }

        if ($missing !== []) {
            throw MissingConfigurationException::forEnvKeys($missing);
        }
    }

    public static function isConfigured(): bool
    {
        static::hydrateIfNeeded();

        return static::$api_key !== '' && static::$public_key !== '';
    }

    /**
     * Forget everything. Mostly useful inside test suites.
     */
    public static function reset(): void
    {
        static::$environment = self::ENV_DEVELOPMENT;
        static::$origin = 'developer.mpesa.vm.co.mz';
        static::$api_key = '';
        static::$public_key = '';
        static::$host = '';
        static::$service_provider_code = '171717';
        static::$initiatorIdentifier = '';
        static::$securityCredential = '';
        static::$port = '';
        static::$ports = self::DEFAULT_PORTS;
        static::$timeout = 90.0;
        static::$connectTimeout = 30.0;
        static::$verifySsl = true;
        static::$configured = false;
        static::$hydrated = false;
        static::$hydrating = false;
    }

    public static function getEnvironment(): string
    {
        static::hydrateIfNeeded();

        return static::$environment;
    }

    public static function isProduction(): bool
    {
        return static::getEnvironment() === self::ENV_PRODUCTION;
    }

    public static function getApiKey(): string
    {
        static::hydrateIfNeeded();

        return static::$api_key;
    }

    public static function getPublicKey(): string
    {
        static::hydrateIfNeeded();

        return static::$public_key;
    }

    /**
     * Resolved at call time so switching environment never leaves a stale host behind.
     */
    public static function getHost(): string
    {
        static::hydrateIfNeeded();

        if (static::$host !== '') {
            return static::$host;
        }

        return static::isProduction() ? static::$hostProduction : static::$hostDeveloper;
    }

    public static function getServiceProviderCode(): string
    {
        static::hydrateIfNeeded();

        return static::$service_provider_code;
    }

    public static function getOrigin(): string
    {
        static::hydrateIfNeeded();

        return static::$origin;
    }

    public static function getInitiatorIdentifier(): string
    {
        static::hydrateIfNeeded();

        return static::$initiatorIdentifier;
    }

    public static function getSecurityCredential(): string
    {
        static::hydrateIfNeeded();

        return static::$securityCredential;
    }

    /**
     * Global port override. Empty means "use the port of each operation".
     */
    public static function getPort(): string
    {
        static::hydrateIfNeeded();

        return static::$port;
    }

    /**
     * @return array<string, string>
     */
    public static function getPorts(): array
    {
        static::hydrateIfNeeded();

        return static::$ports;
    }

    /**
     * Port for a given operation, honouring the global override when present.
     */
    public static function getPortFor(string $operation): string
    {
        $override = static::getPort();

        if ($override !== '') {
            return $override;
        }

        return static::getPorts()[$operation] ?? self::DEFAULT_PORTS[$operation] ?? '';
    }

    public static function getTimeout(): float
    {
        static::hydrateIfNeeded();

        return static::$timeout;
    }

    public static function getConnectTimeout(): float
    {
        static::hydrateIfNeeded();

        return static::$connectTimeout;
    }

    public static function shouldVerifySsl(): bool
    {
        static::hydrateIfNeeded();

        return static::$verifySsl;
    }

    /**
     * Hydrate once from Laravel, unless credentials were already set by hand.
     */
    protected static function hydrateIfNeeded(): void
    {
        if (static::$configured || static::$hydrated || static::$hydrating) {
            return;
        }

        static::hydrateFromLaravel();
    }

    /**
     * Read the "mpesa" config array when running inside a booted Laravel application.
     *
     * @return array<string, mixed>|null
     */
    protected static function laravelConfig(): ?array
    {
        if (! function_exists('app')) {
            return null;
        }

        try {
            $container = app();

            if (! $container->bound('config')) {
                return null;
            }

            $values = $container->make('config')->get('mpesa');
        } catch (\Throwable) {
            return null;
        }

        return is_array($values) ? $values : null;
    }

    /**
     * @throws InvalidEnvironmentException
     */
    protected static function normalizeEnvironment(string $environment): string
    {
        $normalized = strtolower(trim($environment));

        if (! isset(self::ENVIRONMENT_ALIASES[$normalized])) {
            throw InvalidEnvironmentException::for($environment);
        }

        return self::ENVIRONMENT_ALIASES[$normalized];
    }

    /**
     * Accept the key in every shape the M-Pesa portal hands it out:
     * one long base64 line, wrapped base64, or a full PEM block.
     */
    protected static function normalizePublicKey(string $publicKey): string
    {
        $publicKey = trim($publicKey);

        if ($publicKey === '' || str_contains($publicKey, '-----BEGIN')) {
            return $publicKey;
        }

        return (string) preg_replace('/\s+/', '', $publicKey);
    }
}
