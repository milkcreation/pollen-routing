<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\ResponseInterface;
use Pollen\Support\Str;
use Pollen\View\ViewEngine;

class WpBaseController extends BaseController
{
    /**
     * Cartographie des méthodes de récupération des gabarits d'affichage
     * @see ./wp-includes/template-loader.php
     * @var array
     */
    protected $wpTemplateTags = [
        'is_embed'             => 'get_embed_template',
        'is_404'               => 'get_404_template',
        'is_search'            => 'get_search_template',
        'is_front_page'        => 'get_front_page_template',
        'is_home'              => 'get_home_template',
        'is_privacy_policy'    => 'get_privacy_policy_template',
        'is_post_type_archive' => 'get_post_type_archive_template',
        'is_tax'               => 'get_taxonomy_template',
        'is_attachment'        => 'get_attachment_template',
        'is_single'            => 'get_single_template',
        'is_page'              => 'get_page_template',
        'is_singular'          => 'get_singular_template',
        'is_category'          => 'get_category_template',
        'is_tag'               => 'get_tag_template',
        'is_author'            => 'get_author_template',
        'is_date'              => 'get_date_template',
        'is_archive'           => 'get_archive_template',
    ];

    /**
     * Répartiteur de requête HTTP.
     *
     * @return ResponseInterface
     */
    public function dispatch(): ResponseInterface
    {
        foreach (array_keys($this->wpTemplateTags) as $tag) {
            if ($tag() && ($response = $this->handleTag($tag, ...func_get_args()))) {
                return $response;
            }
        }
        return $this->response('Template unavailable', 404);
    }

    /**
     * Traitement de la requête HTTP.
     *
     * @param string $tag Indicateur de contexte d'affichage Wordpress.
     * @param array ...$args Liste des arguments dynamiques de requête HTTP
     *
     * @return ResponseInterface|null
     */
    public function handleTag(string $tag, ...$args): ?ResponseInterface
    {
        $method = Str::camel($tag);

        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }

        if ($template = call_user_func($this->wpTemplateTags[$tag])) {
            if ('attachment' === $tag) {
                remove_filter('the_content', 'prepend_attachment');
            }
        } else {
            $template = get_index_template();
        }

        if ($template = apply_filters('template_include', $template)) {
            $template = preg_replace(
                '#' . preg_quote(get_template_directory(), DIRECTORY_SEPARATOR) . '#',
                '',
                $template
            );
            return $this->response(
                (new ViewEngine(get_template_directory()))->render(
                    pathinfo($template, PATHINFO_FILENAME),
                    $this->params()->all()
                )
            );
        }
        return null;
    }
}