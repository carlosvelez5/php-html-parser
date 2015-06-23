<?php
/*namespace PHPHtmlParser;

use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\Exceptions\ChildNotFoundException;*/

class Selector {

	/** 
	 * Pattern of CSS selectors, modified from mootools
	 *
	 * @var string
	 */
	protected $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
	
	protected $whites				= '/([\x20\t\r\n\f]+)\1/';	// coma: \x2C
	protected $whitesRepeat			= '/([\x20\t\r\n\f]+)\1?/';
	
	protected $whitespace			= '[\x20\t\r\n\f]'; 
	protected $characterEncoding	= '/(?:\\.|[\w-]|[^\x00-\xa0])+/';
	protected $tabnewsline			= '/([\t\r\n\f]+)/';
	protected $commaRepeat			= '/([\x2C]+)\1/';
	protected $splitComma			= '/[\x2C]+/'; // | en utf -> \x7c     /[\x2C\x7c]+/
	
	protected $splitSelectorT		= '/^.*[\x2e\x23]+/';
	protected $splitSelectorN		= '/^(.*)[\x2e\x23]+.*/';	
	
	protected $master				= '(?:[\x20\t\r\n\f]*([\x3e\x7e\x2b\x20]+)?[\x20\t\r\n\f]*)((?:(?:\\.|[\w-]|[^\x00-\xa0])+)?(?:\.|\#)?(?:(?:\\.|[\w-]|[^\x00-\xa0])+))?((?:\[[\x20\t\r\n\f]*(?:(?:\\.|[\w-]|[^\x00-\xa0])+)(?:[\x20\t\r\n\f]*(?:[*^$|!~]?=)[\x20\t\r\n\f]*(?:\'(?:(?:\\.|[^\\\'])*)\'|"(?:(?:\\.|[^\\"])*)"|(?:(?:\\.|[\w#-]|[^\x00-\xa0])+))|)[\x20\t\r\n\f]*\])*)((?:(?:[\w-:\*]*)(?:\([\x20\t\r\n\f]*(?:even|odd|(?:(?:[+-]|)(?:\d*)n|)[\x20\t\r\n\f]*(?:(?:[+-]|)[\x20\t\r\n\f]*(?:\d+)|))[\x20\t\r\n\f]*\)|))*)';
	
	// capturando
	protected $attrs				= '(?:\[[\x20\t\r\n\f]*((?:\\.|[\w-]|[^\x00-\xa0])+)(?:[\x20\t\r\n\f]*([*^$|!~]?=)[\x20\t\r\n\f]*(?:\'((?:\\.|[^\\\'])*)\'|"((?:\\.|[^\\"])*)"|((?:\\.|[\w#-]|[^\x00-\xa0])+))|)[\x20\t\r\n\f]*\])';
	
	// subfunct
	protected $subfunction			= '(?:([\w-:\*]*)(?:\([\x20\t\r\n\f]*(even|odd|(([+-]|)(\d*)n|)[\x20\t\r\n\f]*(?:([+-]|)[\x20\t\r\n\f]*(\d+)|))[\x20\t\r\n\f]*\)|))';
	
	/**
	 * Separar por + > ~ y Espacio
	 */
	protected $splitAd				= '/[\x3e\x7e\x2b\x20]+/';
	
	/**
	 * h1[title],	span[class="example"], 	span[hello="Cleveland"] [goodbye="Columbus"]
	 * a[rel~="copyright"],		a[href="http://www.w3.org/"],	a[hreflang=fr],	a[hreflang|="en"]
	 * DIALOGUE[character*='romeo']
	 * DIALOGUE[character$=juliet]
	 * Attribute selectors: http://www.w3.org/TR/selectors/#attribute-selectors
	 */
	protected $attributes			= '\[[\x20\t\r\n\f]*((?:\\.|[\w-]|[^\x00-\xa0])+)(?:[\x20\t\r\n\f]*([*^$|!~]?=)[\x20\t\r\n\f]*(?:\'((?:\\.|[^\\\'])*)\'|"((?:\\.|[^\\"])*)"|((?:\\.|[\w#-]|[^\x00-\xa0])+))|)[\x20\t\r\n\f]*\]';
		
	
	protected $selectors = [];
	
