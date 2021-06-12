<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;

class ResponseBody extends ResponseCodes
{
    /**
     * Associative array of the request
     */
    private static ?array $parsedRequest = null;

    /**
     * @var bool
     */
    protected bool $isAuthenticated = false;

    /**
     * @var bool
     */
    protected bool $isAdmin = false;

    /**
     * The response data
     *
     * @var array | null
     */
    protected ?array $data = null;

    /**
     * HTTP status code
     *
     * @var int
     */
    protected int $status = 200;

    /**
     * Response informational string
     *
     * @var string
     */
    protected string $message = '';

    /**
     * Missing parameters
     *
     * @var array
     */
    protected array $missing = [];

    public function __construct(array $parsedRequest) {
        self::setParsedRequest($parsedRequest);
    }

    /**
     * Generate the response
     */
    public function __invoke(): ResponseInterface {
        $payload = [
            'authenticated' => $this->isAuthenticated,
            'success' => ($this->status === 200),
            'status' => $this->status,
            'data' => $this->data,
            'missing' => $this->missing,
            'message' => $this->message,
            'timestamp' => time()
        ];

        return (
            new Response(
                $this->status,
                new Headers(['content-type' => 'application\json']),
                (new StreamFactory())->createStream(json_encode($payload))
            )
        );
    }

    /**
     * Set the parsed request array
     *
     * @param array $parsedRequest
     */
    final public static function setParsedRequest(array $parsedRequest): void {
        if (self::$parsedRequest === null) {
            self::$parsedRequest = $parsedRequest;
        }
    }

    /**
     * Returned the parsed request
     *
     * @return array
     */
    final public function getParsedRequest(): array {
        return self::$parsedRequest;
    }

    /**
     * Indicate that the request is an administrator
     *
     * @return ResponseBody
     */
    final public function setIsAdmin(): self {
        $clone = clone $this;
        $clone->isAdmin = true;
        return $clone;
    }

    /**
     * Returns true if the current authenticated user is an admin, false otherwise.
     *
     * @return bool
     */
    final public function getIsAdmin(): bool {
        return ($this->isAdmin);
    }

    /**
     * Indicate that the request is authenticated
     *
     * @return ResponseBody
     */
    final public function setIsAuthenticated(): self {
        $clone = clone $this;
        $clone->isAuthenticated = true;
        return $clone;
    }

    /**
     * Returns true if the request is authenticated
     *
     * @return bool
     */
    final public function getIsAuthenticated(): bool {
        return $this->isAuthenticated;
    }

    /**
     * Returns true if there are missing or required datapoints in the request
     *
     * @return bool
     */
    final public function hasMissingRequiredOrInvalid(): bool {
        return (isset($this->missing['invalid']) || isset($this->missing['required']));
    }

    /**
     * Register a parameter as optional, required or invalid.
     *
     * @param string $section
     * @param string $name
     * @param string | null $type
     */
    final public function registerParam(string $section, string $name, ?string $type): void {
        assert(in_array($section, ['optional', 'required', 'invalid']));
        assert($name !== '');

        if ($type === null) {
            $type = 'unknown';
        }

        $data = $this->missing[$section] ?? [];
        $data[$name] = $data[$name] ?? $type;
        $this->missing[$section] = $data;
    }

    /**
     * Register multiple parameters as optional, required, or invalid.
     *
     * @param string $section
     * @param array $names
     * @param string $type
     */
    final public function registerParams(string $section, array $names, string $type): void {
        foreach ($names as $name) {
            $this->registerParam($section, $name, $type);
        }
    }

    /**
     * Set the response data.
     *
     * @param array|null $data
     * @return ResponseBody
     */
    final public function setData(?array $data): self {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    /**
     * Set the response status code.
     *
     * @param int $status
     * @return self
     */
    final public function setStatus(int $status): self {
        assert($status > 99 && $status < 1000);

        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    /**
     * Return the http status code
     *
     * @return int
     */
    final public function getStatus(): int {
        return $this->status;
    }

    /**
     * Set the reponse message
     *
     * @param string $message
     * @return ResponseBody
     */
    final public function setMessage(string $message): self {
        assert($message !== '');

        $clone = clone $this;
        $clone->message = $message;
        return $clone;
    }
}
