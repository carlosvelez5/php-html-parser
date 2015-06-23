PHP Html Parser
==========================

PHPHtmlParser es un analizador simple y flexible de html , que le permite seleccionar las etiquetas que utilizan cualquier selector CSS , como jQuery. Este proyecto está basado en el proyecto de [paquettg/php-html-parser]( https://github.com/paquettg/php-html-parser) , que a su vez está basado en el proyecto original de [sunra/php-simple-html-dom-parser](https://github.com/sunra/php-simple-html-dom-parser) Versión 1.6.5

Cambios realizados
-------
Es importante resaltar los más importantes cambios realizados a las anteriores librerías...

1. Se puede indentar el código resultante en las funciones de `innerHtml` y `outerHtml`, valores por defecto: `$dom->outerHtml($indent = false, $pad = "\t", $newline = "\n", $level = 0);`. Para esto se establece el primer parámetro a `true`.
2. Se han agregado las funciones de `addClass`, `removeClass` y `hasClass`, para los elementos de instancia `Tag`. Todas estas aceptan cadenas de tipo `string`, con o sin espacios (múltiples clases), o de tipo `array`
3. Se crearon las funciones de `after`, `before`, `insertAfter`, `insertBefore`, `prepend`, `append`, `prependTo`, y `appendTo` que son los equivalentes a jQuery, para agregar nodos antes/después dentro/afuera, y que aceptan cadenas de tipo `string`, o instancias de clases `Tag` o `HtmlNode`
4. Agregada la función `wrap`, igualmente equivalente a `wrap` en jQuery
5. Ya se pueden utilizar una infinidad de selectores en la función `$dom->find( selector );`, incluyendo la gran mayoría de CSS.

```php
// Selectors
$selectors = array(
	// :nth-child
	'li:nth-child(odd):eq(1)',
	'li:nth-child(even):gt(1)',
	'li:nth-child(odd)',
	'li:nth-child(even)',
	'li:nth-child(3n+1)',
	'li:nth-child(2n+1)',
	'li:nth-child(2n)',
	'li:nth-child(3)',
	
	// combine
	'li:odd:first a',
	'li:even:last a:eq(0) i',
	'li:even:last a:eq(0) i:eq(1)',
	'li:odd:first',
	'li:even:last',
	
	// odd|even
	'li:odd',
	'li:even',
	'li',

	// first - last
	':last',
	'ul li:first',
	'ul li:last',
	'ul ul li:first',
	'ul ul li:last',
	'ul ul li a',
	
	// eq
	"ul li:eq(1)",
	"td:eq(2)",
	"td:eq(-2)",
	
	// gt
	"ul > li:gt(4)",
	"ul li:gt(-2)",
	"td:gt(4)",
	"td:gt(-2)",
	
	// lt
	"ul > li:lt(3)",
	"ul li:lt(-3)",
	"td:lt(4)",
	"td:lt(-2)",
	
	'[name*=man][attr1=\'value1\'][attr2^="value2"]',
	'div',
	'div div',
	'div + div',
	'ul#ul > li + li',
	'ul li',
	'ul#ul > *',
	'ul > *',
	'ul *',
	'#ul > li',
	'[lang|=en]',
	'[class~=fa]',
	'#input2 ~ input',
	'*[rel][style]', '*',
);
```

Instalación
-------

Es muy simple de instalar, solo basta con incluir el autoloader (`autoload.php`)

```php
<?php
// lib
require_once "autoload.php";

// Instancia
$dom = new Dom;

// Cargar
$dom->load( $html );
?>
```

Forma de uso
-----

Usted puede encontrar muchos ejemplos de cómo utilizar el analizador dom y cualquiera de sus partes (que es muy probable que nunca toque) en el directorio de pruebas. Las pruebas se realizaron utilizando PHPUnit y son muy pequeños, unas pocas líneas cada uno, y son un gran lugar para comenzar. Teniendo en cuenta que, yo todavía voy a estar mostrando algunos ejemplos de cómo se debe utilizar el paquete. El siguiente ejemplo es un uso muy simplista del paquete.

De igual forma, puedes encontrar más ejemplos de uso en el archivo llamado `test.php`

```php
// lib
require_once "autoload.php";

$dom = new Dom;
$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a = $dom->find('a')[0];
echo $a->text; // "click here"
```

Hay muchas maneras de obtener el mismo resultado de la cúpula, como `$dom->getElementsbyTag('a')[0]` o `$dom->find('a', 0)` que se pueden encontrar en las pruebas o en el propio código.

Cargando archivos
------------------


También puede cargar sin problemas un archivo en el Reino lugar de una cadena, que es mucho más conveniente y es como me salvo la mayoría de los desarrolladores estaré cargando al HTML.
El siguiente ejemplo está tomado de nuestra prueba y utiliza el archivo "big.html " que se encuentra allí.

```php
$dom = new Dom;
$dom->loadFromFile('tests/big.html');
$contents = $dom->find('.content-border');
echo count($contents); // 10

foreach ($contents as $content)
{
	// get the class attr
	$class = $content->getAttribute('class');
	
	// do something with the html
	$html = $content->innerHtml;

	// or refine the find some more
	$child   = $content->firstChild();
	$sibling = $child->nextSibling();
}
```

En este ejemplo se carga el HTML de big.html, una página real de encontrar en línea, y obtiene todas las clases de contenido de las fronteras para procesar. También muestra algunas cosas que puedes hacer con un nodo, pero no es una lista exhaustiva de los métodos que un nodo tiene disponibles.

Alternativamente, se puede usar el método `load()` para cargar el archivo . Se intentará encontrar el archivo usando `file_exists`, y, de ser exitosa , llamará `loadFromFile()`. Lo mismo se aplica a una URL y al método `loadFromUrl()`.

Te recomiendo mirar las librerías [paquettg/php-html-parser]( https://github.com/paquettg/php-html-parser) y [sunra/php-simple-html-dom-parser](https://github.com/sunra/php-simple-html-dom-parser) para obtener más información
