<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/**
 * RSS 2.0 feed's writer
 *
 * This class implements rss 2.0 writing.
 *
 * @author lepidosteus
 */
class feed_rss_2_0 extends feed_base
{
    var $item_class = 'feed_item_rss_2_0';

    var $_itemName = 'item';
    var $_itemIndent = "\t\t";

    /**
     * Feed's time to live.
     *
     * Tells the feed reader when to crawl it again.
     */
    var $_chan_ttl = null;
    /**
     * Feed's language.
     */
    var $_chan_language = null;
    /**
     * Feed's last build date.
     */
    var $_chan_lastBuildDate = null;

    function _print_header($defaulting = true)
    {
        parent::_print_header($defaulting);
        echo '<rss version="2.0">'."\n"
            ."\t".'<channel>'."\n";
        foreach ($this as $k => $v) {
            if (strpos($k, '_chan_') !== false && $v != null) {
                $k = substr($k, 6);
                echo $this->_itemIndent.'<'.$k.'>'.$v.'</'.$k.'>'."\n";
            }
        }
    }

    function _print_footer()
    {
        echo "\t".'</channel>'."\n"
            .'</rss>'."\n";
    }

    function defaulting()
    {
        $this->_chan_ttl = 2600; // the feed is live for an hour
        $this->_chan_lastBuildDate = lc_date(DATE_RSS);
    }
}

/**
 * RSS 2.0 items
 *
 * @author lepidosteus
 */
class feed_item_rss_2_0 extends feed_item
{
    /**
     * Global Unique IDentifier
     *
     * Validated to the url of the item.
     */
    var $guid = '';
    /**
     * Item's description
     */
    var $description = '';
    /**
     * Item's publication date
     */
    var $pubDate = '';

    function feed_item_rss_2_0($input)
    {
        parent::feed_item($input);
    }

    function validate()
    {
        if (!parent::validate()) {
            return false;
        }
        if ($this->guid == '' && $this->link != '') {
            /**
             * rfc says we should either give a real guid, or a truly unique
             * url. Its purpose is for reader to "remember" the item.
             * Giving the news url is good for now, will need refactoring later.
             */
            $this->guid = $this->link;
        }
        return true;
    }

    /**
     * pubDate specific setter
     *
     * @param $pubDate timestamp of the item's publication date
     */
    function _set_pubDate($pubDate)
    {
        $this->pubDate = lc_date(DATE_RSS, (int)$pubDate);
    }
}
?>