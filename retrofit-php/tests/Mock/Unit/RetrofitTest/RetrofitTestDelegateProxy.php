<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Retrofit\Test\Mock\Unit\RetrofitTest;

use Tebru\Retrofit\Annotation\POST;
use Tebru\Retrofit\Call;
use Tebru\Retrofit\Http\MultipartBody;
use Tebru\Retrofit\Proxy;

/**
 * Class RetrofitTestProxy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class RetrofitTestDelegateProxy implements ApiClient, DefaultParamsApiClient, Proxy
{
    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * Constructor
     *
     */
    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function get(): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function uri(string $newUrl, array $queryMap, array $query1, bool $query2, string $path): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function headers(array $headerMap, array $header1, int $header2): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    /**
     * @POST("/")
     */
    public function postWithoutBody(): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function body(RetrofitTestRequestBodyMock $requestBody): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function field(float $field1, bool $field2, string $field3, array $fieldMap): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function part(RetrofitTestRequestBodyMock $part1, MultipartBody $part2, array $partMap): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function callAdapter(): RetrofitTestAdaptedCallMock
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    public function customAnnotation(): Call
    {
        return $this->__handleRetrofitRequest(ApiClient::class, __FUNCTION__, func_get_args(), []);
    }

    /**
     * @param string $string
     * @param bool $bool
     * @param int $int
     * @param float $float
     * @param array $array
     * @param ApiClient|null $client
     * @return Call
     */
    public function getWithDefaults(
        ?string $string = 'test',
        ?bool $bool = true,
        ?int $int = 1,
        ?float $float = 3.2,
        ?array $array = [],
        ?ApiClient $client = null
    ): Call {
        return $this->__handleRetrofitRequest(
            DefaultParamsApiClient::class,
            __FUNCTION__,
            func_get_args(),
            ['test', true, 1, 3.2, [], null]
        );
    }

    /**
     * Constructs a [@see Call] object based on an interface method and arguments, then passes it through a
     * [@see CallAdapter] before returning.
     *
     * @param string $interfaceName
     * @param string $methodName
     * @param array $args
     * @param array $defaultArgs
     * @return mixed
     */
    public function __handleRetrofitRequest(string $interfaceName, string $methodName, array $args, array $defaultArgs)
    {
        return $this->proxy->__handleRetrofitRequest($interfaceName, $methodName, $args, []);
    }
}
