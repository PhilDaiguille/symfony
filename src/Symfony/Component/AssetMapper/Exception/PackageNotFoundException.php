<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\AssetMapper\Exception;

use Symfony\Component\AssetMapper\ImportMap\Resolver\ResolvedImportMapPackage;

/**
 * Thrown when packages cannot be found during importmap package resolution.
 *
 * When thrown during importmap:update, already resolved packages are available via
 * getResolvedPackages() to allow partial updates to continue.
 */
final class PackageNotFoundException extends RuntimeException
{
    /**
     * @param string[]                   $packageNames     The package specifiers that could not be found
     * @param ResolvedImportMapPackage[] $resolvedPackages Packages successfully resolved alongside these failures
     */
    public function __construct(
        private readonly array $packageNames,
        private readonly array $resolvedPackages = [],
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string[]
     */
    public function getPackageNames(): array
    {
        return $this->packageNames;
    }

    /**
     * @return ResolvedImportMapPackage[]
     */
    public function getResolvedPackages(): array
    {
        return $this->resolvedPackages;
    }
}
