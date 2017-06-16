<?php

namespace Cjw\MultiSiteBundle\Routing;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\SignalSlot\Repository;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use RuntimeException;

class CrossDomainRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    const URL_ALIAS_ROUTE_NAME = 'ez_urlalias';

    /**
     * @var \eZ\Publish\Core\SignalSlot\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    protected $generator;

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    protected $requestContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\SignalSlot\Repository $repository
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator  $generator
     * @param \Symfony\Component\Routing\RequestContext     $requestContext
     * @param \Psr\Log\LoggerInterface                      $logger
     */
    public function __construct(
        Repository $repository,
        UrlAliasGenerator $generator,
        RequestContext $requestContext,
        LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->generator = $generator;
        $this->requestContext = $requestContext !== null ? $requestContext : new RequestContext();
        $this->logger = $logger;
    }

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException If no matching resource could be found
     */
    public function matchRequest(Request $request)
    {
        throw new ResourceNotFoundException('CrossDomainRouter does no matching');
    }

    /**
     * Generates a URL for a location, from the given parameters, using the standard eZ UrlAliasGenerator
     * When location is outside content tree root, map domain and rewrite URL.
     *
     * If the generator is not able to generate the URL, it must throw the RouteNotFoundException as documented below.
     *
     * @param string $name The name of the route or a Tag instance
     * @param mixed $parameters An array of parameters
     * @param bool $absolute Whether to generate an absolute URL
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \InvalidArgumentException
     *
     * @return string The generated URL
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        // Direct access to Location
        if ($name instanceof Location) {
            $location = $name;
            $path = $this->generator->generate($location, $parameters);
            if ($this->isLocationOutsideRootContentTree($location)) {
                $path = $this->mapDomain($path, $location);
            }

            return $path;
        }

        // Normal route name
        if ($name === self::URL_ALIAS_ROUTE_NAME) {
            if (isset($parameters['locationId'])) {
                $locationId = $parameters['locationId'];
                /** @var Location $location */
                $location = $this->repository->getLocationService()->loadLocation($locationId);
                unset($parameters['locationId']);
                $path = $this->generator->generate($location, $parameters);
                if ($this->isLocationOutsideRootContentTree($location)) {
                    $path = $this->mapDomain($path, $location);
                }

                return $path;
            }
            if (isset($parameters['contentId'])) {
                $contentId = $parameters['contentId'];
                /** @var Content $content */
                $content = $this->repository->getContentService()->loadContent($contentId);
                $location = $this->repository->getLocationService()->loadLocation($content->contentInfo->mainLocationId);
                unset($parameters['contentId']);
                $path = $this->generator->generate($location, $parameters);
                if ($this->isLocationOutsideRootContentTree($location)) {
                    $path = $this->mapDomain($path, $location);
                }

                return $path;
            }
            throw new InvalidArgumentException(
                "When generating a CrossDomain route, either a 'Location' or a 'locationId' must be provided."
            );
        }
        throw new RouteNotFoundException('CrossDomain router could not match route');
    }

    /**
     * When location is outside content tree root, map domain and rewrite URL.
     *
     * @param string $path
     * @param Location $location
     *
     * @return string
     */
    public function mapDomain($path, $location)
    {
        if ($this->configResolver->hasParameter('domain_map', 'cjwmultisite')) {
            $domainMap = $this->configResolver->getParameter('domain_map', 'cjwsite');
            $rootLocationId = explode('/', $location->pathString)[3];
            if (array_key_exists($rootLocationId, $domainMap)) {
                $scheme = $this->requestContext->getScheme() . '://';
                $pathArray = explode('/', substr($path, 1));
                $lang = array_shift($pathArray);
                $prefix = array_shift($pathArray);
                $host = $domainMap[$rootLocationId];
                $path = $scheme . $host . '/' . $lang . '/' . implode('/', $pathArray);
            }
        }

        return $path;
    }

    /**
     * Check if Location is outside current content tree.
     *
     * @param $location
     *
     * @return bool
     */
    public function isLocationOutsideRootContentTree($location)
    {
        $treeRootPathElement = '/' . $this->configResolver->getParameter('content.tree_root.location_id') . '/';

        return  strpos($location->pathString, $treeRootPathElement) === false;
    }

    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @return \Symfony\Component\Routing\RouteCollection A RouteCollection instance
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * Sets the request context.
     *
     * @param \Symfony\Component\Routing\RequestContext $context The context
     */
    public function setContext(RequestContext $context)
    {
        $this->requestContext = $context;
        $this->generator->setRequestContext($context);
    }

    /**
     * Gets the request context.
     *
     * @return \Symfony\Component\Routing\RequestContext The context
     */
    public function getContext()
    {
        return $this->requestContext;
    }

    /**
     * Tries to match a URL path with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded)
     *
     * @return array An array of parameters
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException If the resource could not be found
     * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedException If the resource was found but the request method is not allowed
     */
    public function match($pathinfo)
    {
        throw new RuntimeException("The CrossDomainController doesn't support the match() method. Please use matchRequest() instead.");
    }

    /**
     * Whether this generator supports the supplied $name.
     *
     * This check does not need to look if the specific instance can be
     * resolved to a route, only whether the router can generate routes from
     * objects of this class.
     *
     * @param mixed $name The route "name" which may also be an object or anything
     *
     * @return bool
     */
    public function supports($name)
    {
        return $name instanceof Location || $name === self::URL_ALIAS_ROUTE_NAME;
    }

    /**
     * Convert a route identifier (name, content object etc) into a string
     * usable for logging and other debug/error messages.
     *
     * @param mixed $name
     * @param array $parameters which should contain a content field containing a RouteReferrersReadInterface object
     *
     * @return string
     */
    public function getRouteDebugMessage($name, array $parameters = array())
    {
        if ($name instanceof RouteObjectInterface) {
            return 'Route with key ' . $name->getRouteKey();
        }

        if ($name instanceof SymfonyRoute) {
            return 'Route with pattern ' . $name->getPath();
        }

        return $name;
    }

    /**
     * Removes prefix from path.
     *
     * Checks for presence of $prefix and removes it from $path if found.
     *
     * @param string $path
     * @param string $prefix
     *
     * @return string
     */
    protected function removePathPrefix($path, $prefix)
    {
        if ($prefix !== '/' && mb_stripos($path, $prefix) === 0) {
            $path = mb_substr($path, mb_strlen($prefix));
        }

        return $path;
    }
}
