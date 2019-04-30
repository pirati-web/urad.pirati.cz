<?php
/**
 * DOM manipulation library.
 *
 * This source file is subject to the "New BSD License".
 *
 * @author     Vojtěch Knyttl
 * @copyright  Copyright (c) 2010 Vojtěch Knyttl
 * @license    New BSD License
 * @link       http://knyt.tl/
 */

namespace Maite\Web;

//use Nette;
use Maite\Utils\Strings;



class DOM {

    private $xml;

    private $input;



    /**
     * Builds an object from potentionally non-well-formed XML/HTML document
     * @param  string  document
     * @return DOM
     */
    public function __construct($source, $type = 'xml') {
        libxml_use_internal_errors(true);
        if ($source instanceof \SimpleXMLElement) {
            $this->xml = $source;
        } else {
            $source = Strings::normalize($source);
            $xml = tidy_parse_string($source, array(
                    'clean' => true, 'input-xml' => $type == 'xml' ? true : false, 'output-xml' => true,
                    'wrap' => 0, 'numeric-entities' => true, 'join-classes' => true), 'UTF8');
            $xml->cleanRepair();
            $xml = preg_replace('#<!DOCTYPE.*?>#sm', '', $xml);
            $this->input = explode("\n", $xml);
            $this->xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_PARSEHUGE);
        }
    }



    public function getErrors() {
        $errors = array();
        foreach (libxml_get_errors() as $e) {
            $view = "";

            for ($i = -2; $i < 1; $i++) {
                $view .= ($e->line+$i+1)." ".$this->input[$e->line+$i]."\n";
            }
            $errors[] = "$e->line:$e->column $e->message\n".$view;
        }
        return $errors;
    }



    /**
     * Applies XPath expression on given document
     * @param  string  xpath expression
     * @param  int  index of node to be returned
     */
    public function xpath($path, $index = -1) {
        return empty($this->xml) ? $this : $this->focus($this->xml->xpath($path), $index);
    }



    /**
     * Returns child with $name of $index
     * @param  string  node name
     * @param  int  index of node to be returned
     * return  XML
     */
    public function child($name = null, $index = -1)  {
        return empty($this->xml)
                    ? $this
                    : $this->focus(is_null($name) ? $this->xml->children() : $this->xml->$name, $index);
    }



    /**
     * Returns value of attribute $name
     * @param  string  attribute name
     * @return string
     */
    public function att($name) {
        if (empty($this->xml))
            return '';

        $att = $this->xml->attributes();
        return (string) $att[$name];
    }



    /**
     * Returns false in case the XML is empty, otherwise returns itself
     * @return mixed
     */
    public function exists() {
        return empty($this->xml) ? false : $this;
    }



    /**
     * Returns array of XML if $index is not given, otherwise element of $index
     * @param   array   list of SimpleXML elements
     * @param   int     index of element
     * @return  array|XML
     */
    protected function focus($focus, $index) {
        if ($index + 1 > count($focus))
            return new DOM(null);
        if ($index != -1) {
            return new DOM($focus[$index]);

        } else {
            $result = array();
            foreach ($focus as $el)
                $result[] = new DOM($el);
            return $result;
        }
    }


    /**
     * Returns a regular expression match for xml() and text()
     * @param  string  regular expression
     * @param  string  string to match
     * @return array|string
     */
    protected static function re($re, $s) {
        if ($re) {
            preg_match("#$re#smu", $s, $re);
            $s = is_array($re) && count($re) == 2 ? $re[1] : $re;
        } else {
            $s = \Maite\Utils\Strings::normalize($s);
        }
        return $s;
    }



    /**
     * Returns a string representing the current object
     * @return  string
     */
    public function xml($re = null) {
        $s = self::prettyPrint(empty($this->xml)?'':$this->xml->asXML());
        return self::re($re, $s);
    }



    /**
     * Returns all the text values inside the current object
     * @return  string
     */
    public function text($re = null) {
        $s = strip_tags(empty($this->xml)?'':$this->xml->asXML());
        $s = html_entity_decode($s, ENT_QUOTES, 'utf-8');
        return self::re($re, $s);
    }



    function __toString() {
        return $this->text();
    }



    /**
     * Counts children (for compatibility with SimpleXML)
     * @return int
     */
    public function count() {
        return empty($this->xml) ? 0 : $this->xml->count();
    }



    public function __get($id) {
        if ($el = $this->child($id, 0)->exists())
            return $el;

        if ($el = $this->att($id))
            return $el;

        return new DOM(null);
    }



    public function __call($child, $index) {
        $index = @$index[0];
        return $this->child($child, $index);
    }



    /**
     * From a given XML document creates an associative array
     * @param  SimpleXML  given xml document (optional)
     * @return array
     */
    public function assoc($xml = null) {
        if ($xml == null)
            $xml = $this->xml;

        $unique = array();
        $children = $xml->children();

        foreach ($children as $c)
            $unique[] = $c->getName();

        if (count(array_unique($unique)) == $xml->count()) {
            $assoc = (array) $xml->attributes();
            foreach ($children as $c) {
                $assoc[$c->getName()] = $c->count() ? $this->assoc($c): (string) $c;
            }
        } else {
            foreach ($xml->children() as $c) {
                $assoc[] = array_merge(array('@attributes' => $xml->attributes()), $this->assoc($c));
            }
        }

        return $assoc;
    }



    /**
     * Helper function to indent XML string to be nicely shown
     * @param  string
     * @return string
     */
    public static function prettyPrint($xml) {
        $xml = str_replace("\n", '&#xA;', $xml);
        $xml = tidy_parse_string($xml, array(
                'output-xml' => true, 'input-xml' => true, 'wrap' => 0,
                'indent' => true, 'numeric-entities' => true), 'UTF8');
        $xml->cleanRepair();
        return (string) $xml;
    }



    /**
     * Static helper function to validate document against a schema
     * @param string
     * @param string
     * @param boolean
     */
    public static function validate($xml, $schema, $relax = false) {
        $doc = new \DomDocument;
        set_error_handler(function($errNo, $errMsg) {
            restore_error_handler();
            $errMsg = preg_replace('#^.*error :#', '', $errMsg);
            throw new DOMException($errMsg);
        });

        $doc->loadXML($xml);
        $validation = $relax ? $doc->relaxNGValidate($schema) : $doc->schemaValidate($schema);
        restore_error_handler();
        return $validation;
    }
}

class DOMException extends \Exception {}
