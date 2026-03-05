<?php

namespace Drupal\fake_store_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fake_store_ui\Service\FakeStoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controlador para mostrar productos de la FakeStoreAPI.
 */
class FakeStoreController extends ControllerBase
{

    /**
     * El servicio de la API.
     *
     * @var \Drupal\fake_store_ui\Service\FakeStoreService
     */
    protected FakeStoreService $apiClient;

    /**
     * Constructor con Dependency Injection.
     */
    public function __construct(FakeStoreService $api_client)
    {
        $this->apiClient = $api_client;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container): static
    {
        return new static (
            $container->get('fake_store_ui.api_client')
            );
    }

    /**
     * Muestra el primer producto obtenido de la API.
     *
     * @return array
     *   Un render array con el título del primer producto.
     */
    public function showProducts(): array
    {
        $products = $this->apiClient->getProducts();

        if (empty($products)) {
            return [
                '#markup' => $this->t('No se pudieron obtener productos de la API externa. Por favor, inténtelo más tarde.'),
            ];
        }

        $producto = $products[0];

        return [
            '#markup' => $this->t('Primer producto: @title', ['@title' => $producto['title']]),
        ];
    }

}
