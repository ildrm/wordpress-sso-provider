<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress\Rest;

use InvalidArgumentException;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WpSsoProvider\Application\ApplicationService;

final class ApplicationsController
{
    public function __construct(private readonly ApplicationService $service)
    {
    }

    public function register(): void
    {
        register_rest_route(
            'wp-sso-provider/v1',
            '/applications',
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'index'],
                    'permission_callback' => static fn (): bool => current_user_can('manage_sso_provider'),
                ],
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'create'],
                    'permission_callback' => static fn (): bool => current_user_can('manage_sso_provider'),
                    'args' => [
                        'name' => ['type' => 'string', 'required' => true],
                        'type' => ['type' => 'string', 'required' => true],
                        'redirect_uris' => ['type' => 'array', 'default' => []],
                        'allowed_scopes' => ['type' => 'array', 'default' => ['openid', 'profile', 'email']],
                    ],
                ],
            ]
        );
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        unset($request);
        return new WP_REST_Response(['items' => $this->service->list(get_current_blog_id())], 200);
    }

    public function create(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        try {
            $params = $this->normalizeParams($request->get_json_params());
            $result = $this->service->create($params, get_current_blog_id(), get_current_user_id());
            return new WP_REST_Response($result, 201);
        } catch (InvalidArgumentException $exception) {
            return new WP_Error('wp_sso_invalid_application', $exception->getMessage(), ['status' => 400]);
        } catch (Throwable $exception) {
            return new WP_Error(
                'wp_sso_application_create_failed',
                __('The application could not be created. Use the request ID in the activity log for support.', 'wordpress-sso-provider'),
                ['status' => 500]
            );
        }
    }

    /**
     * @param array<array-key, mixed> $params
     * @return array<string, mixed>
     */
    private function normalizeParams(array $params): array
    {
        $normalized = [];
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}
