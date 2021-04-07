# Guzzle Message Anonymizer Formatter

Anonymize parts of json or xml payloads and request headers before logging with a guzzle middleware.  
For data protection you might not want to log personal data, like names, addresses etc in your requests.

## Usage
Example usage for Json Payloads:

    ...
    $formatter = new GuzzleMessageJsonAnonymizerFormatter(
        ['PersonalData'],
        $substitute = '*****',
        AbstractAnonymizerFormatter::DEBUG,
        ['Authorization' => '*****']
    );
    $loggerMiddleware = Middleware::log($logger, $formatter);
    $stack->push($loggerMiddleware);
    $config  = [
        ...
        'handler'  => $stack,
    ];
    $client  = new Client($config);

This will log:

    <<<<<<<<
    HTTP/1.1 200 OK
    Date: Thu, 18 Jun 2020 10:04:21 GMT
    Content-Type: application/json
    Authorization: *****
    ...
    {
      ...
      "Payload": {
        "Foo": "Bar", 
        ...
        "PersonalData": "*****"
      }, 
      ...
    }


## Usage Symfony

Declare the Formatter service:

    Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageXmlAnonymizerFormatter:
      arguments:
        $elements:
          - 'none:FirstName'
          - 'none:LastName'
        $attributes:
          - 'none:Customer[@Age]'
        $namespaces:
          none: 'http://namespace.de/middleware/payment/'

Override existing message formatter of a logger middleware:

    csa_guzzle.logger.message_formatter:
      alias: 'Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageXMLAnonymizerFormatter'

Or declare a dedicated anonymizer_logger middleware:

    guzzle.middleware.anonymizer_logger:
      class: Closure
      factory: ['GuzzleHttp\Middleware', log]
      arguments: ['@monolog.logger', '@Freshcells\GuzzleMessageAnonymizerFormatter\GuzzleMessageXmlAnonymizerFormatter', 'info']
      tags:
        - { name: csa_guzzle.middleware, alias: anonymizer_logger }

