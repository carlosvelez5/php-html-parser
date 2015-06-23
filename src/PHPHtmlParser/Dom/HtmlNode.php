<?php
/*namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Exceptions\UnknownChildTypeException;
use PHPHtmlParser\Exceptions\ChildNotFoundException;*/

class HtmlNode extends AbstractNode {

	/**
	 * Remembers what the innerHtml was if it was scaned previously.
	 */
	protected $innerHtml = null;

	/**
	 * Remembers what the outerHtml was if it was scaned previously.
	 *
	 * @var string
	 */
	protected $outerHtml = null;

	/**
	 * Remembers what the text was if it was scaned previously.
	 *
	 * @var string
	 */
	protected $text = null;

	/**
	 * Remembers what the text was when we looked into all our
	 * children nodes.
	 *
	 * @var string
	 */
	protected $textWithChildren = null;

	/**
	 * Cuenta de los hijos que son instancia de HtmlNode
	 */
	protected $childrensOfHtmlNode = 0;
	
	/**
	 * Elementos en línea
	 */
	protected $inlineTags = 'a,abbr,acronym,b,basefont,bdo,big,br,cite,code,dfn,em,font,i,img,input,kbd,label,q,s,samp,select,small,span,strike,strong,sub,sup,textarea,tt,u,var';
	
	/**
	 * Elementos en bloque
	 */
	//protected $blockTags = 'address,blockquote,center,dir,div,dl,fieldset,form,h1,h2,h3,h4,h5,h6,hr,isindex,menu,noframes,noscript,ol,p,pre,table,ul';
	
	/**
	 * Variables de indent
	 */
	protected $isIndent = false;
	protected $pad		= "\t";
	protected $newline	= "\n";
	protected $level	= 0;
	
	/**
	 * Sets up the tag of this node.
	 */
	public function __construct( $tag )
	{
		if ( !$tag instanceof Tag )
		{
			$tag = new Tag($tag);
		}
		
		// arrays
		$this->inlineTags	= explode( ',', $this->inlineTags );
		//$this->blockTags	= explode( ',', $this->blockTags );
		
		$this->tag = $tag;
		
		parent::__construct();
	}
	
	
	/**
	 * Métodos Mágicos
	 */
	
	/**
	* Las llamadas plugin / funciones importados
	*
	* Este es también el lugar donde una buena cantidad de plugins tienen su magia.
	* Este método se llama la magia, cada vez que un "undefined" de clase
	* Método que se llama en código, y lo usamos para llamar a una función importada.
	*
	* Usted NUNCA debe nunca invocar esta función de forma manual. El universo va a implosionar si lo hace ... en serio ;)
	*
	* @param string $method
	* @param array $args
	*/
	public function __call ( $method, $args )
	{
		switch ( strtolower( $method ) )
		{
			// DOM Insertion, Inside
            case 'append':
            case 'prepend':
				// agregar primer párametro
				array_unshift( $args, strtolower( $method ) );
				
            	return call_user_func_array( array( $this, "insertInside" ), $args );
			
			// DOM Insertion, Outside
            case 'after':
            case 'before':
				// agregar primer párametro
				array_unshift( $args, strtolower( $method ) );
				
            	return call_user_func_array( array( $this, "insertOutside" ), $args );
        }
		
		if( method_exists( $this->tag, $method ) )
		{
			// LLamar function de usuario		
			return call_user_func_array( array( $this->tag, $method ), $args );
		}
		
		return null;
	}
	
	/**
	 * Se utiliza para consultar datos a partir de propiedades inaccesibles.
	 */
	function __get( $name )
	{
		// obtener attrs
		$attrs = $this->tag->getAttributes();
		
		// Algún attr
        if ( isset( $attrs[ $name ] ) )
        {
            return $this->tag->$name;
        }
		
        switch ( $name )
		{
            case 'outerHtml': return $this->outerHtml();
            case 'innerHtml': return $this->innerHtml();
            case 'plaintext': return $this->text();
            default: return array_key_exists( $name, $attrs );
        }
    }

	/**
	 * se ejecuta al escribir datos sobre propiedades inaccesibles.
	 */
    function __set( $name, $value )
	{
		// Inner - Outer
        switch ( $name )
		{
            case 'outerHtml': case 'html' :	return $this->outerHtml = $value;				
            case 'innerHtml':	return $this->innerHtml = $value;
        }
		
        return $this->tag->setAttribute( $name, $value );        
    }

	/**
	 * se lanza al llamar a isset() o a empty() sobre propiedades inaccesibles.
	 */
    function __isset( $name )
	{
        switch ($name)
		{
            case 'innerHtml': return true;
            case 'outerHtml': return true;
            case 'plaintext': return true;
        }
		
		// obtener attrs
		$attrs = $this->tag->getAttributes();
		
        //no value attr: nowrap, checked selected...
        return ( array_key_exists( $name, $attrs ) ) ? true : isset( $attrs[ $name ] );
    }

