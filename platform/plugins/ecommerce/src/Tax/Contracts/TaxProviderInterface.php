<?php

namespace Botble\Ecommerce\Tax\Contracts;

interface TaxProviderInterface
{
    public function name(): string;

    public function calculator(): TaxCalculatorInterface;

    public function priority(): int;
}
