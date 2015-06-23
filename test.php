<?php
// lib
require_once "autoload.php";

// Muestra reultado
function showResult( ){
	// globals
	global $dom, $highlight;
	
	echo '<pre class="brush: xml; highlight: [',	implode( ', ', $highlight ),
		
							 //    outerHtml( indent = false, pad = "\t", newline = "\r\n" )
		']">', htmlentities( $dom->outerHtml( true, '  ' ) ), "</pre>";
}

function func( $name, $code ){
	// function
	echo '<h6><b>'. $name .'</b>&nbsp;&nbsp;&nbsp;';
	
	echo ' <code>',
		htmlentities( $code ).'</code>;</h6>';
}	
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Test -ParseHTML</title>
<script type="text/javascript" src="syntaxhighlighter_3.0.83/scripts/shCore.js"></script>
<script type="text/javascript" src="syntaxhighlighter_3.0.83/scripts/shBrushJScript.js"></script>
<script type="text/javascript" src="syntaxhighlighter_3.0.83/scripts/shBrushXml.js"></script>
<link type="text/css" rel="stylesheet" href="syntaxhighlighter_3.0.83/styles/shCoreDefault.css"/>
<script type="text/javascript">SyntaxHighlighter.all();</script>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,800italic,400,300,600,700,800" />
<style type="text/css">
* {
	margin: 0px;
}
#wrap {
	width: 75%;
	background-color: rgba(255,255,255,.7);
	padding: 40px;
	margin: 80px auto;
	border-radius: 6px;
	border: 1px solid #DFDFDF;
	box-shadow: 2px 2px 10px #E5E5E5;
}
body {
	background-color: #EBEBEB;
}
h4, h6, p, span {
	margin-bottom: 10px;
	font-family: 'Open Sans';
	font-size: 16px;
}
p {
	font-size: 13px;
}
h6 {
	font-size: 14px;
	margin-top: 15px;
	font-weight: 500;
}
h6 b {
	font-weight: 600;
}
</style>
</head>

<body>
<div id="wrap">
  <h4 align="center">Test -ParseHTML</h4>
  <?php
// Instancia
$dom = new Dom;


$html = <<<HTML
<ul id="ul" class="sitemap">
  <li><a href="/IEPN/publicaciones/">List 1, item 1<i> </i></a></li>
  <li>List 1, item 2</li>
  <li><a href="/IEPN/publicaciones/calentamiento-global/">List 1, item 3</a></li>
  <li><a href="#">List 1, item 4</a>
    <ul class="sitemap">
      <li class="separate"><a href="/IEPN/home/">List 2, item 1<i> </i></a></li>
      <li>List 2, item 2</li>
      <li><a href="/IEPN/">List 2, item 3</a></li>
    </ul>
  </li>
  <li><a>a1 List 1, item 5<i> i 1 - list 5, item 5</i></a></li>
  <li><a href="#">List 1, item 6</a>
</ul>
HTML;

// Cargar
$dom->load( $html );

echo	'<h4>Test Functions</h4><h6>HTML de entrada</h6>',
		'<pre class="brush: xml;">', htmlentities( $html ), "</pre>";

/**
 * prepend
 */
$ul		= $dom->find( 'ul' );
$ul		->prepend( '<li><a>new li</a></li>' );

// línea a resaltar
$highlight = [2, 8];

// mostrar
func( 'prepend', '$dom->find( \'ul\' )->prepend( \'<li><a>new li</a></li>\' )' );	showResult();


/**
 * con Tag
 */
$newli = new Tag( 'li' );
$newli	->id = 'id';

$ul		->prepend( $newli );
$newli	->addClass( 'class' );

$highlight = [2, 9];

// mostrar
func( 'prepend', '$dom->find( \'ul\' )->prepend( new Tag( \'li\' ) )' );	showResult();



/**
 * append
 */
$ul		= $dom->find( 'ul:first' );
$li	= '<li><a href="#">List 1, item 7</a></li><li><b>last li</b> </li>';
$ul		->append( $li );

// línea a resaltar
$highlight = [ 18, 19 ];

// mostrar
func( 'append', '$dom->find( \'ul:first\' )->append( \''.$li.'\' )' );	showResult();


/**
 * Con tags y HtmlNode 
 */
$parent	= new HtmlNode( 'ul' );

$li1	= new Tag( 'li' );
$li1	= new HtmlNode( $li1 );
$li2	= new HtmlNode( 'li' );

// agregar
$parent	->addChild( $li1 );
$parent	->append( $li2 );

// agregar a la ul
$ul		->append( $parent );

// set attr
$li1	->id = 'li1';
$li2	->id = 'li2';
$parent	->setAttribute( 'id', 'newul' );

// línea a resaltar
$highlight = [20, 21, 22, 23];

// mostrar
func( 'append', '$dom->find( \'ul:first\' )->append( \''.$parent . '\'' );	showResult();



/** 
 * instancia de Dom
 */
