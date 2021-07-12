<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\BinaryFileResponse;
use Pollen\Http\BinaryFileResponseInterface;
use Pollen\Http\JsonResponse;
use Pollen\Http\JsonResponseInterface;
use Pollen\Http\RedirectResponse;
use Pollen\Http\RedirectResponseInterface;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Support\DateTime;
use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Proxy\RouterProxy;
use Psr\Container\ContainerInterface as Container;
use SplFileInfo;
use InvalidArgumentException;

class BaseController
{
    use ContainerProxy;
    use HttpRequestProxy;
    use RouterProxy;

    protected ?ParamsBag $datasBag = null;

    /**
     * @param Container|null $container
     */
    public function __construct(?Container $container = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
        }
        $this->boot();
    }

    /**
     * Booting.
     *
     * @return void
     */
    public function boot(): void { }

    /**
     * @param ResponseInterface $response
     * @param int $expire
     *
     * @return ResponseInterface
     */
    protected function cachedResponse(ResponseInterface $response, int $expire = 60 * 60 * 24 * 365): ResponseInterface
    {
        $response->setSharedMaxAge($expire);
        $response->setMaxAge($expire);
        $response->setExpires((new DateTime())->addSeconds($expire));

        return $response;
    }

    /**
     * Returns a BinaryFileResponse allowing to download or display the file.
     *
     * @param SplFileInfo|string $file
     * @param string|null $fileName
     * @param string $disposition attachment|inline
     *
     * @return BinaryFileResponseInterface
     */
    protected function file(
        $file,
        string $fileName = null,
        string $disposition = 'attachment'
    ): BinaryFileResponseInterface {
        $response = new BinaryFileResponse($file);

        $filename = $fileName ?? $response->getFile()->getFilename();
        $response->headers->set ('Content-Type', $response->getFile()->getMimeType());
        $response->setContentDisposition($disposition, $filename);

        return $response;
    }

    /**
     * Returns a JsonResponse of json serialized data.
     *
     * @param string|array|object|null $data
     * @param int $status
     * @param array $headers
     *
     * @return JsonResponseInterface
     */
    protected function json($data = null, int $status = 200, array $headers = []): JsonResponseInterface
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Returns instance of datasBag|Set list of datas|Get a data value, associated with controller.
     *
     * @param array|string|null $key
     * @param mixed $default
     *
     * @return mixed|ParamsBag
     *
     * @throws \InvalidArgumentException
     */
    public function datas($key = null, $default = null)
    {
        if (!$this->datasBag instanceof ParamsBag) {
            $this->datasBag = new ParamsBag($this->defaultDatas());
        }

        if ($key === null) {
            return $this->datasBag;
        }

        if (is_string($key)) {
            return $this->datasBag->get($key, $default);
        }

        if (is_array($key)) {
            $this->datasBag->set($key);

            return $this->datasBag;
        }

        throw new InvalidArgumentException('Invalid DatasBag passed method arguments');
    }

    /**
     * List of default datas associated with controller.
     *
     * @return array
     */
    public function defaultDatas() : array
    {
        return [];
    }

    /**
     * Returns a RedirectResponse for an absolute url or a relative path.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     *
     * @return RedirectResponseInterface
     */
    protected function redirect(string $path = '/', int $status = 302, array $headers = []): RedirectResponseInterface
    {
        return new RedirectResponse($path, $status, $headers);
    }

    /**
     * Returns a RedirectResponse to the request referer.
     *
     * @param int $status
     * @param array $headers
     *
     * @return RedirectResponseInterface
     */
    protected function referer(int $status = 302, array $headers = []): RedirectResponseInterface
    {
        return $this->redirect($this->httpRequest()->headers->get('referer'), $status, $headers);
    }

    /**
     * Returns a Response object.
     *
     * @param string $content .
     * @param int $status
     * @param array $headers
     *
     * @return ResponseInterface
     */
    protected function response(string $content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Returns a RedirectResponse for a named route.
     *
     * @param string $name
     * @param array $params
     * @param bool $isAbsolute
     * @param int $status
     * @param array $headers
     *
     * @return RedirectResponseInterface
     */
    protected function route(
        string $name,
        array $params = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): RedirectResponseInterface {
        return $this->router()->getNamedRouteRedirect($name, $params, $isAbsolute, $status, $headers);
    }
}
