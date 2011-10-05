<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## XHTML filtering configuration
# @author legolas558
#
# this file is included only when XHTML input processing is necessary

include $d_root.'classes/htmlawed.php';

global $htmLawed_config;
$htmLawed_config = array('abs_url' => -1, 'clean_ms_char' => 0, // disabled because corrupted UTF-8
	'comment' => 2, 'balance' => 0, 'elements' => '*'
//'elements' => 'strong em u pre tt ul li ol'
);


global $my;
if ($my->gid<4)
	$htmLawed_config['elements'] .= '-script-iframe';
// only editors and above are allowed to use such javascript events
if ($my->gid<2) {
	// will disable javascript events
	$htmLawed_config['deny_attribute'] = 'on*';
	// will disable flash and similar embed usages and iframes
	$htmLawed_config['elements'] .= '-embed';
}

/*
// javascript events allowed only for editor users
if ($my->gid>=2) {
	$img_valid['onmouseover'] = $a_valid['onmouseover'] = 1;
	$img_valid['onmouseout'] = $a_valid['onmouseout'] = 1;
	$a_valid['onclick'] = 1;
}

// only editors and above are allowed to add flash content in editors
if ($my->gid>=2)
$my_kses->AddHTML('embed',array('type'=>"application/x-shockwave-flash",'src' => 1,
								'width' => 1,'height' => 1,'play'=>"true", 'loop'=>"true"));
								
// only managers and administrators are allowed to use IFRAMEs (only TF)
if ($my->gid>=4)
	$my_kses->addHTML('iframe', array('src' => 1, 'name' => 1, 'width' => 1, 'height' => 1,
								'align' => 1, 'frameborder' => array('minval' => 0, 'maxval' =>1),
								'marginwidth' => 1,
								'marginheight' => 1) );



//'schemes' => 'https', 'svn', 'news', 'nntp', 'telnet', 'gopher'

$num_arr = array('minval' => 0, 'maxval' => getrandmax());

$my_kses->AddHTML('strong');
$my_kses->AddHTML('em');
$my_kses->AddHTML('u');
$my_kses->AddHTML('pre');
$my_kses->AddHTML('tt');
$my_kses->AddHTML('ul');
$my_kses->AddHTML('li');
$my_kses->AddHTML('ol');
$my_kses->AddHTML( 'table', array('border' => $num_arr,
'width' => 1,
'align' => 1,		// deprecated by XHTML
'bordercolor' => array('minlen' => 3, 'maxlen' => 7),
'bgcolor' => array('minlen' => 3, 'maxlen' => 7),
'cellspacing' => $num_arr,
'cellpadding' => $num_arr,
'background' => array('minlen' => 3, 'maxlen' => 200),
'id' => 1
)
);

$t_attr = array(
"colspan" => array('minval' => 2),
"rowspan" => array('minval' => 2),
"class" => array("minlen" => 1),
"width" => array("maxval" => 1000),
"style" => 1,
"nowrap" => array('valueless' => 'n')
);

$my_kses->AddHTML('td', $t_attr);
$my_kses->AddHTML('tr', $t_attr);
$my_kses->AddHTML('sup');
$my_kses->AddHTML('sub');

$img_valid = array('src' => 1,
'align' => 1,		// deprecated by XHTML
'border' => $num_arr,
'width' => $num_arr,
'height' => $num_arr,
'id' => 1,
'name' => 1,
'alt' => 1,
'longdesc' => 1,
'style' => 1,
'hspace' => $num_arr,
'vspace' => $num_arr
);

$a_valid = array('href' => 1, 'title' => 1, 'target' => 1, 'rel' => 1);



$my_kses->AddHTML('a', $a_valid);
$my_kses->AddHTML('title', array('valueless' => 'n'));
$my_kses->AddHTML('p', array('align' => 1, 'class' => 1, 'style' => 1));
$my_kses->AddHTML('div', array('align' => 1, 'class' => 1, 'style' => 1));
$my_kses->AddHTML('img', $img_valid);
$my_kses->AddHTML('br');
$my_kses->AddHTML('hr');
$my_kses->AddHTML('h1');
$my_kses->AddHTML('h2');
$my_kses->AddHTML('h3');
$my_kses->AddHTML('h4');
$my_kses->AddHTML('h5');
$my_kses->AddHTML('h6');

// for native editor formatting
$my_kses->AddHTML('span', array('style' => 1));

// non-XHTML valid, will be removed once the DOCTYPE is Strict
$my_kses->AddHTML('b');
$my_kses->AddHTML('i');
$my_kses->AddHTML('font', array(
		'face' => 1,
		'color' => 1,
		'size' => 1)
);
*/
?>