<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/**
 * Base class for feeds writers
 *
 * This class has two purposes: to provide a factory mask for the feeds'
 * writers, and to implements the base of any feed writer.
 * This class should be abstract.
 *
 * Using a descendant is quite simple:
 * $feed = feed_base::factory('feed_type');
 * $feed->setWhatever('value');
 * $feed->add_item(array('link' => 'http://example.com', ...));
 * $feed->display();
 *
 * @abstract
 * @author lepidosteus
 */
 
 class feed_base
{
    /**
     * The name of the feed's item's class associated with the current
     * feed type. eg: feed_item_atom_1_0
     */
    var $item_class = null;
    /**
     * Mime type for the current feed.
     */
    var $mime = 'application/xml';

    /**
     * Name of items in the feed (item, outline, entry, ...)
     */
    var $_itemName = 'item';
    /**
     * Indentation string, to append before any output inside the feed
     */
    var $_itemIndent = "\t";

    /**
     * Feed's title
     */
    var $_chan_title = null;
    /**
     * Feed's description
     */
    var $_chan_description = null;
    /**
     * Feed's link
     */
    var $_chan_link = null;

    /**
     * Feed's items are stored in this array
     */
    var $_items = array();

    /**
     * Factory mask to create feeds' writers.
     *
     * You give it a feed type's name, and it returns a class instance
     * to work with supporting it (or if not found, supporting rss 2.0).
     * This function should be static.
     *
     * @param $feed_type string representation of the feed type wanted.
     * If not valid, it will default to rss_2_0.
     * @return a feed_base descendant instance
     */
    function factory($feed_type = 'rss_2_0', $default = 'rss_2_0', $active = array())
    {
	// filter invalid feed types (coming from $_GET)
	switch ($feed_type) {
		case 'rss_1_0':
		case 'rss_2_0':
		case 'atom_1_0':
		// they're OK
		break;
		default:
			// unknown feed type, fallback to default
			$feed_type = $default;
	}
	if (!$active[$feed_type]) {
		CMSResponse::Unauthorized();
		exit();
	}
	global $d_root;
	$feed_class = 'feed_'.$feed_type;
	require $d_root.'classes/feed/'.$feed_class.'.php';
        return new $feed_class;
    }

    /**
     * Prints the feed's header
     *
     * Should be protected. To be overriden to add feed-specific header.
     *
     * @param $defaulting if set to true (default), the defaulting() method
     * will be called before any printing is done.
     */
    function _print_header($defaulting = true)
    {
        if ($defaulting) {
            $this->defaulting();
        }
		global $d;
        echo '<?xml version="1.0" encoding="'.raw_strtoupper($d->Encoding()).'"?>'."\n";
    }

    /**
     * Prints a feed's item's header.
     *
     * Should be protected. Override if your feed needs some specific data.
     *
     * @param &$item the item being displayed
     */
    function _print_item_open(&$item)
    {
        echo $this->_itemIndent.'<'.$this->_itemName.'>'."\n";
    }

    /**
     * Prints an item of the feed.
     *
     * Should be protected.
     *
     * @param $item the feed_item descendant being displayed.
     */
    function _print_item($item)
    {
        if ($item->is_empty()) { // try to keep it clean
            return;
        }
        $this->_print_item_open($item);
        foreach ($item as $k => $v) {
            if ($v !== '') {
                echo $this->_itemIndent."\t".'<'.$k.'>'.$v.'</'.$k.'>'."\n";
            }
        }
        echo $this->_itemIndent.'</'.$this->_itemName.'>'."\n";
    }

    /**
     * Adds an item to the feed list, to later be displayed.
     *
     * @param $item the feed_item descendant to add.
     * @param $validating if set to true (default), the item validates itself
     * before being added.
     * @return boolean, true if successfull, false otherwise.
     */
    function add_item($item, $validating = true)
    {
        if ($validating) {
            if (!$item->validate()) {
                return false;
            }
        }
        $this->_items[] = $item;
        return true;
    }

    /**
     * Prints the whole feed.
     *
     * @param $defaulting if set to true (default), the defaulting() method
     * will be called before any printing is done.
     */
    function display($defaulting = true)
    {
        $this->_print_header($defaulting);
        foreach ($this->_items as $i) {
            $this->_print_item($i);
        }
        $this->_print_footer();
    }

    /**
     * Sets the feed's description
     *
     * @param $desc the new description
     */
    function setDescription($desc)
    {
        $this->_chan_description = $desc;
    }

    /**
     * Sets the feed's title
     *
     * @param $title the new title
     */
    function setTitle($title)
    {
        $this->_chan_title = $title;
    }

    /**
     * Sets the feed's link
     *
     * @param $link the new link
     */
    function setLink($link)
    {
        $this->_chan_link = $link;
    }
    
    function setChannelTitle($chan_title) {
	$this->_chan_title =$chan_title;
    }


    /**
     * Prints the feed's footer
     *
     */
    function _print_footer() { }
    /**
     * Set some specific feed's fields to default working values
     *
     */
    function defaulting() { }
    /**
     * Sets the feed's author
     *
     * @param $author the new author
     */
    function setAuthor($author) { }
    /**
     * Sets the feed's associated mail
     *
     * @param $mail the new mail
     */
    function setMail($mail) { }
}

/**
 * Base class for feeds' items.
 *
 * Should be abstract. Implements item's logic, descendant must implement
 * feed-specific fields.
 *
 * @abstract
 * @author lepidosteus
 */
class feed_item
{
    /**
     * Link of this item
     */
    var $link = '';
    /**
     * Title of this item
     */
    var $title = '';

    /**
     * Tells whether this item is empty or not
     *
     * @return boolean, true if empty, false otherwise
     */
    function is_empty()
    {
        foreach ($this as $k => $v) {
            if ($v != null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Constructor, fill the item with its values.
     *
     * For each element of the $input array, it first check if the item
     * class contains an associated _set_whatever() method for specific
     * reading, if not it checks for raw reading.
     *
     * @param $input associated array of values to fille the item
     * @return feed_item
     */
    function feed_item($input)
    {
        if (!is_array($input)) return;
        foreach ($input as $k => $v) {
            $method = '_set_'.$k;
			//L: method_exists()??? should NOT be used - code reflection is deprecated
            if (method_exists($this, $method)) {
                $this->$method($v);
            } else if (isset($this->$k)) {
                $this->$k = $v;
            }
        }
    }

    /**
     * Validates the item.
     *
     * If some required fields are not set, they take a default value if
     * available, or the function returns false.
     *
     * @return boolean true if the validation is a success, false otherwise
     */
    function validate() {
        if ($this->link == '') {
            return false;
        }
        return true;
    }
}

?>