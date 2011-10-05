<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Default cache configuration parameters
# @author legolas558
#
# defines base caching rules for the all components

//TODO: specify variation to cache system class
// Vary-By: $my->lang
// Vary-By: $my->gid (could be automatically optimized: when fragments varying by gid match another fragment (for example, "Registered" gid fragment matching "Public" gid fragment), they could someway be merged (this would need a cache fragments index? not really: we can rely on cheap crc32.md5 filenames so that md5 will identify the "path" of the cache fragment while crc32 and fragment size will identify the content. The same contents are likely to have the same crc32 and size)
// instead of md5 for the paths we could use plain path keywords (faster) and prefix all the cache files with the $d_uid (which is already of topmost secrecy) to prevent direct access to private/cache/ files
// Vary-By: CMSRequest::Querystring() (the same optimization as above could be used, if feasible)
// Cache-TTL: infinite (all our caching is safe to be cached forever)

// The idea is that the single entities (components, modules, drabots) do have a cache profile which defines (through minimal code) how the fragments are cacheable.
// Such profiles should allow the biggest granularity so that optimization can be smartly conducted by the cache system itself

?>
