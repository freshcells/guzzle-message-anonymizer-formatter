<?php

namespace Freshcells\GuzzleMessageAnonymizerFormatter;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Psr7;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAnonymizerFormatter extends MessageFormatter
{
    protected $elements = [];
    protected $attributes = [];
    protected $substitute;
    protected $headersToSubstitute = [];

    /** @var string Template used to format log messages */
    protected $template;

    abstract protected function hidePrivateData(string $content);

    public function __construct(
        array $elements,
        array $attributes = [],
        string $substitute = '*****',
        string $template = self::DEBUG,
        array $headersToSubstitute = []
    ) {
        $this->elements            = $elements;
        $this->attributes          = $attributes;
        $this->substitute          = $substitute;
        $this->template            = $template;
        $this->headersToSubstitute = $headersToSubstitute;
    }

    /**
     * Returns a formatted message string.
     *
     * @param RequestInterface $request   Request that was sent
     * @param ResponseInterface $response Response that was received
     * @param \Exception $error           Exception that was received
     *
     * @return string
     */
    public function format(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $error = null
    ) {
        $cache = [];

        return preg_replace_callback(
            '/{\s*([A-Za-z_\-.0-9]+)\s*}/',
            function (array $matches) use ($request, $response, $error, &$cache) {
                if (isset($cache[$matches[1]])) {
                    return $cache[$matches[1]];
                }

                $result = '';
                switch ($matches[1]) {
                    case 'request':
                        $result = $this->anonymizeStr($request);
                        break;
                    case 'response':
                        $result = $response ? $this->anonymizeStr($response) : '';
                        break;
                    case 'req_headers':
                        $result = trim($request->getMethod()
                                .' '.$request->getRequestTarget())
                            .' HTTP/'.$request->getProtocolVersion()."\r\n"
                            .$this->headers($request);
                        break;
                    case 'res_headers':
                        $result = $response ?
                            sprintf(
                                'HTTP/%s %d %s',
                                $response->getProtocolVersion(),
                                $response->getStatusCode(),
                                $response->getReasonPhrase()
                            )."\r\n".$this->headers($response)
                            : 'NULL';
                        break;
                    case 'req_body':
                        $result = $request->getBody();
                        break;
                    case 'res_body':
                        $result = $response ? $response->getBody() : 'NULL';
                        break;
                    case 'ts':
                    case 'date_iso_8601':
                        $result = gmdate('c');
                        break;
                    case 'date_common_log':
                        $result = date('d/M/Y:H:i:s O');
                        break;
                    case 'method':
                        $result = $request->getMethod();
                        break;
                    case 'req_version':
                    case 'version':
                        $result = $request->getProtocolVersion();
                        break;
                    case 'uri':
                    case 'url':
                        $result = $request->getUri();
                        break;
                    case 'target':
                        $result = $request->getRequestTarget();
                        break;
                    case 'res_version':
                        $result = $response
                            ? $response->getProtocolVersion()
                            : 'NULL';
                        break;
                    case 'host':
                        $result = $request->getHeaderLine('Host');
                        break;
                    case 'hostname':
                        $result = gethostname();
                        break;
                    case 'code':
                        $result = $response ? $response->getStatusCode() : 'NULL';
                        break;
                    case 'phrase':
                        $result = $response ? $response->getReasonPhrase() : 'NULL';
                        break;
                    case 'error':
                        $result = $error ? $error->getMessage() : 'NULL';
                        break;
                    default:
                        // handle prefixed dynamic headers
                        if (strpos($matches[1], 'req_header_') === 0) {
                            $result = $request->getHeaderLine(substr($matches[1], 11));
                        } elseif (strpos($matches[1], 'res_header_') === 0) {
                            $result = $response
                                ? $response->getHeaderLine(substr($matches[1], 11))
                                : 'NULL';
                        }
                }

                $cache[$matches[1]] = $result;

                return $result;
            },
            $this->template
        );
    }

    /**
     * override GuzzleHttp\Psr7\str(MessageInterface $message)
     * @param MessageInterface $message
     * @return string
     */
    protected function anonymizeStr(MessageInterface $message)
    {
        if ($message instanceof RequestInterface) {
            $msg = trim($message->getMethod().' '
                    .$message->getRequestTarget())
                .' HTTP/'.$message->getProtocolVersion();
            if (!$message->hasHeader('host')) {
                $msg .= "\r\nHost: ".$message->getUri()->getHost();
            }
        } elseif ($message instanceof ResponseInterface) {
            $msg = 'HTTP/'.$message->getProtocolVersion().' '
                .$message->getStatusCode().' '
                .$message->getReasonPhrase();
        } else {
            throw new \InvalidArgumentException('Unknown message type');
        }

        foreach ($message->getHeaders() as $name => $values) {
            if (isset($this->headersToSubstitute[$name])) {
                $msg .= "\r\n{$name}: ".$this->headersToSubstitute[$name];
            } else {
                $msg .= "\r\n{$name}: ".implode(', ', $values);
            }
        }

        return "{$msg}\r\n\r\n".$this->hidePrivateData($message->getBody());
    }

    protected function headers(MessageInterface $message)
    {
        $result = '';
        foreach ($message->getHeaders() as $name => $values) {
            if (isset($this->headersToSubstitute[$name])) {
                $result .= $name.': '.$this->headersToSubstitute[$name]."\r\n";
            } else {
                $result .= $name.': '.implode(', ', $values)."\r\n";
            }
        }

        return trim($result);
    }
}
