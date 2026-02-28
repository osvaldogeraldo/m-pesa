<?php

namespace BrilliantMind\MPesa\Config;

class Config {

    /**
     * @var string $environment
     */
    private static string $environment = 'development';

    /**
     * @var string $hostDeveloper
     */
    private static string $hostDeveloper = 'api.sandbox.vm.co.mz';

    /**
     * Summary of hostProduction
     * @var string hostProduction
     */
    private static string $hostProduction = 'api.vm.co.mz';

    /**
     * @var string $origin
     */
    private static string $origin = 'developer.mpesa.vm.co.mz';

    /**
     * @var string $api_key
     */
    private static string $api_key;


    private static string $host;
    /**
     * @var string $public_key
     */
    private static string $public_key;


    /**
     * @var string $service_provider_code
     */
    private static string $service_provider_code = '171717';

    private static string $initiatorIdentifier;

    private static string $securityCredential;

    public static function config( string $api_key, string $public_key, string $environment = null, string $service_provider_code = null, string $origin = null, string $initiatorIdentifier = null, string $securityCredential = null ) {
        self::$environment = $environment ?? self::$environment;
        self::$host =  self::$environment == 'development' ? self::$hostDeveloper : self::$hostProduction;
        self::$origin = $origin ?? self::$origin;
        self::$api_key = $api_key;
        self::$public_key = $public_key;
        self::$service_provider_code = $service_provider_code ?? self::$service_provider_code;
        self::$initiatorIdentifier = $initiatorIdentifier ?? self::$initiatorIdentifier;
        self::$securityCredential = $securityCredential ?? self::$securityCredential;
    }

    /**
     * @return string
     */
    public static function getEnvironment(): string 
    {
        return self::$environment;
    }

    /**
     * @return string
     */
    public static function getApiKey(): string 
    {
        return self::$api_key;
    }

    /**
     * @return string
     */
    public static function getPublicKey(): string 
    {
        return self::$public_key;
    }


    public static function getHost(): string 
    {
        return self::$host;
    }
    /**
     * @return string
     */
    public static function getServiceProviderCode(): string 
    {
        return self::$service_provider_code;
    }

    public static function getOrigin(): string
    {
        return self::$origin;
    }

    public static function getInitiatorIdentifier(): string 
    {
        return self::$initiatorIdentifier;
    }

    public static function getSecurityCredential(): string 
    {
        return self::$securityCredential;
    }
}