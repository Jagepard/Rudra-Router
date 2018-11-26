## Table of contents

- [\Rudra\Router](#class-rudrarouter)
- [\Rudra\Interfaces\RouterInterface (interface)](#interface-rudrainterfacesrouterinterface)

<hr /><a id="class-rudrarouter"></a>
### Class: \Rudra\Router

> Class Router

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\Rudra\Interfaces\ContainerInterface</em> <strong>$container</strong>, <em>\string</em> <strong>$namespace</strong>)</strong> : <em>void</em><br /><em>Router constructor.</em> |
| public | <strong>annotation(</strong><em>\string</em> <strong>$controller</strong>, <em>\string</em> <strong>$action=`'actionIndex'`</strong>, <em>int/\integer</em> <strong>$number</strong>)</strong> : <em>void</em> |
| public | <strong>annotationCollector(</strong><em>array</em> <strong>$data</strong>, <em>\boolean</em> <strong>$multilevel=false</strong>)</strong> : <em>void</em> |
| public | <strong>any(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>void</em> |
| public | <strong>delete(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>void</em> |
| public | <strong>directCall(</strong><em>array</em> <strong>$route</strong>, <em>null</em> <strong>$params=null</strong>)</strong> : <em>void</em> |
| public | <strong>get(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>mixed</em> |
| public | <strong>handleMiddleware(</strong><em>array</em> <strong>$middleware</strong>)</strong> : <em>void</em> |
| public | <strong>patch(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>void</em> |
| public | <strong>post(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>void</em> |
| public | <strong>put(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>void</em> |
| public | <strong>resource(</strong><em>\string</em> <strong>$pattern</strong>, <em>\string</em> <strong>$controller</strong>, <em>array</em> <strong>$middleware=array()</strong>, <em>array</em> <strong>$actions=array()</strong>)</strong> : <em>void</em> |
| public | <strong>set(</strong><em>array</em> <strong>$route</strong>)</strong> : <em>void</em> |
| public | <strong>setNamespace(</strong><em>\string</em> <strong>$namespace</strong>)</strong> : <em>void</em> |
| protected | <strong>handleAnnotation(</strong><em>array</em> <strong>$data</strong>)</strong> : <em>void</em> |
| protected | <strong>handleAnnotationMiddleware(</strong><em>array</em> <strong>$annotation</strong>)</strong> : <em>array</em> |
| protected | <strong>handlePattern(</strong><em>array</em> <strong>$route</strong>, <em>array</em> <strong>$request</strong>)</strong> : <em>array</em> |
| protected | <strong>handleRequest(</strong><em>array</em> <strong>$route</strong>)</strong> : <em>void</em> |
| protected | <strong>matchRequest(</strong><em>array</em> <strong>$route</strong>)</strong> : <em>void</em> |
| protected | <strong>setCallable(</strong><em>array</em> <strong>$route</strong>, <em>mixed</em> <strong>$params</strong>)</strong> : <em>mixed</em> |
| protected | <strong>setClassName(</strong><em>\string</em> <strong>$className</strong>, <em>\string</em> <strong>$namespace</strong>)</strong> : <em>string</em> |
| protected | <strong>setRoute(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>, <em>\string</em> <strong>$httpMethod</strong>, <em>array</em> <strong>$middleware=array()</strong>)</strong> : <em>void</em> |
| protected | <strong>setRouteData(</strong><em>\string</em> <strong>$class</strong>, <em>\string</em> <strong>$method</strong>, <em>int/\integer</em> <strong>$number</strong>, <em>mixed</em> <strong>$result</strong>, <em>mixed</em> <strong>$httpMethod</strong>)</strong> : <em>array</em> |

*This class implements [\Rudra\Interfaces\RouterInterface](#interface-rudrainterfacesrouterinterface)*

<hr /><a id="interface-rudrainterfacesrouterinterface"></a>
### Interface: \Rudra\Interfaces\RouterInterface

> Interface RouterInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\Rudra\Interfaces\ContainerInterface</em> <strong>$container</strong>, <em>\string</em> <strong>$namespace</strong>)</strong> : <em>void</em><br /><em>RouterInterface constructor.</em> |
| public | <strong>annotation(</strong><em>\string</em> <strong>$class</strong>, <em>\string</em> <strong>$method</strong>, <em>int/\integer</em> <strong>$number</strong>)</strong> : <em>void</em> |
| public | <strong>any(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>)</strong> : <em>void</em> |
| public | <strong>delete(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>)</strong> : <em>void</em> |
| public | <strong>directCall(</strong><em>array</em> <strong>$classAndMethod</strong>, <em>null</em> <strong>$params=null</strong>)</strong> : <em>void</em> |
| public | <strong>get(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>)</strong> : <em>mixed</em> |
| public | <strong>patch(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>)</strong> : <em>void</em> |
| public | <strong>post(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>)</strong> : <em>void</em> |
| public | <strong>put(</strong><em>\string</em> <strong>$pattern</strong>, <em>mixed</em> <strong>$target</strong>)</strong> : <em>void</em> |
| public | <strong>resource(</strong><em>\string</em> <strong>$pattern</strong>, <em>\string</em> <strong>$controller</strong>, <em>array</em> <strong>$actions=array()</strong>)</strong> : <em>void</em> |
| public | <strong>set(</strong><em>array</em> <strong>$route</strong>)</strong> : <em>mixed</em> |

