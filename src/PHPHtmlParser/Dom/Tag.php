<?php
/*namespace PHPHtmlParser\Dom;

use PHPHtmlParser\Dom;
use stringEncode\Encode;*/

class Tag {

	/**
	 * The name of the tag.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The attributes of the tag.
	 *
	 * @var array
	 */
	protected $attr = [];

	/**
	 * Is this tag self closing.
	 *
	 * @var bool
	 */
	protected $selfclosing = false;

	/**
	 * Tag noise
	 */
	protected $noise = '';

	/**
	 * The encoding class to... encode the tags
	 *
	 * @var mixed
	 */
	protected $encode = null;


	public function __construct($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Se utiliza para consultar datos a partir de propiedades inaccesibles.
	 */
	function __get( $name ) {
		//
		$attr = $this->getAttribute( $name );
		
		if( is_array( $attr ) )
			$attr = $attr["value"];
			
		return $attr;
    }

	/**
	 * se ejecuta al escribir datos sobre propiedades inaccesibles.
	 */
    function __set( $name, $value ) {
		return $this->setAttribute( $name, $value );
    }
	
	/**
	 * se invoca cuando se usa unset() sobre propiedades inaccesibles.
	 */
    function __unset( $name ) {		
        return $this->removeAttribute( $name );
    }


	/**
	 * Returns the name of this tag.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->name;
	}

	/**
	 * Sets the tag to be self closing.
	 *
	 * @chainable
	 */
	public function selfClosing()
	{
		$this->selfclosing = true;
		return $this;
	}

	/**
	 * Checks if the tag is self closing.
	 *
	 * @return bool
	 */
	public function isSelfClosing()
	{
		return $this->selfclosing;
	}

	public function setEncoding(Encode $encode)
	{
		$this->encode = $encode;
	}

	/**
	 * Sets the noise for this tag (if any)
	 *
	 * @chainable
	 */
	public function noise($noise)
	{
		$this->noise = $noise;
		return $this;
	}		

	/**
	 * Set an attribute for this tag.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @chainable
	 */
	public function setAttribute($key, $value)
	{
		$key = strtolower($key);
		if ( ! is_array($value))
		{
			$value = [
				'value'       => addslashes( $value ),
				'doubleQuote' => true,
			];
		}
		$this->attr[$key] = $value;

		return $this;
	}

	/**
	 * Sets the attributes for this tag
	 *
	 * @param array $attr
	 * @chainable
	 */
	public function setAttributes(array $attr)
	{
		foreach ($attr as $key => $value)
		{
			$this->setAttribute($key, $value);
		}

		return $this;
	}

	/**
	 * Returns all attributes of this tag.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		$return = [];
		foreach ($this->attr as $attr => $info)
		{
			$return[$attr] = $this->getAttribute($attr);
		}
		return $return;
	}

	/**
	 * Returns an attribute by the key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		if ( ! isset($this->attr[$key]))
		{
			return null;
		}
		
		$value = $this->attr[$key]['value'];
		if (is_string($value) AND ! is_null($this->encode))
		{
			// convert charset
			$this->attr[$key]['value'] = $this->encode->convert($value);
		}

		return $this->attr[$key];
	}
	
	/**
	 * Verifica si existe el attr
	 */
	public function hasAttr( $key )
	{
		$key = strtolower( $key );
		
		// Exists
		$exists = $this->getAttribute( $key );
		
		return is_null( $exists ) ? false : true;
	}

	/**
	 * Generates the opening tag for this object.
	 *
	 * @return string
	 */
	public function makeOpeningTag()
	{
		$return = '<'.$this->name;

		// add the attributes
		foreach ($this->attr as $key => $info)
		{
			$info = $this->getAttribute($key);
			$val  = $info['value'];
			if (is_null($val))
			{
				$return .= ' '.$key;
			}
			elseif ($info['doubleQuote'])
			{
				$return .= ' '.$key.'="'.$val.'"';
			}
			else
			{
				$return .= ' '.$key.'=\''.$val.'\'';
			}
		}

		if ($this->selfclosing)
		{
			return $return.' />';
		}
		else
		{
			return $return.'>';
		}
	}

	/**
	 * Generates the closing tag for this object.
	 *
	 * @return string
	 */
	public function makeClosingTag()
	{
		if ($this->selfclosing)
		{
			return '';
		}

		return '</'.$this->name.'>';
	}
	
	/**
	 * Eliminar attr
	 */
	public function removeAttribute( $key )
	{
		if ( isset( $this->attr[ $key ] ) )
		{
			// eliminar
			unset( $this->attr[ $key ] );
		}
		
		return $this;
	}
	
	public function removeAttr( $key )
	{
		return $this->removeAttributes( $key );	
	}
	
	public function removeAttrs( $key = null )
	{
		return $this->removeAttributes( $key );	
	}
	
	/**
	 * Eliminar attrs
	 */
	public function removeAttributes( $keys = null )
	{
		if( is_array( $keys ) )
		{
			foreach ( $keys as $key => $attr )
			{
				$this->removeAttribute( $attr );
			}
		}
		else if( is_string( $keys ) )
		{
			$this->removeAttribute( $keys );
		}
		else if( is_null( $keys ) )
		{
			// remove all
			$this->attr = [];
		}
		
		return $this;
	}
	
	/**
	 * Comprueba si tiene la clase dicha
	 */
	public function hasClass( $className, $i = false ){
		//
		$RegExp = $this->getRegExp( $className, $i );		
		$classText = $this->getAttribute( "class" );
		
		if( is_null( $classText ) ) return false;

		return preg_match( $RegExp, stripslashes( $classText["value"] ) ) ? true : false;
	}

	/**
	 * agregar clase
	 */
	public function addClass( $className, $i = false )
	{
		// Separar por espacios
		$className = !is_array( $className ) ? explode( ' ', $className ) : $className ;
		
		foreach( $className as $class )
		{
			if( is_string( $class ) && !$this->hasClass( $class, $i ) ){
				// obtener class
				$classText = $this->getAttribute( "class" );
				
				if( is_null( $classText ) )
					$classText = $class;
				else
					$classText = $classText["value"] . " " . $class;			
				
				$this->setAttribute( 'class', $classText );
			}
		}
		
		return $this;
	}

	/**
	 * Quitar clase
	 */
	public function removeClass( $className, $i = false ){		
		// Separar por espacios
		$className = !is_array( $className ) ? explode( ' ', $className ) : $className;
		
		foreach( $className as $class )
		{
			//
			$RegExp = $this->getRegExp( $class, $i );		
			$classText = $this->getAttribute( "class" );
		
			// reemplazar
			$classText = trim( preg_replace( $RegExp, ' ', stripslashes( $classText["value"] ) ) );
		
			// Eliminar
			if( strlen( $classText ) == 0 || empty( $classText ) )
				$this->removeAttribute( 'class' );
				
			// Establecer nuevo valor
			else	
				$this->setAttribute( 'class', $classText );
		}
		return $this;
	}
	
	/**
	 * crear una expresion regular
	 */
	public function getRegExp( $className, $i = false ){
		//
		return '@(?:^|\s+)' . preg_quote( $className ) . '(?:\s+|$)@' . ( !$i ? "i" : "" );
	}
}