	/**
	 * 
	 */
	public $defaulSelector = array(
		"tag"			=> NULL,	// div
		"key"			=> NULL,	// # o . ( id o class )
		"val"			=> "",		// class o id
		"type"			=> NULL,	// ~ espacio > y +
		"operatorS"		=> NULL,	// =,!=,|=,^=,$=,~=
		"idId"			=> false,
		
		// attrs
		"attrs"			=> NULL,
		"filters"		=> NULL
	);

	/**
	 * Constructs with the selector string
	 *
	 * @param string $selector
	 */
	public function __construct($selector)
	{
		$this->parseSelectors( $selector );
	}

	/**
	 * Returns the selectors that where found in __construct
	 *
	 * @return array
	 */
	public function getSelectors()
	{
		return $this->selectors;
	}

	/**
	 * Attempts to find the selectors starting from the given
	 * node object.
	 *
	 * @param Node $noda
	 * @return array
	 */
	public function find( $node, $whiteTextNode = true )
	{
		$results = new Collection;

		foreach ( $this->selectors as $selector )
		{
			$nodes = [$node];	$ids = [];
			
			if (count($selector) == 0)
				continue;
			
			foreach ($selector as $key => $rule)
			{
				$nodes = $this->seek( $nodes, $rule, @$selector[ $key+1 ], $key, $whiteTextNode );				
			}
			
			if( is_array( $nodes ) )
			{
				// this is the final set of nodes
				foreach ($nodes as $result)
				{
					if( !in_array( $result->id(), $ids ) /*&& !is_null( $result->outerHtml() )*/ )
					{
						$results[] = $result;
						
						//add
						$ids[] = $result->id();
					}
				}
			}
			else if( !is_null( $nodes ) )
			{
				$results[] = $nodes;
			}
		}
		
		return $results;
	}
	
	/**
	 * Obtener hijos de elementos
	 */
	protected function getChildrens( $nodes, $levels = false ){
		// nada
		if( count( $nodes ) == 0 )
			return [];
		
		$return = [];
		
		foreach( $nodes as $node ){
			// no tiene nodos
			if ( ! $node->hasChildren() )
				continue;
			
			$child = $node->firstChild();
			
			while ( ! is_null( $child ) )
			{
				$return[] = $child;
				
				if( $levels )
				{
					// recursive
					$submatches = $this->getChildrens( [ $child ], $levels );
					
					if ( count( $submatches ) > 0 ) {
						foreach ( $submatches as $submatch )
						{
							$return[] = $submatch;
						}
					}
				}
				
				try
				{
					// get next child
					$child = $node->nextChild( $child->id() );
				}
				catch (ChildNotFoundException $e)
				{
					// no more children
					$child = NULL;
				}
			}
		}
		
		return $return;
	}

