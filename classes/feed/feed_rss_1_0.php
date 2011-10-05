<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/**
 * RSS 1.0 feed's writer
 *
 * @author lepidosteus
 */
 
 class feed_rss_1_0 extends feed_base
{
    var $item_class = 'feed_item_rss_1_0';

    var $_itemName = 'item';

    function _print_header($defaulting = true)
    {
        parent::_print_header($defaulting);
        //L: modified to use some Lanius CMS core facilities
		global $d_website, $pathway;
        $feed_uri = xhtml_safe($d_website.$pathway->Current());
        echo '<rdf:RDF '
            .'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" '
            .'xmlns="http://purl.org/rss/1.0/">'."\n"
            .$this->_itemIndent.'<channel rdf:about="'.$feed_uri.'">'."\n";

        foreach ($this as $k => $v) {
            if (strpos($k, '_chan_') !== false && $v != null) {
                $k = substr($k, 6);
                echo $this->_itemIndent."\t".'<'.$k.'>'.$v.'</'.$k.'>'."\n";
            }
        }

        echo $this->_itemIndent.'<items>'."\n"
            .$this->_itemIndent."\t".'<rdf:Seq>'."\n";
        foreach ($this->_items as $i) {
            echo $this->_itemIndent."\t\t".'<rdf:li resource="'.$i->link.'" />'."\n";
        }
        echo $this->_itemIndent."\t".'</rdf:Seq>'."\n"
            .$this->_itemIndent.'</items>'."\n";

        echo $this->_itemIndent.'</channel>'."\n";
    }

    function _print_item_open(&$item)
    {
        echo $this->_itemIndent.'<'.$this->_itemName.' rdf:about="'.$item->link.'">'."\n";
    }

    function _print_footer()
    {
        echo '</rdf:RDF>'."\n";
    }
}

/**
 * RSS 1.0 items
 *
 * @author lepidosteus
 */
class feed_item_rss_1_0 extends feed_item
{
    /**
     * Item's description
     */
    var $description = '';

    function feed_item_rss_1_0($input)
    {
        parent::feed_item($input);
    }
}
?>