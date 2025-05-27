## Table of contents
- [Rudra\Router\MiddlewareInterface](#rudra_router_middlewareinterface)
- [Rudra\Router\Router](#rudra_router_router)
- [Rudra\Router\RouterFacade](#rudra_router_routerfacade)
- [Rudra\Router\RouterInterface](#rudra_router_routerinterface)
- [Rudra\Router\Routing](#rudra_router_routing)
- [Rudra\Router\Traits\RouterAnnotationTrait](#rudra_router_traits_routerannotationtrait)
- [Rudra\Router\Traits\RouterRequestMethodTrait](#rudra_router_traits_routerrequestmethodtrait)
<hr>

<a id="rudra_router_middlewareinterface"></a>

### Class: Rudra\Router\MiddlewareInterface
| Visibility | Function |
|:-----------|:---------|
|abstract public|<em><strong>next</strong>( array $chainOfMiddlewares ): void</em><br>|


<a id="rudra_router_router"></a>

### Class: Rudra\Router\Router
##### implements [Rudra\Router\RouterInterface](#rudra_router_routerinterface)
| Visibility | Function |
|:-----------|:---------|
|public|<em><strong>set</strong>( array $route ): void</em><br>|
|private|<em><strong>handleRequestUri</strong>( array $route ): void</em><br>|
|private|<em><strong>handleRequestMethod</strong>(): void</em><br>|
|private|<em><strong>handlePattern</strong>( array $route  array $request ): array</em><br>|
|private|<em><strong>setCallable</strong>( array $route   $params ): void</em><br>|
|public|<em><strong>directCall</strong>( array $route   $params ): void</em><br>|
|private|<em><strong>callActionThroughReflection</strong>( ?array $params  string $action  object $controller ): void</em><br>|
|private|<em><strong>callActionThroughException</strong>(  $params   $action   $controller ): void</em><br>|
|public|<em><strong>handleMiddleware</strong>( array $chainOfMiddlewares ): void</em><br>|
|public|<em><strong>annotationCollector</strong>( array $controllers  bool $getter  bool $attributes ): ?array</em><br>|
|protected|<em><strong>handleAnnotationMiddleware</strong>( array $annotation ): array</em><br>|
|public|<em><strong>__construct</strong>( Rudra\Container\Interfaces\RudraInterface $rudra )</em><br>|
|public|<em><strong>rudra</strong>(): Rudra\Container\Interfaces\RudraInterface</em><br>|
|public|<em><strong>get</strong>( array $route ): void</em><br>|
|public|<em><strong>post</strong>( array $route ): void</em><br>|
|public|<em><strong>put</strong>( array $route ): void</em><br>|
|public|<em><strong>patch</strong>( array $route ): void</em><br>|
|public|<em><strong>delete</strong>( array $route ): void</em><br>|
|public|<em><strong>any</strong>( array $route ): void</em><br>|
|public|<em><strong>resource</strong>( array $route  array $actions ): void</em><br>|


<a id="rudra_router_routerfacade"></a>

### Class: Rudra\Router\RouterFacade
| Visibility | Function |
|:-----------|:---------|
|public static|<em><strong>__callStatic</strong>( string $method  array $parameters ): mixed</em><br>|


<a id="rudra_router_routerinterface"></a>

### Class: Rudra\Router\RouterInterface
| Visibility | Function |
|:-----------|:---------|
|abstract public|<em><strong>set</strong>( array $route ): void</em><br>|
|abstract public|<em><strong>directCall</strong>( array $route   $params ): void</em><br>|


<a id="rudra_router_routing"></a>

### Class: Rudra\Router\Routing
| Visibility | Function |
|:-----------|:---------|


<a id="rudra_router_traits_routerannotationtrait"></a>

### Class: Rudra\Router\Traits\RouterAnnotationTrait
| Visibility | Function |
|:-----------|:---------|
|public|<em><strong>annotationCollector</strong>( array $controllers  bool $getter  bool $attributes ): ?array</em><br>|
|protected|<em><strong>handleAnnotationMiddleware</strong>( array $annotation ): array</em><br>|


<a id="rudra_router_traits_routerrequestmethodtrait"></a>

### Class: Rudra\Router\Traits\RouterRequestMethodTrait
| Visibility | Function |
|:-----------|:---------|
|abstract public|<em><strong>set</strong>( array $route ): void</em><br>|
|public|<em><strong>get</strong>( array $route ): void</em><br>|
|public|<em><strong>post</strong>( array $route ): void</em><br>|
|public|<em><strong>put</strong>( array $route ): void</em><br>|
|public|<em><strong>patch</strong>( array $route ): void</em><br>|
|public|<em><strong>delete</strong>( array $route ): void</em><br>|
|public|<em><strong>any</strong>( array $route ): void</em><br>|
|public|<em><strong>resource</strong>( array $route  array $actions ): void</em><br>|
<hr>

###### created with [Rudra-Documentation-Collector](#https://github.com/Jagepard/Rudra-Documentation-Collector)
