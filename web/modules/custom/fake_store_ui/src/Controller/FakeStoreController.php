<?php

namespace Drupal\fake_store_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fake_store_ui\Service\FakeStoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controlador para mostrar y filtrar productos de la FakeStoreAPI.
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
     * Muestra los productos con filtrado por nombre y categoría.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   La petición HTTP actual.
     *
     * @return array
     *   Un render array con la plantilla de productos.
     */
    public function showProducts(Request $request): array
    {
        $all_products = $this->apiClient->getProducts();

        // Obtenemos los parámetros de filtrado desde la URL.
        $filter_name = trim($request->query->get('nombre', ''));
        $filter_cat = trim($request->query->get('cat', ''));

        // Extraemos las categorías únicas de todos los productos.
        $categories = [];
        foreach ($all_products as $product) {
            if (!empty($product['category'])) {
                $categories[$product['category']] = $product['category'];
            }
        }
        sort($categories);

        // Aplicamos filtros en PHP.
        $filtered = $all_products;

        if ($filter_name !== '') {
            $filtered = array_filter($filtered, function ($p) use ($filter_name) {
                return stripos($p['title'], $filter_name) !== FALSE;
            });
        }

        if ($filter_cat !== '') {
            $filtered = array_filter($filtered, function ($p) use ($filter_cat) {
                return $p['category'] === $filter_cat;
            });
        }

        return [
            '#theme' => 'fake_store_products',
            '#products' => array_values($filtered),
            '#categories' => $categories,
            '#filter_name' => $filter_name,
            '#filter_cat' => $filter_cat,
            '#attached' => [
                'library' => ['fake_store_ui/main'],
            ],
            '#cache' => [
                // No cacheamos la página porque depende de parámetros de la URL.
                'max-age' => 0,
            ],
        ];
    }

}