	/**
	 * Parses the selector string
	 *
	 * @param string $selector
	 */
	protected function parseSelectors( $selector )
	{
		// Eliminar espacios, tab repetidos
		$selector = preg_replace( $this->whitesRepeat, "\x20", trim( $selector ) );
		
		// Eliminar saltos de línea y tabs
		$selector = preg_replace( $this->tabnewsline, "", $selector );
		
		// Reeplazar comas repetidas
		$selector = preg_replace( $this->commaRepeat, "\x2C", $selector );
		
		// separar por comas
		$parts = preg_split( $this->splitComma, $selector );
		$selectors = [];		$i = 0;
		
		// Recoorer
		foreach( $parts as &$selector ){
			// Eliminar espacios en blanco
			$selector = trim( $selector );
						
			// Separar por esparios > + ~
			$preg = preg_match_all( '/'. $this->master . '/is', $selector, $selector, PREG_SET_ORDER );
			
			if( $preg ){
				// Recorrer
				foreach( $selector as $item ){
					// * Global
					if( !empty( $item[ 4 ] ) && substr( $item[ 4 ], 0, 1 ) === "*" && empty( $item[ 2 ] ) ){
						$item[ 2 ] = substr( $item[ 4 ], 0, 1 );
						$item[ 4 ] = substr( $item[ 4 ], 1 );	
					}
					
					// Ordenar 
					if( isset( $item[0] ) && !empty( $item[0] ) ){					
						// vars
						$sel = array();		$operator = $item[1];
						$sel[ "type" ]		= preg_replace( $this->whitesRepeat, '', $operator );
						
						// selector  div#id  span.class
						if( !empty( $item[2] ) ){
							// Separar
							$sel[ "val" ] = preg_replace( $this->splitSelectorT, "", $item[2] );
							$sel[ "tag" ] = preg_replace( $this->splitSelectorN, "$1", $item[2] );
							$control = $sel[ "val" ] == $item[2];
							$isId = !!preg_match( '/\x23/', $item[2] );
							
							$sel[ "key" ]			= $control ? NULL : $isId ? "id" : "class";	
							$sel[ "operatorS" ]		= $isId ? "=" : "*=";
							$sel[ "idId" ] 			= $isId;
							
							if( $control ){
								$sel[ "tag" ] = $sel[ "val" ];
								$sel[ "val" ] = $sel[ "key" ] = $sel[ "operatorS" ]	= NULL;	
							}								
						}

						// [attr="value"]	[attr='value']	[attr=value]
						if( !empty( $item[ 3 ] ) ){
							// preg_math
							$match = preg_match_all( '/'. $this->attrs . '/is', $item[ 3 ], $attrs, PREG_SET_ORDER );
							
							if( $match )
							{
								// attrs
								$sel[ 'attrs' ] = [];	$addPerAttr = false;
								
								foreach( $attrs as $matche )
								{
									$newAttr = array( 
										"attr"		=> $matche[ 1 ],
										"operator"	=> @$matche[ 2 ],
										"value"		=> @$matche[ 3 ] != "" ? $matche[ 3 ]
														: ( @$matche[ 4 ] != "" ? @$matche[ 4 ]
														: @$matche[ 5 ] )
									);
																		
									if( ( ( strtolower( $newAttr[ "attr" ] ) == "id" )
										|| ( strtolower( $newAttr[ "attr" ] ) == "class" ) )
										&& !array_key_exists( 'key', $sel ) ){
										// 
										$sel[ "key" ]			= $newAttr[ "attr" ];
										$sel[ "val" ]			= $newAttr[ "value" ];
										$sel[ "operatorS" ]		= $newAttr[ "operator" ];
										
										unset( $newAttr );
									} 
									else
									{
										// Agregar
										$sel[ 'attrs' ][] = $newAttr;	
									}
								}
							}
						}
						
						// :eq|gt|first|last( +2 | -1 | 2 | +3n | -3n | odd | event )
						if( !empty( $item[ 4 ] ) ){
							// separar por :
							$filters = preg_split( '/[:]+/is', $item[ 4 ] );
							
							// attrs
							$sel[ 'filters' ] = [];
								
							foreach( $filters as $filter )
							{
								// nada
								if( empty( $filter ) || 
									!preg_match_all( '/'. $this->subfunction . '/is', $filter, $function, PREG_SET_ORDER ) )
									continue;
								
								// Function Actual
								$current = NULL;
								
								// Recorrer
								foreach( $function as $func ){
									if( !empty( $func[ 0 ] ) ){
										$current = $func;
										break;
									}
								}
								
								// nada
								if( is_null( $current ) ) continue;
								
								$filterArray = array(
									"function"		=> ':'. $current[1],		// :eq
									"value"			=> @$current[2],	// event, odd, n, (+-)n									
									"subfunction"	=> NULL,			// (odd, event)
									"signo"			=> NULL,			// +-
									"number"		=> NULL				// 5
								);
										
								switch ( $filterArray["value"] ) {
									case "even"	:
									case "odd" 		:
										$filterArray["subfunction"] = 'odd-even';
										break;
									
									// +2n
									case !!preg_match( '/^([+-]?[\d]*n)$/', $filterArray["value"] ) :
										$filterArray["signo"]	= @$current[4];
										$filterArray["number"]	= @$current[5];
										$filterArray["subfunction"] = 'n';
										break;
									
									// 2n+3
									case !!preg_match( '/^([+-]?[\d]*n[+-]?[\d]+)$/', $filterArray["value"] ) :
										$filterArray["signo"]	= @$current[6];
										$filterArray["number"]	= @$current[5];
										$filterArray["start"]	= @$current[7];	
										$filterArray["subfunction"] = 'n*';							
										break;
										
									// 2, -4, +6
									case !!preg_match( '/^[+-]?[\d]+$/', $filterArray["value"] ) :
										$filterArray["signo"]	= @$current[6];
										$filterArray["number"]	= @$current[7];										
										break;								
								}
							
								// add
								$sel[ 'filters' ][] = $filterArray;
							}
						}
						
						// Pasar a minúsculas
						$sel = array_map( "str2strtolower", $sel );
						
						// merge
						$sel = array_merge( $this->defaulSelector, $sel );
						
						$selectors[ $i ][] = $sel;						
					}
				}
			}
			
			$i++;
		}
		
		// save last results
		if ( count( $selectors ) > 0 )
		{
			$this->selectors = $selectors;
		} //echo '<pre>', print_r( $this->selectors, true ), '</pre>'; exit;
	}

