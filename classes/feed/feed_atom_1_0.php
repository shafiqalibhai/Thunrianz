<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//TODO: the <summary> should have an HTML type specification...there is too much class overkill here but yet such a simple modification (adding an attribute) becomes difficult to implement. I propose to rewrite the output level of the component so that more customization is allowed for each feed type. Object orienting is good, but ALWAYS object orienting is not good

$o = (string)((int)(date('Z')/(60*60)));
if ($o[0]=='-') {
	if (strlen($o)<3)
		$o = '-0'.substr($o,1);
} else {
	if (strlen($o)<2)
		$o = '+0'.$o;
	else
		$o = '+'.$o;
}
define('STRFT_ATOM', '%G-%m-%dT%T'.$o.':00');

/**
 * Atom 1.0 feed's writer
 *
 * @author lepidosteus
 */
class feed_atom_1_0 extends feed_base
{
    var $item_class = 'feed_item_atom_1_0';

    var $_itemName = 'entry';

    /**
     * Latest feed update date
     */
    var $_chan_updated = null;
    /**
     * Feed's author name
     */
    var $_author = null;
    /**
     * Feed's author mail
     */
    var $_mail = null;

    function _print_header($defaulting = true)
    {
        parent::_print_header($defaulting);
        //L: modified to use some Lanius CMS core facilities
		global $d_website, $pathway;
        $feed_uri = xhtml_safe($d_website.$pathway->Current());
        echo '<feed xmlns="http://www.w3.org/2005/Atom">'."\n"
            .$this->_itemIndent.'<title>'.$this->_chan_title.'</title>'."\n"
            .$this->_itemIndent.'<id>'.$this->_chan_link.'</id>'."\n"
            .$this->_itemIndent.'<link rel="alternate" href="'.$this->_chan_link.'" />'."\n"
            .$this->_itemIndent.'<link rel="self" href="'.$feed_uri.'" />'."\n"
            .$this->_itemIndent.'<updated>'.$this->_chan_updated.'</updated>'."\n"
            .$this->_itemIndent.'<author>'."\n"
            .$this->_itemIndent."\t".'<name>'.$this->_author.'</name>'."\n"
            .$this->_itemIndent."\t".'<email>'.$this->_mail.'</email>'."\n"
            .$this->_itemIndent.'</author>'."\n";
    }

    function _print_item_open(&$item)
    {
        parent::_print_item_open($item);
        echo $this->_itemIndent."\t".'<link href="'.$item->link.'" />'."\n";
        $item->link = '';
    }

    function _print_footer()
    {
        echo '</feed>'."\n";
    }

    function defaulting()
    {
	if (!isset($this->_chan_title) || !isset($this->_author) || !isset($this->_mail))
		trigger_error('Channel title/author/mail not set!');
        if ($this->_chan_updated == null) {
		//L: this should be set to the latest modification time taken from the listed URLs
            $this->_chan_updated = lc_strftime(STRFT_ATOM);
        }
    }

    /**
     * Sets the feed's author's name
     *
     * @param $author new author name
     */
    function setAuthor($author)
    {
        $this->_author = $author;
    }
    
    /**
     * Sets the feed's author email
     *
     * @param $mail the new mail
     */
    function setMail($mail)
    {
        $this->_mail = $mail;
    }
}

/**
 * Atom 1.0 items
 *
 * @author lepidosteus
 */
class feed_item_atom_1_0 extends feed_item
{
    /**
     * Summary of the item
     */
    var $summary = '';
    /**
     * Id of the item. Needs to be unique in our feed
     */
    var $id = '';
    /**
     * Latest update date for this item
     */
    var $updated = '';

    function feed_item_rss_1_0($input)
    {
        parent::feed_item($input);
    }

    function validate()
    {
        if (!parent::validate()) {
            return false;
        }
        if ($this->id == '') {
            $this->id = $this->link;
        }
        return true;
    }

    /**
     * Description setter (redirect to summary)
     *
     * @param $desc the description for this item
     */
    function _set_description($desc)
    {
        $this->summary = $desc;
    }

    /**
     * pubDate setter (redirect to updated after convertion)
     *
     * @param $timestamp timestamp of the latest update
     */
    function _set_pubDate($timestamp)
    {
        $this->updated = lc_strftime(STRFT_ATOM, (int)$timestamp);
    }
}
?>