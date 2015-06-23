<?php
// Incluir
define( '_CLASS_PATH', dirname( __FILE__ ) );

/**
 * Include the Class
 */
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Dom.php');

// Exceptions
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/ChildNotFoundException.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/CircularException.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/CurlException.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/NotLoadedException.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/ParentNotFoundException.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/StrictException.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Exceptions/UnknownChildTypeException.php');


require_once( _CLASS_PATH .'/src/PHPHtmlParser/Content.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Options.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Selector.php');


require_once( _CLASS_PATH .'/src/PHPHtmlParser/Dom/AbstractNode.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Dom/Collection.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Dom/TextNode.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Dom/HtmlNode.php');
require_once( _CLASS_PATH .'/src/PHPHtmlParser/Dom/Tag.php');

?>