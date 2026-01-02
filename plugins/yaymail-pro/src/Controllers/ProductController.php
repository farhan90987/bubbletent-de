<?php

namespace YayMail\Controllers;

use YayMail\Abstracts\BaseController;
use YayMail\Models\ProductModel;
use YayMail\Utils\SingletonTrait;

/**
 * Product Controller
 *
 * @method static ProductController get_instance()
 */
class ProductController extends BaseController {
    use SingletonTrait;

    private $model = null;

    protected function __construct() {
        $this->model = ProductModel::get_instance();
        $this->init_hooks();
    }

    protected function init_hooks() {
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/product/featured-product',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_featured_products' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/product/categories',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_categories' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );

        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/product/tags',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_tags' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/product',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_products' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );
    }

    /**
     * Handle get featured products
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The response data.
     */
    public function exec_get_featured_products( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_featured_products' ], $request );
    }

    /**
     * Get featured products
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The featured products data.
     */
    public function get_featured_products( \WP_REST_Request $request ) {
        $params['number_of_products'] = sanitize_text_field( $request->get_param( 'number_of_products' ) );
        $params['product_type']       = sanitize_text_field( $request->get_param( 'product_type' ) );
        $params['sorted_by']          = sanitize_text_field( $request->get_param( 'sorted_by' ) );

        $params['category_ids'] = json_decode( sanitize_text_field( $request->get_param( 'category_ids' ) ) );
        $params['tag_ids']      = json_decode( sanitize_text_field( $request->get_param( 'tag_ids' ) ) );
        $params['product_ids']  = json_decode( sanitize_text_field( $request->get_param( 'product_ids' ) ) );

        $result = $this->model->get_featured_products( $params );

        return $result;
    }

    /**
     * Handle get product categories
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The response data.
     */
    public function exec_get_categories( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_categories' ], $request );
    }

    /**
     * Get product categories
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The product categories data.
     */
    public function get_categories( \WP_REST_Request $request ) {
        $params['search_string'] = sanitize_text_field( $request->get_param( 'search_string' ) );
        $params['page_num']      = sanitize_text_field( $request->get_param( 'page_num' ) );
        $params['page_size']     = sanitize_text_field( $request->get_param( 'page_size' ) );
        $params['term_type']     = 'product_cat';

        $result = $this->model->get_terms( $params, [ 'id' => 'term_id' ] );

        return $result;
    }


    /**
     * Handle get product tags
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The response data.
     */
    public function exec_get_tags( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_tags' ], $request );
    }

    /**
     * Get product tags
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The product tags data.
     */
    public function get_tags( \WP_REST_Request $request ) {
        $params['search_string'] = sanitize_text_field( $request->get_param( 'search_string' ) );
        $params['page_num']      = sanitize_text_field( $request->get_param( 'page_num' ) );
        $params['page_size']     = sanitize_text_field( $request->get_param( 'page_size' ) );
        $params['term_type']     = 'product_tag';
        $result                  = $this->model->get_terms( $params, [ 'id' => 'term_id' ] );

        return $result;
    }

    /**
     * Handle get products
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The response data.
     */
    public function exec_get_products( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_products' ], $request );
    }

    /**
     * Get products
     *
     * @param \WP_REST_Request $request The request object.
     * @return array The products data.
     */
    public function get_products( \WP_REST_Request $request ) {
        $params['search_string'] = sanitize_text_field( $request->get_param( 'search_string' ) );
        $params['page_num']      = sanitize_text_field( $request->get_param( 'page_num' ) );
        $params['page_size']     = sanitize_text_field( $request->get_param( 'page_size' ) );
        $result                  = $this->model->get_terms( $params );

        return $result;
    }
}