$str	= '<div id="newdiv" class="wrap"><div><b>new content</b></div><div><p>new p</p></div></div>';
$html	= new Dom;
$html	->load( $str );

$ul		->append( $html );
$html	->find( 'div' )->addClass( 'div' );

// línea a resaltar
$highlight = [24, 25, 26, 27, 28, 29];

// mostrar
func( 'append', '$dom->find( \'ul:first\' )->append( \''. $html->outerHtml() . '\'' );	showResult();



/**
 * before
 */
$li	= '<p>new p before li:nth-child(2)</p>';

// select li
$target	= $dom->find( 'ul li:nth-child(2)' );
$target	->before( $li );

// set Attr
/*$li		->addClass( [ 'class1', 'class2', 'class3' ] );
$li		->removeClass( 'class2 class3' );*/

// línea a resaltar
$highlight = [3, 11, 24];

// mostrar
func( 'before', '$dom->find( \'ul li:nth-child(2)\' )->before( \'' . $li . '\' )' );	showResult();



/**
 * after
 */
$node	= new HtmlNode( 'div' );
$node2	= new HtmlNode( 'div' );
$a	= new Tag( 'a' );

// agregar
$dom	->find( 'ul:eq(0)' )->after( $node );

// agregar
$node	->append( $a );
$node2	->insertBefore( $node );

// set Attr
$node	->addClass( 'class' );
$node2	->id = 'first-div';
$a		->setAttribute( 'href', 'javascript:;' );

// línea a resaltar
$highlight = [31, 32];

// Remover las lis menores a 3 ( las 2 primeras )
$dom	->find( 'ul:eq(0) > li:lt(3)' )->remove();

// mostrar
func( 'after', '$dom->find( \'ul:eq(0)\' )->after( \''. $node->outerHtml . '\' )' );
func( 'remove', '$dom->find( \'ul:eq(0) > li:lt(3)\' )->remove()', true );	showResult();



/**
 * insertBefore
 */
$a		= new HtmlNode( 'a' );
$tag 	= new HtmlNode( 'li' );

$tag	->addChild( $a );
$a		->addClass( 'class' );

// add
$lastLi	= $dom->find( 'ul:first > li:last', 0 );
$tag	->insertBefore( $lastLi );

// set Attr
$tag	->id = 'id';

// línea a resaltar
$highlight = [18];

// mostrar
func( 'insertBefore', '$tag->insertBefore( $dom->find( \'ul:first > li:last\', 0 ) )' );	showResult();


/**
 * insertAfter
 */
$a		= new HtmlNode( 'a' );
$a		->addClass( 'class2' );

$tag	= new Tag( 'li' );
$tag 	= new HtmlNode( $tag );
$tag	->addChild( $a );

// add
$tag	->insertAfter( $lastLi );

// set Attr
$tag	->id = 'id2';

// línea a resaltar
$highlight = [20];

// mostrar
func( 'insertAfter', '$tag->insertAfter( $dom->find( \'ul:first > li:last\', 0 ) )', true );	showResult();



/**
 * appendTo
 */
$a		= new HtmlNode( 'a' );
$a		->addClass( 'class3' );

$tag	= new Tag( 'p' );
$tag 	= new HtmlNode( $tag );
$tag	->addChild( $a );

// add
$tag	->appendTo( $lastLi );

// set Attr
$tag	->id = 'id3';
$tag	->other = 'other';

// remove all attrs
$a		->removeAttrs();

// línea a resaltar
$highlight = [20];

// mostrar
func( 'appendTo', '$tag->appendTo( $dom->find( \'ul:first > li:last\', 0 ) )', true );	showResult();



/**
 * prependTo
 */
$a		= new HtmlNode( 'a' );
$a		->addClass( 'class4' );

$tag	= new Tag( 'p' );
$tag 	= new HtmlNode( $tag );
$tag	->addChild( $a );

// add
$tag	= $tag->prependTo( $lastLi );

// set Attr
$tag	->id = 'id4';
$tag	->other = 'other';

// remove attr
unset( $tag->id );

// línea a resaltar
$highlight = [20];

// mostrar
func( 'prependTo', '$tag->prependTo( $dom->find( \'ul:first > li:last\', 0 ) )', true );	showResult();



/**
 * wrap
 */
$target = new HtmlNode( 'div' );
$div	= new HtmlNode( 'div' );
$tagp	= new HtmlNode( 'p' );
$em		= new HtmlNode( new Tag( 'em' ) );
$b		= new HtmlNode( new Tag( 'b' ) );

$target	->append( $div );
$div	->append( $tagp );
/*$tagp	->append( $em );
$em		->append( $b );*/

$lastLi	->wrap( $target /*'<div><div><p><em><b>f</b></em></p></div></div>'*/ );

$target	->addClass( 'wrap' );
$em		->addClass( 'em' );

// línea a resaltar
$highlight = [31,32,33,34,35,36,37,38,39,40];

// mostrar
func( 'wrap', '$lastLi->wrap( \'<div><div><p><em><b></b></em></p></div></div>\' )', true );	showResult();