	/**
	 * se invoca cuando se usa unset() sobre propiedades inaccesibles.
	 */
    function __unset( $name )
	{
		// obtener attrs
		$attrs = $this->tag->getAttributes();
		
        if ( isset( $attrs[ $name ] ) )
            return $this->tag->removeAttribute( $name );
    }

	
	/**
	 * Gets the inner html of this node.
	 *
	 * @return string
	 * @throws UnkownChildTypeException
	 */
	public function innerHtml( $indent = false, $pad = "\t", $newline = "\n", $level = 0 )
	{
		// Verificar si son diferentes
		$this->isDiferents( $indent, $pad, $newline, $level );
		
		if ( ! $this->hasChildren())
		{
			// no children
			return '';
		}

		if ( ! is_null($this->innerHtml))
		{
			// we already know the result.
			// return $this->innerHtml;
		}

		$child	= $this->firstChild();
		$padding = $string = '';
		
		if( $indent )
		{
			// Obtener pad
			$padding = $this->getPad( $pad, $level );
		}
		
		// aumentar level
		$level++;
		
		// continue to loop until we are out of children
		while( ! is_null($child))
		{
			if ($child instanceof TextNode)
			{
				$string .= $child->text();
			}
			elseif ($child instanceof HtmlNode)
			{
				// Elemento en bloque
				$isBlocked = !in_array( strtolower( $child->getTag()->name() ), $this->inlineTags );
				$isBlocked ? $this->childrensOfHtmlNode++ : false;
				
				$string .= $child->outerHtml( $indent, $pad, $newline, $level );
			}
			else
			{
				throw new UnknownChildTypeException('Unknown child type "'.get_class($child).'" found in node');
			}

			try
			{
				$child = $this->nextChild($child->id());
			}
			catch (ChildNotFoundException $e)
			{
				// no more children
				$child = null;
			}
		}
		
		// remember the results
		$this->innerHtml = $string;

		return $string;
	}

	/**
	 * Gets the html of this node, including it's own
	 * tag.
	 *
	 * @return string
	 */
	public function outerHtml( $indent = false, $pad = "\t", $newline = "\n", $level = 0 )
	{
		// Verificar si son diferentes
		$this->isDiferents( $indent, $pad, $newline, $level );
		
		// special handling for root
		if ( $this->tag->name() == 'root')
		{
			return $this->innerHtml( $indent, $pad, $newline, $level++ );
		}

		if ( ! is_null($this->outerHtml))
		{
			// we already know the results.
			// return $this->outerHtml;
		}
		
		// padding
		$padding = '';		$isBlocked = !in_array( strtolower( $this->tag->name() ), $this->inlineTags );
		
		if( $indent && $isBlocked )
		{
			// Obtener pad
			$padding = $newline . $this->getPad( $pad, $level );
		}
		
		// str
		$return = $padding . $this->tag->makeOpeningTag();
		
		
		if ($this->tag->isSelfClosing())
		{
			// ignore any children... there should not be any though
			return $return;
		}

		// get the inner html
		$return .= $this->innerHtml( $indent, $pad, $newline, $level++ );
		
		// no tiene hijos 
		if( $this->childrensOfHtmlNode == 0 )	$padding = '';
			
		// add closing tag
		$return .= $padding . $this->tag->makeClosingTag();

		// remember the results
		$this->outerHtml = $return;

		return $return;
	}
	
	/**
	 * 
	 */
	protected function isDiferents( $indent, $pad, $newline, $level ){
		// Inicia en 0
		$this->childrensOfHtmlNode = 0;
		
		// Si son diferentes, limpiar las variables
		if( $this->isIndent !== $indent 
		    || $this->pad !== $pad
			|| $this->newline !== $newline )
		{
			$this->clear();
		}
		
		// Guardar valores
		$this->isIndent	= $indent;
		$this->pad		= $pad;
		$this->newline	= $newline;
		$this->level	= $level;
	}

	/**
	 * Gets the text of this node (if there is any text). Or get all the text
	 * in this node, including children.
	 *
	 * @param bool $lookInChildren
	 * @return string
	 */
	public function text($lookInChildren = false)
	{
		if ($lookInChildren)
		{
			if ( ! is_null($this->textWithChildren))
			{
				// we already know the results.
				return $this->textWithChildren;
			}
		}
		elseif( ! is_null($this->text))
		{
			// we already know the results.
			return $this->text;
		}

		// find out if this node has any text children
		$text = '';
		foreach ($this->children as $child)
		{
			if ($child['node'] instanceof TextNode)
			{
				$text .= $child['node']->text;
			}
			elseif($lookInChildren and
			       $child['node'] instanceof HtmlNode)
			{
				$text .= $child['node']->text($lookInChildren);
			}
		}

		// remember our result
		if ($lookInChildren)
		{
			$this->textWithChildren = $text;
		}
		else
		{
			$this->text = $text;
		}

		return $text;
	}

	/**
	 * Call this when something in the node tree has changed. Like a child has been added
	 * or a parent has been changed.
	 */
	protected function clear()
	{
		$this->innerHtml = null;
		$this->outerHtml = null;
		$this->text		 = null;
		
		$this->isIndent	= null;
		$this->pad		= null;
		$this->newline	= null;
		$this->level	= null;
	}
	
	/**
	 * str_repeat — Repite un string
	 */
	protected function getPad( $str = "\t", $multiplier )
	{
		return str_repeat( $str, $multiplier );
	}
}