	/**
	 * Attempts to find all children that match the rule 
	 * given.
	 *
	 * @param array $nodes
	 * @param array $rule
	 * @recursive
	 */
	protected function seek( array $nodes, array $rule, $nextRule = array(), $level = 0, $whiteTextNode, $applyFilter = true )
	{
		// Result
		$return = [];
		
		// nada
		if( count( $nodes ) == 0 )
			return $return;
			
		// recorrer nodos
		foreach ( $nodes as $node )
		{			
			// div > p, Selecciona todos los elementos p, hijos directos de div
			if( $level > 0 && ( $rule['type'] === '>' || $rule['type'] === '' ) )
			{
				// Buscar primer hijo
				if ( ! $node->hasChildren() )
					continue;
				
				$node = $this->getChildrens( [ $node ], $rule['type'] === '>' ? false : true );
			}
			
			// li + li, Selecciona todos los li que estan antecedidos por li
			else if( $level > 0 && $rule['type'] === '+' )
			{
				// Buscar siguiente elemento hermano
				$nodeId		= $node->id();
				$parent		= $node->getParent();
				
				// no tiene hijos
				if ( ! $parent->hasChildren() )
					continue;
					
				try
				{
					// get next child
					$child	= $parent->nextChild( $nodeId );
				}
				catch ( ChildNotFoundException $e )
				{
					// no more children
					$child = NULL;
				}
				
				if( !is_null( $child ) && $child instanceof TextNode )
				{
					while ( ! is_null( $child ) )
					{				
						if( ! ( $child instanceof TextNode ) )
						{
							$node = [ $child ];		$child = NULL;		break;	
						}
					
						try
						{
							// get next child
							$child = $parent->nextChild( $child->id() );
						}
						catch ( ChildNotFoundException $e )
						{
							// no more children
							$child = NULL;
						}
					}
				}
				else
				{
					$node = !is_null( $child ) ? [ $child ] : [];
				}
			}
			
			// #item ~ div, selecciona todos los div que tienen el mismo padre que #item y que estan despúes del mismo.
			else if ( $level > 0 && $rule['type'] === '~' )
			{
				// Buscar padre
				$nodeId		= $node->id();			$childres = [];
				$parent		= $node->getParent();
				
				// no tiene hijos
				if ( ! $parent->hasChildren() )	continue;
				
				try
				{
					// get next child
					$childres[] = $child = $parent->nextChild( $nodeId );
				}
				// no more children
				catch ( ChildNotFoundException $e ) { }

				// Buscar los hermanos siguientes 
				while ( ! is_null( $child ) )
				{				
					$childres[] = $child;
				
					try
					{
						// get next child
						$child = $parent->nextChild( $child->id() );
					}
					catch ( ChildNotFoundException $e )
					{
						// no more children
						$child = NULL;
					}
				}
				
				if( count( $childres ) == 0 )	continue;
				
				// Convertir a array
				$node = [];
				
				// Recorrer
				foreach( $childres as $nod )
				{
					if( $nod && !is_null( $nod ) )
					{
						$node[] = $nod;
					}
				}
			}
			
			// Buscar todos
			else
			{
				$node = [ $node ];
			}
			
			// nada
			if( count( $node ) == 0 )
				continue;
			
						
			// Recorrer nodos
			foreach( $node as $item )
			{
				// vars
				$pass	= true;		$addPer = "";
			
				// tags
				if ( !empty( $rule['tag'] ) && is_null( $rule['key'] ) && is_null( $rule['val'] ) )
				{
					$addPer = "tag";
								
					if ( ! ( $rule['tag'] == '*' || $rule['tag'] == strtolower( $item->getTag()->name() ) ) )
					{
						$pass = false;
					}
				}
				
				else if ( !empty( $rule['key'] ) && !empty( $rule['val'] ) )
				{
					$addPer = "attr";
					
					// verificar tag
					$tag = true;
					
					if( !empty( $rule['tag'] ) && $rule['tag'] != "*" ){
						$tag = $rule['tag'] == strtolower( $item->getTag()->name() );
					}
					
					if( $tag ){
						// Verificar clase o id
						$check = $this->match( $rule['operatorS'], $rule['val'], $item->getAttribute( $rule['key'] ) );
	
						// handle multiple classes
						if ( ! $check && $rule['key'] == 'class' )
						{
							$childClasses = explode(' ', $item->getAttribute( 'class' ));
							foreach ( $childClasses as $class )
							{
								if ( ! empty( $class ) )
								{
									$check = $this->match( $rule['operatorS'], $rule['val'], $class);
								}
								
								if ( $check ) break;
							}
						}
	
						if ( ! $check )
						{
							$pass = false;
						}
											
					} else {
						// no es el mismo tag
						$pass = false;
					}
				}
				
				// Agregar
				if( $pass )	$return[] = $item;
				
				// Recorrer attrs
				if( is_array( $rule['attrs'] ) && count( $rule['attrs'] ) > 0 )
				{
					//
					$pass2 = true;
					
					foreach( $rule['attrs'] as $attrRule )
					{
						if( empty( $attrRule['value'] ) ){
							// Comprobar que tenga el attr
							if( !$item->getTag()->hasAttr( $attrRule['attr'] ) ){
								$pass2 = false;		break 1;
							}
							
						} else if( !empty( $attrRule['operator'] ) ){
							if( !$this->match( $attrRule['operator'], $attrRule['value'], $item->getAttribute( $attrRule['attr'] ) ) ){
								$pass2 = false;		break 1;
							}
						}
					}
					
					// Quitar el último elemento porque no paso el attr
					if( $pass && !$pass2 ){
						@array_pop( $return );
					
					} else if( $pass2 && $addPer == "" ){
						$return[] = $item;
					}
				}
				
				/**
				 * Recursive 
				 */
				if ( ! $item->hasChildren() || $level > 0 )
					continue;
				
				$children = [];
				$child	  = $item->firstChild();
				
				while ( ! is_null( $child ) )
				{
					// Buscar lo mismo en los hijos
					$matches = $this->seek( [ $child ], $rule, $nextRule, $level, $whiteTextNode, false );
					
					if ( count( $matches ) > 0 ) {
						foreach ( $matches as $match )
						{
							$return[] = $match;
						}
					}
				
					try
					{
						// get next child
						$child = $item->nextChild( $child->id() );
					}
					catch (ChildNotFoundException $e)
					{
						// no more children
						$child = NULL;
					}
				}
			} // end foreach 2
			
			
		}  // end foreach 1
		
		
		// Quitar nodos de texto del resultado
		if( $whiteTextNode )
		{
			$return = array_filter( $return, function( $var ){
				// Si son TextNode, no agregar al array
				return !( $var instanceof TextNode ); 
			});
		}
		
		// Filtrar elementos
		if(	is_array( $rule['filters'] ) &&
			count( $rule['filters'] ) > 0 &&
			count( $return ) > 0 &&
			$applyFilter )
		{
			// Itinerar	
			foreach( $rule['filters'] as $filter )
			{
				// no hay items
				if( count( $return ) == 0 )
					break;
				
				// result
				$filterItems = [];
				
				switch ( $filter['function'] ) {
					// Primer item
					case ':first':
						// Primer elemento
						if( isset( $return[ 0 ] ) )
						{
							$filterItems[] = $return[ 0 ];
						}
						
						break 1;
						
					// Último item
					case ':last':
						//
						if( isset( $return[ count( $return ) -1 ] ) )
						{
							$filterItems[] = $return[ count( $return ) -1 ];
						}
						
						break 1;
					
					// Elementos impares
					case ':odd':
						// inicial
						$start = 1;
						
						foreach( $return as $filterItem )
						{
							if( ! ( ( $start % 2 ) == 0 ) )
								$filterItems[] = $filterItem;
							
							$start++;
						}
						
						break 1;
					
					// Elementos pares
					case ':even':
						// inicial
						$start = 1;
						
						foreach( $return as $filterItem )
						{
							if( ( ( $start % 2 ) == 0 ) )
								$filterItems[] = $filterItem;
							
							$start++;
						}
						
						break 1;
					
					/**
					 * cuentan desde 0
					 */
					 
					// Devuelve el item con el index especificado
					case ':eq':
						// eq
						$eq = ( int ) $filter['number'];
						
						// Contar desde la derecha
						if( ! ( $filter['signo'] == '+' || $filter['signo'] == '' ) )
						{
							$eq = count( $return ) - $eq;
						}
						
						if( isset( $return[ $eq ] ) )
						{
							$filterItems[] = $return[ $eq ];
						}
							
						break 1;
					
					// Devuelve los items, execepto los indexs especificados
					// Seleccionar todos los elementos en un índice mayor que el índice dentro del conjunto combinado.
					// :lt( 3 ), selecciona el index: 4, 5, n
					case ':gt':
						// Positivo
						if( $filter['signo'] == '+' || $filter['signo'] == '' ){
							// Hasta donde
							$hasta = ( int ) $filter['number'];
							
						} else {
							// Hasta donde
							$hasta = count( $return ) - ( ( int ) $filter['number'] );
						}
						
						// init
						$i = 0;

						foreach( $return as $filterItem )
						{
							if( $i <= $hasta )
							{
								$i ++;	continue;
							}
							
							$filterItems[] = $filterItem;							
							$i ++;
						}
						
						break 1;
					
					// Devuelve los items, hasta el index especificado
					// Seleccionar todos los elementos en un índice menor que el índice dentro del conjunto combinado
					// :lt( 3 ), selecciona el index: 0, 1, 2
					case ':lt':
						// Positivo
						if( $filter['signo'] == '+' || $filter['signo'] == '' ){
							// Hasta donde
							$hasta = ( int ) $filter['number'];
							
						} else {
							// Hasta donde
							$hasta = count( $return ) - ( ( int ) $filter['number'] );
						}
						
						// init
						$i = 0;

						foreach( $return as $filterItem )
						{
							if( $i >= $hasta ) break 1;
							
							$filterItems[] = $filterItem;							
							$i ++;
						}
						
						break 1;
					
					// Cuenta desde 1
					// Selecciona todos los elementos que son la enésima-niño de sus padres.
					// nth-child(3n+1) nth-child(3n) nth-child(odd)
					case ':nth-child' :
						// tag requerido
						if( !empty( $rule['tag'] ) )
						{
							// vars
							$parentsIds		= [];
							
							// convertir a global
							$_FILES['__rule__'] = $rule;
							
							foreach( $return as $filterItem )
							{
								// obtener padre
								$parent = $filterItem->getParent();
								
								// no tiene hijos
								if ( ! $parent->hasChildren() )
									continue;
								
								if( !in_array( $parent->id(), $parentsIds ) ){
									$parentsIds[] = $parent->id();
									
									// obtener child
									$childs = $this->getChildrens( [ $parent ], false );
									
									// filtrar
									$childs = array_filter( $childs, function( $var ){
										// importar
										$rule = $_FILES['__rule__'];
										$return = true;
										
										if( $var instanceof TextNode )
											$return = false;
										
										if( $return && $rule['tag'] != strtolower( $var->getTag()->name() ) )
											$return = false;
											
										// Si son TextNode, no agregar al array
										return $return;
									});
									
									// 
									if( count( $childs ) == 0 )	continue;
									
									// filtrar
									switch ( $filter['subfunction'] ) {
										case 'odd-even':
											// inicial
											$ic = 0;
											
											// es impar
											$isOdd = strtolower( $filter['value'] ) == 'odd';
											
											// impares o pares
											foreach( $childs as $childC )
											{
												// es par
												$control = ( ( $ic % 2 ) == 0 );
												if( !$isOdd ) $control = !$control;
												
												if( $control ) $filterItems[] = $childC;
												
												// Aumentar
												$ic ++;
											}
											
											break;
											
										case 'n':
										case 'n*':
											// iniciar en...
											$init = $filter['subfunction'] == 'n' ? 0 : ( ( int ) @$filter['start'] );
											$initForeach = 0;
											
											// cada tanto
											$number = ( int ) $filter['number'];
											
											// next
											$nextChilds	= $filter['subfunction'] == 'n'
												? $number - 1
												: $init - 1;
											
											foreach( $childs as $childC )
											{
												// agregar
												if( $nextChilds == $initForeach )
												{
													$filterItems[] = $childC;
													
													// siguiente elemento 
													$nextChilds += $number;
												}
												
												$initForeach++;
											}
											
											break;
										
										// DEFAULT
										case NULL :
										default :
											// vars
											$index	= ( ( int ) $filter['number'] ) - 1;
											$init	= 0;
											
											// impares o pares
											foreach( $childs as $childC )
											{
												// solo un elemento
												if( $init == $index )
													$filterItems[] = $childC;
												
												$init++;											
											}
											
											break;											
									}
								}								
							}
							
							// delete
							unset( $_FILES['__rule__'] );
						}
						
						break 1;
					
					default :
						$filterItems = $return;
					break;
				}
				
				//
				$return = $filterItems;
				
				// Volver a agregar
				if( count( $filterItems ) == 0 )
				{				
					break 1;	
				}
			}
		}		
		return $return;
	}

