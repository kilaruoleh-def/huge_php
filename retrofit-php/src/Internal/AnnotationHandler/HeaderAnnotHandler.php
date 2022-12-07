<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Retrofit\Internal\AnnotationHandler;

use InvalidArgumentException;
use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\Retrofit\AnnotationHandler;
use Tebru\Retrofit\Converter;
use Tebru\Retrofit\Internal\ParameterHandler\HeaderParamHandler;
use Tebru\Retrofit\ServiceMethodBuilder;
use Tebru\Retrofit\StringConverter;

/**
 * Class HeaderAnnotHandler
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class HeaderAnnotHandler implements AnnotationHandler
{
    /**
     * Adds header param handler
     *
     * @param AbstractAnnotation $annotation The annotation to handle
     * @param ServiceMethodBuilder $serviceMethodBuilder Used to construct a [@see ServiceMethod]
     * @param Converter|StringConverter $converter Converter used to convert types before sending to service method
     * @param int|null $index The position of the parameter or null if annotation does not reference parameter
     * @return void
     * @throws \InvalidArgumentException
     */
    public function handle(
        AbstractAnnotation $annotation,
        ServiceMethodBuilder $serviceMethodBuilder,
        ?Converter $converter,
        ?int $index
    ): void {
        if (!$converter instanceof StringConverter) {
            throw new InvalidArgumentException(sprintf(
                'Retrofit: Converter must be a StringConverter, %s found',
                \gettype($converter)
            ));
        }

        $serviceMethodBuilder->addParameterHandler(
            $index,
            new HeaderParamHandler($converter, $annotation->getValue())
        );
    }
}
