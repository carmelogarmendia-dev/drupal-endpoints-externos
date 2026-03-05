<?php

namespace Drupal\fake_store_ui\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Servicio para consumir la FakeStoreAPI.
 */
class FakeStoreService
{

    /**
     * El cliente HTTP (Guzzle).
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected ClientInterface $httpClient;

    /**
     * El backend de caché de datos.
     *
     * @var \Drupal\Core\Cache\CacheBackendInterface
     */
    protected CacheBackendInterface $cache;

    /**
     * El canal de logs.
     *
     * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
     */
    protected LoggerChannelFactoryInterface $loggerFactory;

    /**
     * Clave de caché usada para almacenar los productos.
     */
    const CACHE_KEY = 'fake_store_ui:products';

    /**
     * Tiempo de expiración de la caché: 5 minutos.
     */
    const CACHE_TTL = 300;

    /**
     * Constructor con Dependency Injection.
     */
    public function __construct(
        ClientInterface $http_client,
        CacheBackendInterface $cache,
        LoggerChannelFactoryInterface $logger_factory
        )
    {
        $this->httpClient = $http_client;
        $this->cache = $cache;
        $this->loggerFactory = $logger_factory;
    }

    /**
     * Obtiene el listado de productos desde la FakeStoreAPI.
     *
     * Usa caché para evitar peticiones redundantes (TTL: 5 minutos).
     *
     * @return array
     *   Array de productos o array vacío si hay error.
     */
    public function getProducts(): array
    {
        // Comprobamos si ya tenemos los datos en caché.
        if ($cached = $this->cache->get(self::CACHE_KEY)) {
            return $cached->data;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://fakestoreapi.com/products');
            $data = json_decode($response->getBody()->getContents(), TRUE);

            if (!is_array($data)) {
                $data = [];
            }

            // Guardamos en caché con expiración de 5 minutos.
            $this->cache->set(self::CACHE_KEY, $data, time() + self::CACHE_TTL);

            return $data;
        }
        catch (RequestException $e) {
            $this->loggerFactory->get('fake_store_ui')->error(
                'Error al conectar con la FakeStoreAPI: @message',
            ['@message' => $e->getMessage()]
            );
            return [];
        }
    }

}