	/**
	 * Attempts to match the given arguments with the given operator.
	 *
	 * @param string $operator
	 * @param string $pattern
	 * @param string $value
	 * @return bool
	 */
	protected function match($operator, $pattern, $value)
	{
		$value		= strtolower( $value );
		$pattern	= strtolower( $pattern );
		$return		= false;
		
		switch ($operator) 
		{
			// Iguales
			case '=':
				$return		= $value === $pattern;
			break;
			
			// Diferentes
			case '!=':
				$return		= $value !== $pattern;
			break;
			
			// Comienza por $pattern
			case '^=':
				$return		= preg_match('/^'.preg_quote($pattern, '/').'/i', $value);
			break;
			
			// Termina por $pattern
			case '$=':
				$return		= preg_match('/'.preg_quote($pattern,'/').'$/i', $value);
			break;
			
			// Es igual a $pattern o empieza con $pattern seguida de guión, [es|es-ES]
			case '|=':
				// Son iguales
				$return		= $value === $pattern;
				
				if( !$return ){
					// buscar si empieza por $pattern, seguido de guión
					$return	= preg_match('/^' . preg_quote( $pattern . "-", '/' ).'/i', $value);
				}
								
			break;
			
			// Selecciona los elementos que tienen el atributo especificado con un valor que contiene una palabra dada,
			// delimitado por espacios
			case '~=':
				// separar por espacios
				$parts = explode(' ', $value);
				
				foreach( $parts as $val )
				{
					if ( ! empty( $val ) )
					{
						$return = preg_match( "/^". preg_quote( $pattern, '/' ) . "$/i", $val );
					}
					
					if ( $return ) break;
				}
				
			break;
			
			// Contiene $pattern
			case '*=':
				if ( $pattern[0] != '/' ) 
				{
					$pattern = "/". preg_quote( $pattern, '/' ) . "/i";
				}
						
				$return		= preg_match( $pattern, $value );
			break;
		}
		
		return $return;
	}
}

/**
 * Pasar a minúsculas
 */
function str2strtolower( $text ){
	if( is_string( $text ) && !is_numeric( $text ) )
		$text = strtolower( $text );
	
	if( is_array( $text ) ){
		foreach( $text as $k => $str ){
			$text[ $k ] = str2strtolower( $str );
		}
	}
	
	return $text;
}

/*new Selector( "div" );
new Selector( "div ,span.CLASS  ,, p[data-id*=val]" );
new Selector( "  div, span" );
new Selector( ' span#spanid[attr="value1"]:eq(-5n)  ~div.clase[attr$=\'value2\']:eq(odd),   + .class[attr]:eq(+6) p [ID] .g[class*=e]' );*/