$html = <<<HTML
<div id="root" class="bubble-comment">
  <div class="comment-username">
    <div class="row">
      <div rel="username" class="text_ellipsis col-md-12">
        <b class="tipsy-c" data-html="true" data-gravity="w" original-title="#info-185">CARLOS ( 卡洛斯 )  ツ ♫ ♥ ♪</b>
        <span id="info-185" class="user-cm-tex"><span>@Carlos David</span></span></div>
      <div rel="opt-comment" class="col-md-7" style="display: none;">
        <div align="right">
          <span class="reply-comment">
            <a class="tipsy-c" data-gravity="se" id="reply_comment_185" rel="reply_comment_a" href="#" original-title="Responder al Usuario"><i class="fa fa-reply"></i></a>
            <span class="wrap-like"></span>
            <a class="tipsy-c link_gris" data-gravity="se" id="view_reply_185" href="#" rel="view_reply_comment" original-title="Ver respuestas"></a>
          </span>
          <span class="remove-comment">
            <a class="tipsy-c" data-gravity="se" id="remove_comment_185" rel="remove_comment" style="display:none" href="#" original-title="Eliminar Comentario / Respuesta"><i class="fa fa-times"></i></a>
        </span></div>
      </div>
    </div>
    <div class="text-comment">
      <p>Características de la presencia del fenómeno El Niño, Incremento de la temperatura superficial del mar peruano, Incremento de la t...<a href="/IEPN/publicaciones/calentamiento-global/#item-185" style="color:#0066CC" class="enlaces_opc">Ver más</a></p>
      <div class="data-comment">
        <div class="sitemap"><i class="fa fa-sitemap"></i>
          <ul id="ul" class="sitemap">
            <li lang="en"><a href="/IEPN/publicaciones/">List 1, item 1<i> </i></a></li>
            <li lang="en-UK"class="separate">List 1, item 2</li>
            <li lang="english"><a href="/IEPN/publicaciones/calentamiento-global/">List 1, item 3</a></li>
            <li lang="es"><a href="#">List 1, item 4</a>
              <ul class="sitemap">
                <li class="separate"><a href="/IEPN/home/">List 2, item 1<i> </i></a></li>
                <li>List 2, item 2</li>
                <li><a href="/IEPN/">List 2, item 3</a></li>
              </ul>
            </li>
			<span>span</span>
            <li><a>a1 List 1, item 5<i> i 1 - list 5, item 5</i> <i> i 2 - list 5, item 5</i></a><a>a 2, list 5, item 5</a></li>
			<li lang="es"><a href="#">List 1, item 6</a>
          </ul>
        </div>
        <span data-gravity="w" class="tipsy-c" original-title="01 de Junio del 2015, A las 16:54:11"><i class="fa fa-calendar"></i>
        <d livestamp="hace 2 días">hace 2 días</d>
        .</span></div>
    </div>
  </div>
</div>
<input attr1="value1" attr2="value2 value4" name="man-news">
<input id="input2" attr1="value1" name="milk man">
<input attr1="value1" attr2="value2" attr3="value3" attr4="value4 value5" name="letterman2">
<input name="newmilk">
<table border="1">
  <tr><td>TD #0</td><td>TD #1</td><td>TD #2</td></tr>
  <tr><td>TD #3</td><td>TD #4</td><td>TD #5</td></tr>
  <tr><td>TD #6</td><td>TD #7</td><td><p><span>TD</span> <i>#8</i></p></td></tr>
</table>
<span original-title="12/11/2015"> </span>
<p>Hola Mundo!</p>
<div id="items">
  <div id='div1'><item>1</item></div>
  <div id='div2'><item>2</item></div>
  <div><item>3</item></div>
</div>
<div><span class="tipsy-c a" original-title="12/11/2015"><i class="fa fa-calendar"></i> <i livestamp="hace 2 días">hace 2 días</i> </span></div>
HTML;
				  
// cargar
$dom->load( $html );

// Mostrar 
echo '<br><h4>Test Selectors</h4><h6>Html de Entrada</h6>',
	'<pre class="brush: xml;">', htmlentities( $dom->outerHtml( true ) ), "</pre>";


// Selectors
$selectors = array(
	// :nth-child
	'li:nth-child(odd):first',
	'li:nth-child(even):last',
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

foreach( $selectors as $selector ){
	//
	$items = $dom->find( $selector );
	
	echo "<h6>Selector <b>$selector</b></h6>";
	echo '<p>Resultado: <b>(' . $items->count() .')</b><p>';
	
	$items->each( function( $value, $key ){
		echo '<pre class="brush: xml; first-line: '.($key + 1 ).'">', htmlentities( $value->outerHtml ), '</pre>';
	});
}

echo '<h4>Html Final</h4>',
	'<pre class="brush: xml;">', htmlentities( $dom->outerHtml( true, '  ' ) ), "</pre>";
?>
</div>
</body>
</html>