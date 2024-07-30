<?php  //  charset='UTF-8'   EOL: 'UNIX'   tab spacing=2 ¡important!   word-wrap: no
	/*/ SyncDir.php   written by and Copyright © Joe Golembieski, SoftMoon WebWare
					 ALPHA 1.2.1  July 30, 2024

		This program is licensed under the SoftMoon Humane Use License ONLY to “humane entities” that qualify under the terms of said license.
		For qualified “humane entities”, this program is free software:
		you can use it, redistribute it, and/or modify it
		under the terms of the GNU Affero General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version, with the following additional requirements
		ADDED BY THE ORIGINAL SOFTWARE CREATOR AND LICENSOR that supersede any possible GNU license definitions:
		This original copyright and licensing information and requirements must remain intact at the top of the source-code.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU Affero General Public License for more details.

		You should have received a copy of:
		 • the SoftMoon Humane Use License
		and
		 • the GNU Affero General Public License
		along with this program.  If not, see:
			https://softmoon-webware.com/humane-use-license/
			https://www.gnu.org/licenses/#AGPL    /*/

/*  This ALPHA release has been tested on a Windows NT system with Apache 2.0 and PHP version 7.1.6 ; 8.1.6 ; 8.2.12
 *  I’ve been using it for a while mostly without problems, except:
 *  • I’ve seen my USB thumb-drive apparently overheat and become unresponsive.
 *   This is a hardware problem (video driver gets hot and heats up the metal-casing on the thumb-drive 1" away)
 *   and was solved by moving the thumb-drive to another USB port.
 *   However, PHP “locks-up” waiting for it, and I had to close the server and browser.
 *   It’s hard to know WHAT exactly PHP is doing…in that case I was transferring a large sum of data…
 *   was it going slow?  Five files copied (I could see in Windows Explorer), then nothing,
 *   and the browser just said “waiting on the server”.
 *   Did a bug cause an endless loop?  Not in this case, but I was left guessing at first…
 *   Note the max_execution_time is set below so PHP will not stop automatically,
 *   so large filesets can be copied without PHP aborting before finishing.
 *   Adjust it as you see appropriate!
 *  • A few times the “verify first” option simply did not work.  WHY?  IDK!
 *   I didn’t have time to dig in and start logging everything PHP did, or even try again to do so.
 *   Other times it works perfectly.  When it fails, there is no error message,
 *   and the HTML interface seems normal.
 *   Just nothing gets synced/copied, as if you selected no files to sync/copy.
 *   I've looked at the code again and again, but did not see any reason why it might fail,
 *   other than the data did not transfer from the browser to PHP correctly for some reason.
 *   Playing with the filesystem while debugging is not something I want to do everyday,
 *   so IDK when I will look into that.
 *   I think I remember trying to sync many, many, many “verified first” files at once, and it failed.
 *   It worked when I only verified a few files, if that’s a hint.
 *   Debugging code that fails under unknown circumstances is tricky.
 *   Doing so while your code is continuously modifying the filesystem is a real PITA!
 *
 *  It’s never actually skrewed up anything in the filesystem, but use at your own risk!
*/


ini_set("max_execution_time", 0);  // = ∞     1500 =25 min     1800 =30 min
clearstatcache(true);

define ('TRASH_FOLDER_NAME', ".trash".DIRECTORY_SEPARATOR);  //this could be a “hidden” file as given, or have a full name.ext
define ('TRASH_NAME_EXT', ".trash");  //should generally match the above’s extension.

define ('COMINGLE', FALSE);  // ¿Show directories with files/folders co-mingled =OR= group folders together before files?

define("MS_WINDOWS", stripos(php_uname('s'), 'Win')!==FALSE);
define('POSIX_WILDCARDS_PATTERNS_SUPPORTED', (!MS_WINDOWS  or  phpversion()>="5.3.0"));

define ('INTERNATIONAL_CHARS_SUPPORTED', function_exists('mb_strtolower'));
if (INTERNATIONAL_CHARS_SUPPORTED)  {
	Function uncase($s) {return mb_strtolower($s, 'UTF-8');}  }
else  {
	Function uncase($s) {return strtolower($s);}  }

// these are for checking user input
define('EXT', '#^[^\\\\/?:;*"<>|]+$#');
if (MS_WINDOWS)  { // MS Windows directory separator
	define('NAME', '#^\\\\$|^(\\\\)?[^\\\\/?:;*"<>|]+(?(1)[^\\\\/?:;*"<>|]|[^/?:;*"<>|])$#');
	define('PATH', '#^(([A-Z]:\\\\)?[^/:;?*"<>|]+)|([A-Z]:\\\\)$#i');  }
else  { // Mac OS/LINUX/UNIX directory separator
	define('NAME', '#^(/)?[^\\\\/:;?*"<>|]+(?(1)|/)$#');
	define('PATH', '#^[^\\\\:;?*"<>|]+(/)?$#');  }
define ('REGEX', '/^(.).+\\1[igme]{0,4}$/');   //


//define ('CHKD', "checked='checked' ");
define ('CHKD', "checked ");
define ('EXPANDER',
	'<span class="expand" onclick="this.parentNode.addClass(\'expand\')">▼</span><span onclick="this.parentNode.removeClass(\'expand\')">▲</span>'	);

if (!defined('FNM_CASEFOLD'))  define ('FNM_CASEFOLD', 16);  // for Function filter()

/*
$foo=array("foo"=>"fazz", 7, 5,"sing"=>"soft", 9, 2, 0,"fing"=>"fong", 8, 1, 4);
$bar=array(17,45,29,72,90,"sing"=>"loud",38,81,54,"bar"=>"bazz","bing"=>"bong");
$gek=array_multisort($foo, $bar);
echo var_dump($foo),"<br><br>",var_dump($bar),"<br><br>";
*/


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name='author' content='Joe Golembieski, SoftMoon-WebWare'>
<meta name='copyright' content='Copyright © 2021, 2024 Joe Golembieski, SoftMoon-WebWare'>
<title>Synchronize Directories</title>
<style type="text/css">
body {
	color: lime;
	background-color: black;
	font-family: sans-serif;
	padding-bottom: 1.618em; }
pre {font-size: 1.2em;}
h1 {
	font-weight: bold;
	font-size: 2em;
	color: aqua;
	margin-top: 0.162em;
	padding: 0; }
path,
filename {
	font-family: monospace;
	font-weight: bold;
	white-space: nowrap; }
path.replacement {
	border-left: 4px double red;
	border-right: 4px double red;
	background-color: #620000; }
path.replaced {
	border-right: 4px double red;
	color: darkMageta; }
path.failed-copy {
	color: red;
	font-style: oblique; }
path.failed-copy {
	font-style: oblique 38.2deg; }
h1 path {
	display: block;
	padding: 0 0 0 1em; }
aside {
	position: fixed;
	z-index: 1000;
	right: 0px;
	top: 0px;
	left: auto;
	bottom: auto;
	border-left: 1px dashed;
	border-bottom: 1px dashed;
	color: yellow;
	background-color: black; }
aside span:first-child {
	display: none;
}
aside:hover span:first-child {
	display: block;
	position: absolute;
	background-color: inherit;
	rotate: -38.2deg;
	top: calc(100% + 0.618em);
	bottom: auto;
	left: -1em;
	right: auto; }
.helplink,
help span {
	font-size: 1.618em; }
th .helplink {
	display: block; }
.helplink a,
a.helplink,
help span {
	text-decoration: none;
	color: yellow; }
.helplink a:visited,
a.helplink:visited {
	color: orange; }
mark, note {
	color: Violet; }
mark {
	font-size: 0.618em;
	vertical-align: top;
	background-color: inherit; }
path.replacement + mark {
	border-left: 4px double red;
	font-size: 1.618em; }
section > note {
	display: inline-block;
	margin: 0 .162em 1.618em 2.618em;
	padding: .382em .618em;
	border: 1px solid violet; }
note h3 {
	padding: 0;
	margin: 0;
	text-align: center; }
note p {
	display: inline-block;
	width: 34em;
	vertical-align: top;
	margin: 0 1em 0 0; }
note p:last-child {
	margin-right: 0; }
note h3 mark {
	font-size: inherit;
	margin-right: 1em; }

ul { 	list-style: none; }

h5 {
	font-weight: bold;
	font-size: 1.618em;
	color: red; }
input {
	color: aqua;
	background-color: black;
	font-family: monospace;
	font-weight: bold; }
input[type="text"] {
	padding: 0 7px;
	border-color: PowderBlue; }
input[type="radio"] {
	color: yellow; }
label.checked {
	color: aqua;
	font-weight: bold; }
input[type="submit"] {
	margin: 0 1.618em;
	font-size: 1.618em;
	font-family: sans-serif;
	border: 2px solid aqua; }
#dirInput {
	display: block;
	padding: 0.2em 1em; }
#dirInput label.folder {
	display: block;
	text-indent: -0.618em; }
#dirInput label.folder:last-child {
	margin-top: .2618em; }
#dirInput span {
	font-size: 1.618em;  }
#dirInput label.folder input {
	width: calc(100% - 8.2em); }
fieldset {
	display: block;
	border: none;
	padding: 0;
	margin: 0;
	position: relative; }
div fieldset,
fieldset fieldset,
section fieldset:first-child,
table {
	border: 1px solid yellow;
	width: 100%;
	margin: 0 0 2em 0; }
div#options fieldset:last-child label,
fieldset#Filter_Order label {
	display: block; }

#verifier {
	display: block;
	width: auto;
	margin: 0 0 2em 0;
	padding: 0.618em; }
form#verifier {
	border: 4px double aqua; }

#verifier ul {
	margin: 0 0 1.618em 0;
	padding: 0;
	list-style-type: none; }
#verifier ul ul {
	margin: 0 0 0 1.618em;
	display: none; }
#verifier li > span,
#verifier div > span,
#verifier div.expand > span.expand,
#verifier li.expand > span.expand {
	color: yellow;
	cursor: default;
	display: none; }
#verifier li > span.expand,
#verifier li.expand > span,
#verifier div > span.expand,
#verifier div.expand > span{
	display: inline; }
#verifier .expand > ul {
	display: block; }

#verifier div {
	margin-left: 1em;
	border-left: 1px dotted; }
#verifier div.similar {
	display: inline-block;
	text-align: top; }
#verifier path.replaced + div.similar,
#verifier div.expand {
	display: block; }
#verifier div li:last-child path,
#verifier .replaced path {
	padding-bottom: 0.162em;
	border-bottom: 1px solid; }

#verifier div.similar,
#verifier div.similar * {
	color: orange !important; }
#verifier mark,
	color: red;
	margin: 0 1em; }


#options {
	position: relative;
	z-index: 4; }
#dirInput,
#Filter_Order,
#filters th:hover {
	position: relative;
	z-index: 3; }
#filters th {
	position: relative;
	z-index: 2; }
#filters {
	position: relative;
	z-index: 1; }

help {
	position: absolute;
	bottom: 0px;
	right: 0px;
	top: auto;
	left: auto;
	color: orange;
	background-color: black;
	font-weight: normal; }
help dl,
help p,
help div {
	display: none; }
help:hover {
	right: auto;
	bottom: auto;
	padding: .618em;
	border: 1px solid orange;  }
#options:hover {
	z-index: 10; }
#options help:hover {
	top: 2.618em;
	left: 20%;
	z-index: 10; }
#dirInput help:hover {
	right: 0px;
	left: auto;
	bottom: auto; }
#filters fieldset > help:hover {
	top: 2em;
	left: 80%; }
#filters th > help:hover {
	top: 2em;
	left: 38.2%; }
help:hover dl,
help:hover p,
help:hover div {
	display: block;
	width: 16.18em;
	margin: 0;
	text-align: left; }
help:hover span {
	display: none; }

td fieldset {
	margin: 0 !important; }
td fieldset fieldset label {
	display: none; }
td fieldset fieldset:hover label,
td fieldset.open label {
	display: inline; }
td fieldset legend label {
	display: inline; }
div fieldset,
section fieldset:first-child,
fieldset fieldset {
	display: inline-block;
	width: auto;
	padding-left: 7px;
	vertical-align: bottom; }
div#options fieldset,
section fieldset:first-child,
fieldset fieldset {
	vertical-align: top;  }
legend,
caption {
	color: yellow;
	font-weight: bold;
	font-size: 1.2em; }
td ul legend {
	cursor: default; }
legend span.open,
.open legend span {
	display: none; }
.open legend span.open {
	display: inline; }
th, td {
	border: 1px solid yellow;
	width: 22.5%; }
th:first-child, td:first-child {
	width: 10%; }
td ul {
	padding: 0; }
td input[type='text'] {
	width: calc(100% - 20px); }
td li input {
	width: auto; }
td label {
	font-family: monospace;
	font-weight: bold; }
td label note {
	font-family: sans-serif;
	font-weight: normal; }
td legend label {
	font-family: inherit; }

.disabled,
.disabled * {
	color: DimGray !important;
	border-color: DimGray !important;
	text-decoration: line-through; }

footer {
	text-align: right;
	color: DeepSkyBlue;
	font-family: serif;
	font-weight: bold; }
</style>
<script type='text/javascript'>

const
	is_MS_Windows=(navigator.platform.search(/Win/i)!==(-1)),
	DIR_SEP= is_MS_Windows ? "\\" : "/";

var tabbedOut=false;

function enhanceKeybrd(event)  { // for American QWERTY keyboards
	//  characters not allowed in filenames:  \/?*":;<>|
	if (event.altKey)  return;
	var txt, curPos,
			isPath=event.target.name.match( /dir|ext|file|path/ );
	//console.log(event.keyCode," - ",event.key);
	if (event.keyCode===9)  {tabbedOut=true;  return;}  else  tabbedOut=false;
	switch (event.keyCode)  {
	case 13: event.preventDefault();  return;  // ENTER key
	case 49: if (event.ctrlKey)  txt= event.shiftKey ? "¡" : "●";  // 1! key
	break;
	case 50: if (event.ctrlKey)  txt= event.shiftKey ? "®" : "©";  // 2@ key
	break;
	case 53: if (event.ctrlKey && event.shiftKey)  txt="°";  // 5% key
	break;
	case 54: if (event.ctrlKey)  txt= event.shiftKey ? "☻" : "☺";  // 6^ key
	break;
	case 55: if (event.ctrlKey)  txt= event.shiftKey ? "♫" : "♪";  // 7& key
	break;
	case 56: if (event.ctrlKey)  txt= event.shiftKey ? "☼" : "×";  // 8* key
					 else if (event.shiftKey  &&  isPath)  {
						if (!event.target.name.match(/file/)
						||  event.target.selectionStart!==event.target.value.length
						||  event.target.value.substr(-1)!==DIR_SEP)
							event.preventDefault();
						return;  }
	break;
	case 59: if (isPath  &&  (!event.shiftKey  ||  isPath[0]==='ext'  ||  isPath[0]==='file'))  {  // ;: key
							event.preventDefault();  return;  }
	break;
	case 61: if (event.ctrlKey)  txt= event.shiftKey ? "≈" : "±";  // =+ key
	break;
	case 106: if (isPath)  {event.preventDefault();  return;}  // * key on numeric keypad
	break;
	case 111: if (isPath)  {  // / key on numeric keypad
							if (isPath[0]==='ext')  {event.preventDefault();  return;}
							if (is_MS_Windows)  txt='\\';  }
	break;
	case 188: if (event.shiftKey  &&  isPath)  {event.preventDefault();  return;}  // ,< key
	break;
	case 190: if (event.shiftKey  &&  isPath)  {event.preventDefault();  return;}  // .> key
	break;
	case 191:   // /? key
		if (event.ctrlKey)  txt= event.shiftKey ? '¿' : '÷';
		else if (isPath  &&  (event.shiftKey || isPath[0]==='ext'))  {event.preventDefault();  return;}
		else if (isPath  &&  is_MS_Windows)  txt='\\';
	break;
	case 219:   // [{ key
		if (event.ctrlKey)  txt= event.shiftKey ? '“' : '‘';
	break;
	case 220:   // \| key
		if (event.ctrlKey)  txt= event.shiftKey ? '¦' : '¶';
		else if (isPath  &&  (event.shiftKey || isPath[0]==='ext'))  {event.preventDefault();  return;}
		else if (isPath  &&  !is_MS_Windows)  txt='/';
	break;
	case 221:   // ]} key
		if (event.ctrlKey)  txt= event.shiftKey ? '”' : '’';
	break;
	case 222:   // '" key
		if (event.ctrlKey)  txt= event.shiftKey ? 'φ' : 'π';
		else if (event.shiftKey  &&  isPath)  {event.preventDefault();  return;}
	break;  }
	if (txt)  {
		curPos=event.target.selectionStart;
		event.target.value=event.target.value.substr(0,curPos)+txt+event.target.value.substr(event.target.selectionEnd||curPos);
		event.target.selectionStart=
		event.target.selectionEnd=curPos+txt.length;
		event.preventDefault();  }  };


function popNewField(fileField)  {
	var fileFieldset=fileField.parentNode;
	var allInps=fileFieldset.getElementsByTagName('input');
	if (allInps[allInps.length-1].value.length>0)  {
	var newField=fileField.cloneNode(true);
	fileField=(fileField.nodeType==1  &&  fileField.tagName=="INPUT") ? fileField : fileField.getElementsByTagName('input')[0];
	var inp=(newField.nodeType==1  &&  newField.tagName=="INPUT") ? newField : newField.getElementsByTagName('input')[0];
	inp.value="";
	fileFieldset.appendChild(newField);
	if (tabbedOut)	setTimeout(function () {allInps[allInps.length-1].focus();}, 1);
	return false;  }  }

// ================ from UniDOM =========================

	function addClass(c)  {this.className=aClass(this.className, c);}
	function aClass(cn, ac)  {  //private
		if (!(ac instanceof Array))  ac=[ac];
		for (var i=0; i<ac.length; i++)  {
			if (!(typeof cn == 'string'  &&  cn.match( new RegExp('\\b'+RegExp.escape(ac[i])+'\\b') )))
				cn+=(cn) ? (" "+ac[i]) : ac[i];  }
		cn=cleanClass(cn);
		return cn;  }

	function removeClass(c) {this.className=xClass(this.className, c);}
	function xClass(cn, xc) {  //private
		if (typeof cn != 'string')  return;
		if (!(xc instanceof Array))  xc=[xc];
		for (var i=0; i<xc.length; i++)  {
			cn=cn.replace((typeof xc[i] == 'object'  &&  (xc[i] instanceof RegExp)) ?  xc[i]  :  new RegExp('\\b'+RegExp.escape(xc[i])+'\\b', 'g'),  "");
			cn=cleanClass(cn);  }
		return cn;  }

	//private
	function cleanClass(cn)  {
		cn=cn.replace( /^\s*/ , "");
		cn=cn.replace( /\s*$/ , "");
		cn=cn.replace( /\s{2,}/g , " ");
		return cn;  }

	function useClass(c, b)  {  // c should be the string name of the class
		if (b)  this.className=aClass(this.className, c);
		else  this.className=xClass(this.className, c);  }

	function toggleClass(c)  {  // c should be the string name of the class
		if (this.className.match(c))
					this.className=xClass(this.className, c);
		else  this.className=aClass(this.className, c);  }


Element.prototype.addClass=addClass;
Element.prototype.removeClass=removeClass;
Element.prototype.useClass=useClass;
Element.prototype.toggleClass=toggleClass;

RegExp.escape=function (string) {
	// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
	return string && string.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}

// ===================================================

// this is called when a directory name is (un)checked in the “verify” file-tree
function check_all_in_dir(e)  {
	event.stopPropagation();
	for (var i=1, inps=e.closest("li").getElementsByTagName('input'); i<inps.length; i++)  {
		if (inps[i].type==='checkbox')  inps[i].checked=e.checked;  }
	check_dir(e.closest('ul'), e.checked);  }

// this is called when an individual file is (un)checked in the “verify” file-tree
// the onchange event bubbles up to the UL
function check_dir(ul, chkd)  {
	event.stopPropagation();
	try {
		if (chkd===false)  throw false;
//		var inps=ul.getElementsByTagName('input');
//		for (var i=0; i<inps.length; i++)  {
		for (const inp of ul.getElementsByTagName('input'))  {
			if (inp.name=="")  continue;
			if (!inp.checked)  throw false;  }
		throw true;  }
	catch (b) {
		ul.parentNode.querySelector('input').checked=b;
		if (ul=ul.parentNode.closest('ul'))  check_dir(ul, b);  }  }

// this is called when a group of filename extensions is (un)checked
function check_all_in_group(e)  {
	for (var i=1, inps=e.closest("fieldset").elements; i<inps.length; i++)  {
		if (inps[i].type==='checkbox')  inps[i].checked=e.checked;  }  }

// this is called when an individual filename extension is (un)checked
// the onchange event bubbles up to the FS
function check_legend(fs)  {
	event.stopPropagation();
	for (var i=1; i<fs.elements.length; i++)  {
		if (!fs.elements[i].checked)  {
			fs.querySelector('legend input').checked=false;
			return;  }  }
	fs.querySelector('legend input').checked=true;  }

// the “all folders” option is not available (not logical) when the sync is not recursive
function disable_AllFolders(flag)  {
	const lbl=document.getElementById('AllFolders');
	lbl.useClass('disabled', flag);
	lbl.firstChild.disabled=flag;  }

function align_filterTables()  {
//	for (var i=0, fo_fs=document.getElementById('Filter_Order').elements; i<fo_fs.length; i++)  {
//		if (fo_fs[i].checked)  {var fo=fo_fs[i].value;  break;}  }
	for (const inp of document.getElementById('Filter_Order').elements)  {
		if (inp.checked)  {var fo=inp.value;  break;}  }
	const
		fit=document.getElementById('FilterIn'),
		fot=document.getElementById('FilterOut');
	switch (fo)  {
		case 'none':
			fit.disabled=true;  fit.addClass('disabled');
			fot.disabled=true;  fot.addClass('disabled');
		break;
		case 'in':
			fit.disabled=false;  fit.removeClass('disabled');
			fot.disabled=true;  fot.addClass('disabled');
			fot.parentNode.insertBefore(fit, fot);
		break;
		case 'out':
			fot.disabled=false;  fot.removeClass('disabled');
			fit.disabled=true;  fit.addClass('disabled');
			fit.parentNode.insertBefore(fot, fit);
		break;
		case 'in or out':
			fit.disabled=false;  fit.removeClass('disabled');
			fot.disabled=false;  fot.removeClass('disabled');
			fot.parentNode.insertBefore(fit, fot);
		break;
		case 'in,out':
			fit.disabled=false;  fit.removeClass('disabled');
			fot.disabled=false;  fot.removeClass('disabled');
			fot.parentNode.insertBefore(fit, fot);
		break;
		case 'out,in':
			fit.disabled=false;  fit.removeClass('disabled');
			fot.disabled=false;  fot.removeClass('disabled');
			fit.parentNode.insertBefore(fot, fit);
		break;
	}

}
</script>
</head>
<body>
<?php

if (isset($_POST['submit']))  {   // WRAP MAIN PROCESSING SECTION ******************************************************************
	disable_magic_quotes_gpc();
	//  echo "<pre>",var_dump($_POST),"</pre>";
	Class bad_form_data extends Exception {}

//  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
if ($_POST['submit']==='verified')  {
	goto Verified_Section;  // at file end
	}
//  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

try {

	$_POST['dir_1']=trim($_POST['dir_1']);  $_POST['dir_2']=trim($_POST['dir_2']);
	if (preg_match(PATH, $d=$_POST['dir_1'])===0  or  preg_match(PATH, $d=$_POST['dir_2'])===0)
		throw new bad_form_data("Bad Source or Destination Directory — filename characters only: ".$d);
	if (substr($_POST['dir_1'], -1, 1)!==DIRECTORY_SEPARATOR)  $_POST['dir_1'].=DIRECTORY_SEPARATOR;
	if (substr($_POST['dir_2'], -1, 1)!==DIRECTORY_SEPARATOR)  $_POST['dir_2'].=DIRECTORY_SEPARATOR;
	if (!is_dir($d=$_POST['dir_1'])  or  !is_dir($d=$_POST['dir_2']))
		throw new bad_form_data("Bad Source or Destination Directory — Directory not found: ".$d);

	if (is_array($_POST['filter_in']['files']))  {
		$_POST['filter_in']['super-folders']=array();
		foreach ($_POST['filter_in']['files'] as &$fn)  {
			if (substr($fn, -2)===(DIRECTORY_SEPARATOR."*"))  {
				$fn=substr($fn, 0, -1);
				$_POST['filter_in']['super-folders'][]=DIRECTORY_SEPARATOR.$fn;  }  }  }

	$filters=array('out' => array(
		'exts' => check_array($_POST['filter_out']['exts'], EXT, "Bad Filter Out Extension — filename characters only"),
		'files' => check_array($_POST['filter_out']['files'], NAME, "Bad Filter Out Filename — filename characters only"),
		'paths' => check_array($_POST['filter_out']['paths'], PATH, "Bad Filter Out Path — filename characters only"),
		'regex' => check_array($_POST['filter_out']['regex'], REGEX, "Bad Filter Out Regular Expression"),
		'POSIX_wildcards' => check_array($_POST['filter_out']['POSIX_wildcards']) ),
									'in' => array(
		'exts' => check_array($_POST['filter_in']['exts'], EXT, "Bad Filter In Extension — filename characters only"),
		'files' => check_array($_POST['filter_in']['files'], NAME, "Bad Filter In Filename — filename characters only"),
		'super-folders' => $_POST['filter_in']['super-folders'],
		'paths' => check_array($_POST['filter_in']['paths'], PATH, "Bad Filter In Path — filename characters only"),
		'regex' => check_array($_POST['filter_in']['regex'], REGEX, "Bad Filter In Regular Expression"),
		'POSIX_wildcards' => check_array($_POST['filter_in']['POSIX_wildcards']) ) );


	$_POST['CaseInsense']= ($_POST['case_sense']==="no"  or  ($_POST['case_sense']==="auto"  and  MS_WINDOWS)) ?
						FNM_CASEFOLD : 0;  // this specific logic is used by Function filter() for POSIX Wildcards
	if ($_POST['CaseInsense'])  {
		$filters['in']['exts']=array_map('uncase', $filters['in']['exts']);
		$filters['in']['files']=array_map('uncase', $filters['in']['files']);
		$filters['in']['paths']=array_map('uncase', $filters['in']['paths']);
		$filters['out']['exts']=array_map('uncase', $filters['out']['exts']);
		$filters['out']['files']=array_map('uncase', $filters['out']['files']);
		$filters['out']['paths']=array_map('uncase', $filters['out']['paths']);
		foreach ($filters['in']['regex'] as &$pcre)  {
			$d=substr($pcre, 0, 1);
			if (1===preg_match(($d==='/' ? '#' : '/') . $d . "[^i".$d."]*$" . ($d==='/' ? '#' : '/'),  $pcre))
				$pcre=trim($pcre)."i";  }
		foreach ($filters['out']['regex'] as &$pcre)  {
			$d=substr($pcre, 0, 1);
			if (1===preg_match(($d==='/' ? '#' : '/') . $d . "[^i".$d."]*$" . ($d==='/' ? '#' : '/'),  $pcre))
				$pcre=trim($pcre)."i";  }  }



	$filters['in']['pass_all_folders']=in_array(DIRECTORY_SEPARATOR, $filters['in']['files']);
	if ( count($filters['in']['exts'])===0
	and  (count($filters['in']['files'])===0 or (count($filters['in']['files'])===1 and $filters['in']['pass_all_folders']))
	and  count($filters['in']['paths'])===0
	and  count($filters['in']['paths'])===0
	and  count($filters['in']['regex'])===0
	and  count($filters['in']['POSIX_wildcards'])===0 )
		$_POST['filter_order']= ($_POST['filter_order']==='in') ?  'none' : 'out';

	$dir1=read_dir($_POST['dir_1'], $filters, $_POST['recursive']==='no', $_POST['sort']==='yes');
	$dir2=read_dir($_POST['dir_2'], $filters, $_POST['recursive']==='no', $_POST['sort']==='yes');
//	echo "<pre>",var_dump($dir1, $dir2),"</pre>";
//echo "we got to here also"; exit;

	switch ($_POST['submit'])  {
	case "report only": $h1="Suggested Files to Sync from";
		echo "<div id='verifier'>\n";
	break;
	case "sync ’em": $h1="Files Synchronized from";
		echo "<div id='verifier'>\n";
	break;
	case "verify first":  $h1="Verify Files to Sync from";
		echo "<form id='verifier' action='syncdir.php' method='post'>\n",
					'<input type="hidden" name="verified[dir1]" value="',htmlentities($_POST['dir_1']),"\">\n",
					'<input type="hidden" name="verified[dir2]" value="',htmlentities($_POST['dir_2']),"\">\n",
					"<input type='hidden' name='verified[syncMethod]' value='{$_POST['sync_method']}'>\n";
	break;
	default: throw new bad_form_data("internal error: Bad Submit Method");  }

	$filecount=0;
	switch ($_POST['sync_method'])  {
	case "bi-directional":
		echo "<h1>$h1 <path>",htmlentities($_POST['dir_1']),"</path> to <path>",htmlentities($_POST['dir_2']),"</path></h1>\n";
		$uniq=find_unique($dir1, $dir2, $_POST['check_ages']==='no');
		$filecount+=count($uniq['paths']);
		if ($_POST['find_similar']==='yes')  find_misplaced($uniq, $dir2);
		if ($_POST['submit']==="sync ’em")
			$uniq['replaced']=syncdir($_POST['dir_1'], $_POST['dir_2'], $uniq['paths'], $_POST['preserveCreationTime']==='yes');
		$tree=build_dir_tree($_POST['dir_1'], $uniq['paths']);
		show_dir($tree, $uniq, $_POST['submit']==='verify first' ? 'in_dir1' : FALSE,  COMINGLE);
		//echo "<pre>",var_dump($tree, $uniq),"</pre>";
	case "uni-directional":
		echo "<h1>$h1 <path>",htmlentities($_POST['dir_2']),"</path> to <path>",htmlentities($_POST['dir_1']),"</path></h1>\n";
		$uniq=find_unique($dir2, $dir1, $_POST['check_ages']==='no');
		$filecount+=count($uniq['paths']);
		if ($_POST['find_similar']==='yes')  find_misplaced($uniq, $dir1);
		if ($_POST['submit']==="sync ’em")
			$uniq['replaced']=syncdir($_POST['dir_2'], $_POST['dir_1'], $uniq['paths'], $_POST['preserveCreationTime']==='yes');
		$tree=build_dir_tree($_POST['dir_2'], $uniq['paths']);
		show_dir($tree, $uniq, $_POST['submit']==='verify first' ? 'in_dir2' : FALSE,  COMINGLE);
		//echo "<pre>",var_dump($tree, $uniq),"</pre>";
	break;
	default: throw new bad_form_data("internal error: Bad Sync Method");  }

	switch ($_POST['submit'])  {
	case "verify first":
		if ($filecount>0)  echo "\n<input type='submit' name='submit' value='verified'>\n";
		echo "</form>\n";
	break;
	case "report only":  echo "</div>\n";
	break;
	case "sync ’em":  echo "\n</div>\n</body></html>\n";
	exit;  }  }

catch (bad_form_data $e)  {$errorHTML="<h5>".$e->getMessage()."</h5>\n";}
}   // END WRAP MAIN PROCESSING SECTION  ******************************************************************************



 //  *************  here is the main initial HTML page  ************************************************************
?>

<h1>Synchronize Directory Folders</h1>
<?php echo $errorHTML; ?>
<aside>Click<span> or hover</span> on <span class='helplink'>☻</span>s for help</aside>
<form action="syncdir.php" method='post' onkeydown='enhanceKeybrd(event);'>
<div id='options'>
	<fieldset id='dirInput'>
	<label class='folder'>folder1 path <input type='text' name='dir_1' value='<?php echo htmlentities($_POST['dir_1']);?>'></label>
	Synchronize files:&nbsp;
	<label title="bi-directional">between both folders<span>↕</span><input type='radio' name='sync_method' value="bi-directional" <?php
		if ($_POST['sync_method']=="bi-directional")  echo CHKD; ?>></label>
	<label title="uni-directional"><input type='radio' name='sync_method' value="uni-directional" <?php
		if ($_POST['sync_method']!="bi-directional")  echo CHKD; ?>><span>↑</span>from folder2 to folder1</label>
	<label class='folder'>folder2 path <input type='text' name='dir_2' value='<?php echo htmlentities($_POST['dir_2']);?>'></label>
	<help><span>☻</span><p>It is best to define paths from the root <?PHP echo MS_WINDOWS ? "drive (or user," : "user (or drive,"; ?>
	depending on <abbr title='Operating System'>OS</abbr>)
	and not to rely on <abbr>PHP</abbr> relative or “include” paths.</p></help>
	</fieldset>
	<fieldset id="recursive" onchange="disable_AllFolders(event.target.value==='no');">
	<legend>Include sub-directories recursively?</legend>
	<label><input type='radio' name='recursive' value='yes' <?php
		if ($_POST['recursive']!="no")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='recursive' value='no' <?php
		if ($_POST['recursive']=="no")  echo CHKD; ?>>No</label>
	</fieldset>
	<fieldset><legend>Check file ages when comparing?</legend>
	<label><input type='radio' name='check_ages' value='yes' <?php
		if ($_POST['check_ages']!="no")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='check_ages' value='no' <?php
		if ($_POST['check_ages']=="no")  echo CHKD; ?>>No</label>
	</fieldset>
	<fieldset><legend>Case-Sensitive<mark>‡</mark> File &amp; Folder Name Comparisons &amp; Filters?</legend>
	<label><input type='radio' name='case_sense' value='yes' <?php
	if ($_POST['case_sense']=="yes")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='case_sense' value='no' <?php
	if ($_POST['case_sense']=="no")  echo CHKD; ?>>No</label>
	<label><input type='radio' name='case_sense' value='auto' <?php
	if ($_POST['case_sense']!="yes"  and  $_POST['case_sense']!="no")  echo CHKD; ?>>Automatic by <abbr title='Operating System'>OS</abbr>:&nbsp; <?php
	echo php_uname('s'), (stripos(php_uname('s'), "win")===FALSE) ? " =Yes" :" =No" ; ?></label>
	</fieldset>
	<fieldset><legend>Look for similar files when unmatched?</legend>
	<label><input type='radio' name='find_similar' value='yes' <?php
		if ($_POST['find_similar']!="no")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='find_similar' value='no' <?php
		if ($_POST['find_similar']=="no")  echo CHKD; ?>>No</label>
	</fieldset>
	<fieldset><legend>Preserve original file “last-modified” time for copied file?</legend>
	<label><input type='radio' name='preserveCreationTime' value='yes' <?php
		if ($_POST['preserveCreationTime']!="no")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='preserveCreationTime' value='no' <?php
		if ($_POST['preserveCreationTime']=="no")  echo CHKD; ?>>No</label>
	</fieldset>
	<fieldset><legend>Sort the copied files?</legend>
	<label><input type='radio' name='sort' value='yes' <?php
		if ($_POST['sort']!="no")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='sort' value='no' <?php
		if ($_POST['sort']=="no")  echo CHKD; ?>>No</label>
	</fieldset>
	<fieldset><legend>Use trash folder for overwritten files?</legend>
	<label><input type='radio' name='trash' value='auto' <?php
		if ($_POST['trash']!="yes" and $_POST['trash']!="no")  echo CHKD; ?>>local where available</label>
	<label><input type='radio' name='trash' value='yes' <?php
		if ($_POST['trash']=="yes")  echo CHKD; ?>>create local folders</label>
	<label><input type='radio' name='trash' value='no' <?php
		if ($_POST['trash']=="no")  echo CHKD; ?>>No</label>
	<help><span>☻</span><p>When using “local folders where available,”
	if a <filename><?php echo TRASH_FOLDER_NAME; ?></filename> folder
	is found in the current directory, it will be used.&nbsp;
	If none is found, the parent directories (recursively) will be checked
	back up to the root directory path given.</p>
	<p>Local <filename><?php echo TRASH_FOLDER_NAME; ?></filename> folder(s)
	will be automatically created as needed (when that option is specified)
	in the current directory that the overwritten file is in.</p>
	<p>Files that are saved in the <filename><?php echo TRASH_FOLDER_NAME; ?></filename> folder
	are given additional extensions including the date &amp; time,
	in the form of <filename>.yyyy-mm-dd-hhmmss<?php echo TRASH_NAME_EXT; ?></filename></p>
	</help>
	</fieldset>
</div>

<section id='filters'>

<fieldset id="Filter_Order" onchange="align_filterTables();"><legend>Filter application order</legend>
<label><input type='radio' name='filter_order' value='in' <?php
	if ($_POST['filter_order']==="in")  echo CHKD; ?>>Filter in only</label>
<label><input type='radio' name='filter_order' value='out' <?php
	if ($_POST['filter_order']==="out")  echo CHKD; ?>>Filter out only</label>
<label><input type='radio' name='filter_order' value='in or out' <?php
	if (!isset($_POST['filter_order']) or $_POST['filter_order']==="in or out")  echo CHKD; ?>>Filter in, or out</label>
<label><input type='radio' name='filter_order' value='in,out' <?php
	if ($_POST['filter_order']==="in,out")  echo CHKD; ?>>Filter in, then out</label>
<label><input type='radio' name='filter_order' value='out,in' <?php
	if ($_POST['filter_order']==="out,in")  echo CHKD; ?>>Filter out, then in</label>
<label><input type='radio' name='filter_order' value='none' <?php
	if ($_POST['filter_order']==="none")  echo CHKD; ?>>No Filters applied</label>
<help><span>☻</span><p>If <em>no</em> “In Filters” are defined,
		all files will be filtered in unless filtered out.</p>
	<dl>
	<dt>Filter in, or out</dt>
	<dd>If a file matches an “in filter,” it will be included;
		otherwise, it must <em>not</em> match an “out filter” to be included.</dd>
	<dt>Filter in, then out</dt>
	<dd>A file must first be “filtered in,” and then <em>not</em> “filtered out” to be included.</dd>
	<dt>Filter out, then in</dt>
	<dd>A file must first <em>not</em> be “filtered out,” and then be “filtered in” to be included.</dd>
</dl><p>Note that “Filter out, then in” &amp; “Filter in, then out”
will pass the same files when used with the same filters,
but depending on the filters defined &amp; the folder/file names that exist,
one may be noticeably faster than the other on slow systems
with a large number of files in the directories to be synced.</p></help>
</fieldset>

<note><h3><mark>‡</mark>International Characters</h3>
<?php if (INTERNATIONAL_CHARS_SUPPORTED): ?>
<p>Directories must be character-encoded in <abbr>UTF-8</abbr>
(such as Microsoft Windows’ <abbr>NT</abbr> File System (<abbr>NTFS</abbr>) and <acronym>exFAT</acronym> formats)
for use with International characters when case-<em>in</em>sensitive.&nbsp;
<acronym>FAT</acronym>, <acronym>FAT16</acronym>, &amp; <acronym>FAT32</acronym> file systems
(typically found on <abbr>USB</abbr> “thumb drives”) often use the
Windows’ “<abbr title='Original Equipment Manufacturer'>OEM</abbr>” character-set
which typically differs from <abbr>UTF-8</abbr>.&nbsp;
<?php else: ?>
<p>This installation of <abbr>PHP</abbr> does not support International characters,
so files and directory folder names that have them
can not be properly compared in a case-<em>in</em>sensitive way.&nbsp;
Also, <acronym>FAT</acronym>, <acronym>FAT16</acronym>, &amp; <acronym>FAT32</acronym> file systems
(typically found on <abbr>USB</abbr> “thumb drives”) often use the
Windows “<abbr title='Original Equipment Manufacturer'>OEM</abbr>” character-set encoding
which typically differs from Microsoft Windows’ <abbr>NT</abbr> File System (<abbr>NTFS</abbr>)
and <acronym>exFAT</acronym> formats that use <abbr>UTF-8</abbr> encoding.&nbsp;
<?php endif; ?>
Files saved with the same name using International characters in different directory formats
(i.e. <acronym>FAT</acronym>, <acronym>FAT16</acronym>, <acronym>FAT32</acronym>, &amp; <acronym>LINUX</acronym>
<abbr title='versus'>vs.</abbr>
<abbr>NTFS</abbr>, <acronym>exFAT</acronym>, Joliet, &amp; <abbr>MacOS</abbr> <abbr>HFS-Plus</abbr> &amp; <abbr>APFS</abbr> filesystems)
may not be properly matched by this software, even when case-sensitive.&nbsp;</p>
<p><acronym>LINUX</acronym> does not specify a character set.&nbsp;
Each application defines the character-encoding for the filename it saves.&nbsp;
Therefore, this software may again fail to find matching and/or similar filenames
when files and/or folders are named using International characters in a <acronym>LINUX</acronym> file-system.&nbsp;
Similarly for any file-system, any file or folder names using International characters that are not
character-encoded using <abbr>UTF-8</abbr> may not match filters.</p>
</note>

<fieldset id="FilterIn"><table>
<caption>Filter In<mark>‡</mark></caption>
<thead><tr>
<th scope='col'>Filename Extensions</th>
<th scope='col'>File &amp; Folder Names
<help><span>☻</span><dl>
	<dt><filename>foo</filename></dt>
	<dt><filename>foo.bar</filename></dt>
	<dd>will match either files or directory folders</dd>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo</filename></dt>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo.bar</filename></dt>
	<dd>will match only files</dd>
	<dt><filename>foo<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dt><filename>foo.bar<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dd>will match only directory folders.&nbsp;
	If you filter in a directory folder, its files still need to be filtered in also.</dd>
	<dt><filename>foo<?php echo DIRECTORY_SEPARATOR; ?>*</filename></dt>
	<dd>If you end your folder-name with an asterisk, all of its sub-folders (recursively) will be included also.</dd>
</dl></help></th>
<th scope='col'>File &amp; Folder Paths
	<help><span>☻</span><p>It is best to define paths from the root <?PHP echo MS_WINDOWS ? "drive (or user," : "user (or drive,"; ?>
	depending on <abbr title='Operating System'>OS</abbr>)
	and not to rely on <abbr>PHP</abbr> relative or “include” paths.</p></help></th>
<th scope='col'>by Regular Expressions (<abbr title='Perl Compatible Regular Expression'>PCRE</abbr>)<span class='helplink'>
	<a href="https://www.php.net/manual/en/intro.pcre.php" target='php_pcre'>☻</a></span>
	<help><span>☻</span>
	<div><p><?php
	if (POSIX_WILDCARDS_PATTERNS_SUPPORTED)
		echo "Both Regular Expressions and POSIX Wildcard Patterns";
	else
		echo "Regular Expressions"; ?>
	can match either a complete path or simply a file or folder name.&nbsp;
	Be careful how you define them in that sense.</p>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo</filename></dt>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo.bar</filename></dt>
	<dd>typical pattern to match files</dd>
	<dt><filename>foo<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dt><filename>foo.bar<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dd>typical pattern to match directory folders</dd>
	<dl>
	</dl></div></help></th>
<?php if (POSIX_WILDCARDS_PATTERNS_SUPPORTED)  { ?>
<th scope='col'>by POSIX Filesystem Wildcard Patterns<span class='helplink'>
	<a href="https://www.php.net/manual/en/function.fnmatch.php" target='php_wildcards'>☻</a>
	<a href="https://tldp.org/LDP/GNU-Linux-Tools-Summary/html/x11655.htm" target='posix_wildcards'>☻</a></span></th><?php } ?>
</tr></thead>
<tbody><tr>
<td><fieldset><ul>
<?php  $in_exts=array(
	'docs'=>array(
		'.txt'=>FALSE,
		'.doc'=>FALSE,
		'.odt'=>FALSE,
		'.pdf'=>FALSE,
		'.xps'=>FALSE ),
	'scripts'=>array(
		'.htm'=>FALSE,
		'.html'=>FALSE,
		'.html5'=>FALSE,
		'.css'=>FALSE,
		'.js'=>FALSE,
		'.ts'=>FALSE,
		'.json'=>FALSE,
		'.node'=>FALSE,
		'.php'=>FALSE,
		'.pl'=>FALSE,
		'.py'=>FALSE,
		'.rb'=>FALSE,
		'.CMD'=>FALSE,
		'.sh'=>FALSE ),
	'images'=>array(
		'.jpg'=>FALSE,
		'.gif'=>FALSE,
		'.png'=>FALSE,
		'.bmp'=>FALSE,
		'.svg'=>FALSE,
		'.webp'=>FALSE,
		'.tiff'=>FALSE ),
	'music'=>array(
		'.wav'=>FALSE,
		'.wma'=>FALSE,
		'.flac'=>FALSE,
		'.aac'=>FALSE,
		'.mp3'=>FALSE,
		'.webm'=>FALSE,
		'.ogg'=>FALSE,
		'.aiff'=>FALSE,
		'.alac'=>FALSE),
	'video'=>array(
		'.webm'=>FALSE,
		'.mpeg'=>FALSE,
		'.mpg'=>FALSE,
		'.mp2'=>FALSE,
		'.mpe'=>FALSE,
		'.mpv'=>FALSE,
		'.ogg'=>FALSE,
		'.mp4'=>FALSE,
		'.m4p'=>FALSE,
		'.m4v'=>FALSE,
		'.avi'=>FALSE,
		'.wmv'=>FALSE,
		'.mov'=>FALSE,
		'.qt'=>FALSE) );

show_file_exts('filter_in', $in_exts);

//  =============================================
Function show_file_exts($group, $extentions)  {
 foreach ($extentions as $ext => $default_checked)  {
	if (is_array($default_checked)):
		?>
	<li><fieldset onchange='check_legend(this);'>
	<legend onclick='this.parentNode.toggleClass("open");'
				 ><span>▼</span><span class='open'>▲</span><label><input
					type='checkbox' onchange='check_all_in_group(this);' <?php
					if (isset($_POST['submit']))  {
						if (is_array($_POST[$group]['exts'])
						and count(array_diff(array_keys($default_checked), $_POST[$group]['exts']))===0)
							echo CHKD;  }
					else if (!in_array(FALSE, $default_checked))  echo CHKD;
					echo ">",htmlentities($ext); ?></label></legend>
		<ul>
<?php show_file_exts($group, $default_checked); ?>
		</ul></fieldset></li>
<?php continue;
	endif;   ?>
		<li><label><input type='checkbox' name='<?php echo $group; ?>[exts][]' value='<?php echo htmlentities($ext),"' ";
			¿checkIt($group, $ext, $default_checked); ?>><?php echo htmlentities($ext); ?></label></li>
<?php  }  }
//  =============================================


if ($¿isA=is_array($_POST['filter_in']['exts']))
	$_POST['filter_in']['exts']=array_diff($_POST['filter_in']['exts'], array_keys_deep($in_exts));
do { ?>
	<li><input type='text' name='filter_in[exts][]' value="<?php if ($¿isA) echo array_shift($_POST['filter_in']['exts']); ?>"
		onblur='popNewField(this.parentNode)' onfocus='tabbedOut=false' title='enter your own Extension'></li>
<?php } while ($¿isA  and  count($_POST['filter_in']['exts'])>0); ?>
</ul></fieldset></td>

<td>
	<label id='AllFolders'><input type='checkbox' name='filter_in[files][]' value="<?php echo DIRECTORY_SEPARATOR; ?>" <?php
		if (($¿isA=is_array($_POST['filter_in']['files']))
		and  FALSE !== ($k=@array_search(DIRECTORY_SEPARATOR, $_POST['filter_in']['files'])))  {
			echo CHKD;  unset($_POST['filter_in']['files'][$k]);  }
		?>> <?php echo DIRECTORY_SEPARATOR; ?> &nbsp; <note>(this means all directory folders)</note></label>
<?php  $fn="";  do {
	if ($¿isA) {
		$fn=array_shift($_POST['filter_in']['files']);
		if (is_array($_POST['filter_in']['super-folders'])
		&&  in_array(DIRECTORY_SEPARATOR.$fn, $_POST['filter_in']['super-folders']))  $fn.="*";  }
	?><input type='text' name='filter_in[files][]' value="<?php echo $fn; ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a File or Folder name'>
	<?php } while ($¿isA  and  count($_POST['filter_in']['files'])>0); ?></td>

<?php $¿isA=is_array($_POST['filter_in']['paths']); ?>
<td><?php do { ?><input type='text' name='filter_in[paths][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_in']['paths']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a File or Folder path'>
	<?php } while ($¿isA  and  count($_POST['filter_in']['paths'])>0); ?></td>

<?php $¿isA=is_array($_POST['filter_in']['regex']); ?>
<td><?php do { ?><input type='text' name='filter_in[regex][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_in']['regex']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a Perl-Compatible Regular Expression (File or Folder) (name or path) filter'>
	<?php } while ($¿isA  and  count($_POST['filter_in']['regex'])>0); ?></td>

<?php if (POSIX_WILDCARDS_PATTERNS_SUPPORTED)  {
	$¿isA=is_array($_POST['filter_in']['regex']); ?>
<td><?php do { ?><input type='text' name='filter_in[POSIX_wildcards][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_in']['POSIX_wildcards']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a POSIX Wildcard Pattern (File or Folder) (name or path) filter'>
<?php } while ($¿isA  and  count($_POST['filter_in']['POSIX_wildcards'])>0); ?></td><?php } ?>
</tr></tbody>
</table></fieldset>

<fieldset id="FilterOut"><table>
<caption>Filter Out<mark>‡</mark></caption>
<thead><tr>
<th scope='col'>Filename Extensions</th>
<th scope='col'>File &amp; Folder Names<help><span>☻</span>
<dl>
	<dt><filename>foo</filename></dt>
	<dt><filename>foo.bar</filename></dt>
	<dd>will match either files or directory folders</dd>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo</filename></dt>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo.bar</filename></dt>
	<dd>will match only files</dd>
	<dt><filename>foo<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dt><filename>foo.bar<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dd>will match only directory folders</dd>
</dl></help></th>
<th scope='col'>File &amp; Folder Paths
	<help><span>☻</span><p>It is best to define paths from the root <?PHP echo MS_WINDOWS ? "drive (or user," : "user (or drive,"; ?>
	depending on <abbr title='Operating System'>OS</abbr>)
	and not to rely on <abbr>PHP</abbr> relative or “include” paths.</p></help></th>
<th scope='col'>by Regular Expressions (<abbr title='Perl Compatible Regular Expression'>PCRE</abbr>)<span class='helplink'>
	<a href="https://www.php.net/manual/en/intro.pcre.php" target='php_pcre'>☻</a></span>
	<help><span>☻</span>
	<div><p><?php
	if (POSIX_WILDCARDS_PATTERNS_SUPPORTED)
		echo "Both Regular Expressions and POSIX Wildcard Patterns";
	else
		echo "Regular Expressions"; ?>
	can match either a complete path or simply a file or folder name.&nbsp;
	Be careful how you define them in that sense.</p>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo</filename></dt>
	<dt><filename><?php echo DIRECTORY_SEPARATOR; ?>foo.bar</filename></dt>
	<dd>typical pattern to match files</dd>
	<dt><filename>foo<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dt><filename>foo.bar<?php echo DIRECTORY_SEPARATOR; ?></filename></dt>
	<dd>typical pattern to match directory folders</dd>
	<dl>
	</dl></div></help></th>
<?php if (POSIX_WILDCARDS_PATTERNS_SUPPORTED): ?>
<th scope='col'>by POSIX Filesystem Wildcard Patterns<span class='helplink'>
	<a href="https://www.php.net/manual/en/function.fnmatch.php" target='php_wildcards'>☻</a>
	<a href="https://tldp.org/LDP/GNU-Linux-Tools-Summary/html/x11655.htm" target='posix_wildcards'>☻</a></span></th>
<?php endif; ?>
</tr></thead>
<tbody><tr>
<td><fieldset><ul>
<?php  $out_exts=array(
	TRASH_NAME_EXT=>TRUE,
	'.exe'=>TRUE,
	'.lnk'=>TRUE,
	'fonts'=>array(
		'.ttf'=>TRUE,
		'.otf'=>TRUE,
		'.woff'=>TRUE ),
	'docs'=>array(
		'.txt'=>FALSE,
		'.doc'=>FALSE,
		'.odt'=>FALSE,
		'.pdf'=>FALSE,
		'.xps'=>FALSE ),
	'scripts'=>array(
		'.htm'=>FALSE,
		'.html'=>FALSE,
		'.html5'=>FALSE,
		'.css'=>FALSE,
		'.js'=>FALSE,
		'.ts'=>FALSE,
		'.json'=>FALSE,
		'.node'=>FALSE,
		'.php'=>FALSE,
		'.pl'=>FALSE,
		'.py'=>FALSE,
		'.rb'=>FALSE,
		'.CMD'=>FALSE,
		'.sh'=>FALSE ),
	'images'=>array(
		'.jpg'=>FALSE,
		'.gif'=>FALSE,
		'.png'=>FALSE,
		'.bmp'=>FALSE,
		'.svg'=>FALSE,
		'.webp'=>FALSE,
		'.tiff'=>FALSE ),
	'music'=>array(
		'.wav'=>FALSE,
		'.wma'=>FALSE,
		'.flac'=>FALSE,
		'.aac'=>FALSE,
		'.mp3'=>FALSE,
		'.webm'=>FALSE,
		'.ogg'=>FALSE,
		'.aiff'=>FALSE,
		'.alac'=>FALSE),
	'video'=>array(
		'.webm'=>FALSE,
		'.mpeg'=>FALSE,
		'.mpg'=>FALSE,
		'.mp2'=>FALSE,
		'.mpe'=>FALSE,
		'.mpv'=>FALSE,
		'.ogg'=>FALSE,
		'.mp4'=>FALSE,
		'.m4p'=>FALSE,
		'.m4v'=>FALSE,
		'.avi'=>FALSE,
		'.wmv'=>FALSE,
		'.mov'=>FALSE,
		'.qt'=>FALSE) );

show_file_exts('filter_out', $out_exts);

if ($¿isA=is_array($_POST['filter_out']['exts']))
	$_POST['filter_out']['exts']=array_diff($_POST['filter_out']['exts'], array_keys_deep($out_exts));
do { if ($¿isA  and  ($ext=array_shift($_POST['filter_out']['exts']))!=="")  { ?>
	<li><input type='text' name='filter_out[exts][]' value="<?php echo $ext; ?>"
		onblur='popNewField(this.parentNode)' onfocus='tabbedOut=false' title='enter your own Extension'></li>
<?php }  } while ($¿isA  and  count($_POST['filter_out']['exts'])>0); ?>
</ul></fieldset></td>

<td>
<?php $¿isA=is_array($_POST['filter_out']['files']); ?>
	<label><input type='checkbox' name='filter_out[files][]' value='Thumbs.db'   <?php if (!$¿isA  or  in_array('Thumbs.db', $_POST['filter_out']['files']))  echo "checked";?>><filename>Thumbs.db</filename></label>
	<label><input type='checkbox' name='filter_out[files][]' value='desktop.ini' <?php if (!$¿isA  or  in_array('desktop.ini', $_POST['filter_out']['files']))  echo "checked";?>><filename>desktop.ini</filename></label>
	<label><input type='checkbox' name='filter_out[files][]' value='trash<?php echo DIRECTORY_SEPARATOR; ?>' <?php if (!$¿isA  or  in_array('trash'.DIRECTORY_SEPARATOR, $_POST['filter_out']['files']))  echo "checked";?>><filename>trash<?php echo DIRECTORY_SEPARATOR; ?></filename></label>
<?php if ($¿isA)
		$_POST['filter_out']['files']=array_diff($_POST['filter_out']['files'], array("Thumbs.db", "desktop.ini"));
do { ?>
<input type='text' name='filter_out[files][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_out']['files']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a File or Folder name'>
	<?php } while ($¿isA  and  count($_POST['filter_out']['files'])>0); ?></td>

<?php $¿isA=is_array($_POST['filter_out']['paths']) ?>
<td><?php do { ?><input type='text' name='filter_out[paths][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_out']['paths']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a File or Folder Path'>
	<?php } while ($¿isA  and  count($_POST['filter_out']['paths'])>0); ?></td>

<?php $¿isA=is_array($_POST['filter_out']['paths']) ?>
<td><?php do { ?><input type='text' name='filter_out[regex][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_out']['regex']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a Perl-Compatible Regular Expression (File or Folder) (name or path) filter'>
	<?php } while ($¿isA  and  count($_POST['filter_out']['regex'])>0); ?></td>

<?php if (POSIX_WILDCARDS_PATTERNS_SUPPORTED)  {
	$¿isA=is_array($_POST['filter_out']['paths']) ?>
<td><?php do { ?><input type='text' name='filter_out[POSIX_wildcards][]' value="<?php if ($¿isA)  echo array_shift($_POST['filter_out']['POSIX_wildcards']); ?>"
	onblur='popNewField(this)' onfocus='tabbedOut=false' title='enter a POSIX Wildcard Pattern (File or Folder) (name or path) filter'>
	<?php } while ($¿isA  and  count($_POST['filter_out']['POSIX_wildcards'])>0); ?></td><?php } ?>
</tr></tbody>
</table></fieldset>

</section>

<input type='submit' name='submit' value="report only">
<input type='submit' name='submit' value="verify first">
<input type='submit' name='submit' value="sync ’em">
</form>
<footer>by SoftMoon WebWare © 2021, 2024</footer>
<script type='text/javascript'>
for (const inp of document.getElementById('recursive').elements) {
	if (inp.checked)  {disable_AllFolders(inp.value==='no');  break;}  }

for (const inp of document.getElementsByTagName('input'))  {
	if (inp.type==='radio')  inp.parentNode.useClass('checked', inp.checked);  }

align_filterTables();

document.body.addEventListener('change', function(event) {
	if (event.target.type!=='radio')  return;
	for (const inp of event.target.closest('fieldset').elements)  {
		inp.parentNode.useClass('checked', inp.checked);  }  });
</script>
</body>
</html>

<?php  exit;

Function ¿checkIt($fltr, $value, $dflt=FALSE)  {
	if (($dflt  and  !is_array($_POST[$fltr]['exts']))
	or  (is_array($_POST[$fltr]['exts'])  and  in_array($value, $_POST[$fltr]['exts'])))  echo CHKD;  }

Function disable_magic_quotes_gpc()  {
	if (TRUE == function_exists('get_magic_quotes_gpc')  and  1 == get_magic_quotes_gpc())
		if (strtolower(ini_get('magic_quotes_sybase')=="on"))  $_POST=str_unquote_deep($_POST);
		else  $_POST=stripslashes_deep($_POST);
	return $_POST;  }

Function stripslashes_deep($value)  {
	$value=(is_array($value)) ?  array_map('stripslashes_deep', $value)  :  stripslashes($value);
	return $value;  }

Function str_unquote_deep($value)  {
	$value=(is_array($value)) ?  array_map('str_unquote_deep', $value)  :  str_replace("''", "'", $value);
	return $value;  }

Function check_array(&$a, $pattern=FALSE, $err_msg="")  { if ($a==NULL)  return array();
	if (!is_array($a))  throw new bad_form_data("internal error: Bad Filter Info Structure");
	$a=array_filter($a, "strlen");
//	if ($pattern  and  count(preg_grep($pattern, $a, PREG_GREP_INVERT))>0)  throw new bad_form_data($err_msg);
	if ($pattern  /* fuck the PHP developers for PHP8 */
	and	$temp=preg_grep($pattern, $a, PREG_GREP_INVERT)
	and is_array($temp)
	and count($temp)>0)  throw new bad_form_data($err_msg);
	return ($a);  }

Function read_dir($dir, &$filters, $¿shallow=false, $¿sort=true)  {
	if (substr($dir, -1, 1)!==DIRECTORY_SEPARATOR)  $dir.=DIRECTORY_SEPARATOR;
	$filelist = array();  $filepaths = array();  $subdirs=array();
	$d = dir($dir);
	while (false !== ($entry = $d->read()))  {
		if ($entry==='.'  or  $entry==='..'  or  $entry===""
		or  filter_file($entry, $dir.$entry, $filters, $srch_val))  continue;
		if (is_dir($dir.$entry))  {                  // ↑ value is returned
			if ($¿shallow)  continue;
			$subdirs[$entry] = null;  }
		else {
			$filelist[] = $entry;
			$filepaths[] = $srch_val;  }  }
	$d->close();
	if ($¿sort  &&  count($filelist))  {
		array_multisort(
			$filelist,
			SORT_ASC,
			$¿sort=SORT_NATURAL | ($_POST['CaseInsense'] ? SORT_FLAG_CASE : 0),
			$filepaths );  }
	if (count($subdirs))  {
		if ($¿sort)  ksort($subdirs, $¿sort);
		foreach ($subdirs as $subdir => &$d)  {$d = read_dir($dir.$subdir, $filters, $¿shallow, $¿sort);}
		$filelist['/']=$subdirs;  }
	$filelist['.']=$dir;
	$filelist['?']=$filepaths;
	return $filelist;  }


// find directory entries that are in $dir1 that are not in $dir2
// (make sure they are the same file - length -)
// or possibly if the age of a file in $dir1 is - younger - than the same file in $dir2
Function find_unique(&$dir1, &$dir2, $¿ignore_age=true, $subpath=DIRECTORY_SEPARATOR)  {
	// NOTE: filesize() is accurate up to 2GB on 32bit systems, 4GB on 64bit systems; but still works for comparisons up to 4GB.
	$unique=array('names'=>array(), '?'=>array(), 'sizes'=>array(), 'paths'=>array());
	if (is_array($dir1['?']))  foreach ($dir1['?'] as $k => $filename)  {
		$path1=$dir1['.'].$dir1[$k];
		if ( is_array($dir2['?'])
		and  is_numeric($k2=array_search($filename, $dir2['?']))  /* could be false or null or 0 */
		and  filesize($path1)===filesize($path2 = $dir2['.'].$dir2[$k2])
		and  ($¿ignore_age
					or  filemtime($path1)<=filemtime($path2)) )
			continue;
		$unique['names'][]=$dir1[$k];
		$unique['?'][]=$filename;
		$unique['sizes'][]=filesize($path1);
		$unique['paths'][]=$path1;
		$unique['subpaths'][]=$subpath;
		$unique['replaced'][]= is_numeric($k2);  }
	if (isset($dir1['/']))  {
		$dir2subs= (isset($dir2['/']) ? $dir2['/'] : array());
		foreach ($dir1['/'] as $dirname => $subdir1)  {
			$subdir2=isset($dir2subs[$dirname]) ? $dir2subs[$dirname] : array();
			$unique=array_merge_recursive( $unique,
				find_unique($subdir1, $subdir2, $¿ignore_age, $subpath.$dirname.DIRECTORY_SEPARATOR) );  }  }
	return $unique;  }

Function find_misplaced(&$unique, &$dir, $subpath=DIRECTORY_SEPARATOR)  {
	if (!is_array($unique['similars']))  $unique['similars']=array();
	if (is_array($dir['?']))
		foreach ($dir['?'] as $dk => $filename)  {
			$path=$dir['.'].$dir[$dk];
			if (count($keys=array_keys($unique['?'], $filename)))
				foreach ($keys as $uk)  {
					if ($subpath===$unique['subpaths'][$uk])  continue;
					if (!is_array($unique['similars'][$uk]))  $unique['similars'][$uk]=array();
					if (!in_array($path, $unique['similars'][$uk]))  $unique['similars'][$uk][]=$path;  }
			if (count($keys=array_keys($unique['sizes'], filesize($path))))
				foreach ($keys as $uk)  {
					if ($subpath===$unique['subpaths'][$uk])  continue;
					if (!is_array($unique['similars'][$uk]))  $unique['similars'][$uk]=array();
					if (!in_array($path, $unique['similars'][$uk]))  $unique['similars'][$uk][]=$path;  }  }
	if (is_array($dir['/']))
		foreach ($dir['/'] as $dirname => $subdir)  {find_misplaced($unique, $subdir, $subpath.$dirname.DIRECTORY_SEPARATOR);}  }


Function filter_file($filename, $path, &$filters, &$srch_val)  {
	//return TRUE if file is to be - ignored -
	//                                   ↓ strtolower, or mb_strtolower when supported
	$srch_val= ($_POST['CaseInsense'] ?  uncase($filename) : $filename);
	if ($_POST['CaseInsense'])  $path=uncase($path);
	switch ($_POST['filter_order'])  {
		case "none":    return false;
		case "in":      return filter($srch_val, $path, $filters['in'], false);
		case "out":     return filter($srch_val, $path, $filters['out'], true);
		case "in,out":  return filter($srch_val, $path, $filters['in'], false) or filter($srch_val, $path, $filters['out'], true);
		case "out,in":  return filter($srch_val, $path, $filters['out'], true) or filter($srch_val, $path, $filters['in'], false);
		case "in or out":
			return filter($srch_val, $path, $filters['in'], true) ? false : filter($srch_val, $path, $filters['out'], true);  }  }

Function filter($filename, $path, &$filter, $logic_bool)  {
	//return TRUE if file is to be - ignored - EXCEPT “filter in or out” returns true if the file is filtered -in-
	if ((($isdir=is_dir($path))  and  $filter['pass_all_folders'])
	or  in_array($filename, $filter['files'])
	or  in_array($path, $filter['paths'])
	or  in_array($name= ($isdir ? $filename.DIRECTORY_SEPARATOR : DIRECTORY_SEPARATOR.$filename), $filter['files']))
		return $logic_bool;
	if ($isdir)  foreach ($filter['super-folders'] as $folder)  {
		if (str_contains($path, $folder))  return $logic_bool;  }
	foreach ($filter['exts'] as $ext)  {
		if ($ext===substr($filename, -strlen($ext)))  return $logic_bool;  }
	foreach ($filter['regex'] as $pcre)  {
		if (@preg_match($pcre, $name)
		or  @preg_match($pcre, $path))  return $logic_bool;  }
	foreach ($filter['POSIX_wildcards'] as $wc)  {
		if (@fnmatch($wc, $name, $_POST['CaseInsense'])
		or  @fnmatch($wc, $path, $_POST['CaseInsense']))  return $logic_bool;  }
	return !$logic_bool;  }

Function build_dir_tree($base, &$filepaths)  {
	$tree=array();
	$base_len=strlen($base);
	if (substr($base, -1)===DIRECTORY_SEPARATOR)  $base=substr($base, 0, -1);
	else  $base_len++;
	foreach($filepaths as $k=>$path)  {
		$path=explode(DIRECTORY_SEPARATOR, substr($path, $base_len));
		array_unshift($path, $base);
		build_mdarray($tree, $path, $k);  }
	return $tree;  }

Function show_dir(&$tree, &$files, $verify, $comingle=false, $tabs="")  {
	if (count($tree)==0)  {echo "<h2>—none—</h2>\n";  return;}
	$html="";  ksort($tree, SORT_STRING);
	echo $tabs,"<ul onchange='check_dir(this)'>\n";
	foreach ($tree as $path => $k)  {
		if (is_array($k))  {  // $path is a sub-directory in this case
			echo $tabs, '<li class="expand">',EXPANDER;
			if ($verify)  echo '<label><input type="checkbox" onchange="check_all_in_dir(this)">';
			echo '<path>', htmlentities($path),DIRECTORY_SEPARATOR, '</path>';
			if ($verify)  echo '</label>';
			echo "\n";
			show_dir($k, $files, $verify, $comingle, $tabs."\t");
			echo $tabs,"</li>\n";
			continue;  }
		$html.= $tabs . '<li>';
		if ($verify)  $html.= '<label><input type="checkbox" name="verified[' . $verify . '][]" value="' . htmlentities($files['paths'][$k]) . '">';
		$class="";
		if ($files['replaced'][$k])  $class.='replacement ';
		if (substr($path, 0,1)===chr(24))  $class.='failed-copy';
		if ($class)  $class=' class="'.$class.'"';
		$html.= '<path' . $class . '>' . htmlentities($path) . '</path>';  // $files['names'][$k]
		if ($verify)  $html.= '</label>';
		if (is_string($files['replaced'][$k]))
			$html.= "\n".$tabs.'<div class="replaced"><mark>&rArr;</mark><path'.(substr($files['replaced'][$k], 0,1)===chr(24) ? ' class="failed-copy"' : "").'>' . htmlentities($files['replaced'][$k]) . '</path></div>';
		if (isset($files['similars'])  and  $files['similars'][$k])  {
			$html.= "\n".$tabs.'<div class="similar">'.EXPANDER."Similar files found:\n <ul>\n";
			foreach ($files['similars'][$k] as $spath)  {$html.= ' <li><path>'.htmlentities($spath)."</path></li>\n";}
			$html.= $tabs." </ul></div>";  }
		$html.= "</li>\n";
		if ($comingle)  {echo $html;  $html="";}  }
	if (!$comingle)  echo $html;
	echo $tabs,"</ul>\n";  }


Function build_mdarray(&$a, $keys, $v)  {
	$k=array_shift($keys);
	if (count($keys)===0)  {
		if (isset($a[$k]))  {
			if (is_array($a[$k]))  $a[$k][]=$v;
			else  $a[$k]=array($a[$k], $v);  }
		else  $a[$k]=$v;
		return true;  }
	if (isset($a[$k]))  {
		if (!is_array($a[$k]))  return false;  }
	else  $a[$k]=array();
	return build_mdarray($a[$k], $keys, $v);  }

Function array_keys_deep(&$A)  {
	$keys=array();
	array_walk_recursive($A, function($v, $k) use (&$keys)  {array_push($keys, $k);});
	return $keys;  }


Function syncdir($src_dir, $dest_dir, &$filepaths, $doKeepOrgCreationTime)  {
	$sd_len=strlen($src_dir);
	$replaced=array();
	foreach ($filepaths as $k => &$path)  {
		$file=basename($path);  $dest_path=dirname($path).DIRECTORY_SEPARATOR;
		if (substr($dest_path, 0, $sd_len)!==$src_dir)  throw new bad_form_data('internal error — Verified Pathname does not match Source Directory');
		$subpath=substr($dest_path, $sd_len);
		$dest_path=$dest_dir.$subpath;
		$dest=$dest_path.$file;
		$replaced[$k]=FALSE;
		if (!is_dir($dest_path))  {mkdir($dest_path, 0777, true);  chmod($dest_path, 0777);}
		else
		if (is_file($dest))  $replaced[$k]=trash($file, $subpath, $dest_dir);
		// note if copy fails, any matching file at the destination was still trashed!
		if (copy($path, $dest))  {
			if ($doKeepOrgCreationTime)  touch($dest, filemtime($path));  }
		else  $path=chr(24).$path;  }  //  ASCII CAN  “cancel”
	return $replaced;  }

Function trash($file, $path, $root)  {
	switch ($_POST['trash'])  {
	case "yes":
		$trash=$root.$path.TRASH_FOLDER_NAME;
		if (!is_dir($trash))  {mkdir($trash);  chmod($trash, 0777);}
	break;
	case "auto":
		$subpath=$path;
		do {$trash=$root.$subpath.TRASH_FOLDER_NAME;}
		while ( !is_dir($trash)  and  $subpath
			and  ( ($p=strrpos($subpath, DIRECTORY_SEPARATOR, -1)  and  $subpath=substr($subpath, 0, $p+1))
						or  ($subpath="" or true) ) ); // if strrpos returned (-1) instead of false we could eliminate this last part
		if (is_dir($trash))  break;
	case "no":  unlink($root.$path.$file);
	return TRUE;
	default: throw new bad_form_data('internal error — Undefined Trash Location');  }
	$x=0;  $date=date('Y-m-d-H-i-s');
	do {$trash_path= $trash . $file . '.[' . $date . '].[' . sprintf('%3d', ++$x) . '].' . TRASH_NAME_EXT;}
	while (is_file($trash_path));
	if (!rename($root.$path.$file, $trash_path))  $trash_path=chr(24).$trash_path;  //  ASCII CAN  “cancel”
	return $trash_path;  }




	//  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Verified_Section:

try {
	if (preg_match(PATH, $_POST['verified']['dir1'])===0  or  preg_match(PATH, $_POST['verified']['dir2'])===0)
		throw new bad_form_data("internal error - Bad Verified Source or Destination Directory — filename characters only");
	if (!is_dir($_POST['verified']['dir1'])  or  !is_dir($_POST['verified']['dir2']))
		throw new bad_form_data("internal error - Bad Verified Source or Destination Directory — Directory not found");
	$_POST['verified']['in_dir1']=check_array($_POST['verified']['in_dir1'], PATH, "internal error - Bad Verified File path");
	$_POST['verified']['in_dir2']=check_array($_POST['verified']['in_dir2'], PATH, "internal error - Bad Verified File path");

	echo "<div id='verified'><h1>Verified Files Synchronized ",
		($_POST['verified']['syncMethod']==="bi-directional") ? "between" : "from",
		"<path>",htmlentities($_POST['verified']['dir2']),"</path>",
		($_POST['verified']['syncMethod']==="bi-directional") ? "and" : "to",
		"<path>",htmlentities($_POST['verified']['dir1']),"</path></h1>\n";

	if (count($_POST['verified']['in_dir1'])>0)  {
		$uniq=array('paths'=> &$_POST['verified']['in_dir1']);
		$uniq['replaced']=
			syncdir($_POST['verified']['dir1'], $_POST['verified']['dir2'], $uniq['paths'], $_POST['preserveCreationTime']==='yes');
		$tree=build_dir_tree($_POST['verified']['dir1'], $uniq['paths']);
		show_dir($tree, $uniq, FALSE,  COMINGLE);  }

	if (count($_POST['verified']['in_dir2'])>0)  {
		$uniq=array('paths'=> &$_POST['verified']['in_dir2']);
		$uniq['replaced']=
			syncdir($_POST['verified']['dir2'], $_POST['verified']['dir1'], $uniq['paths'], $_POST['preserveCreationTime']==='yes');
		$tree=build_dir_tree($_POST['verified']['dir2'], $uniq['paths']);
		show_dir($tree, $uniq, FALSE,  COMINGLE);  }

	echo "</div>";

} catch (bad_form_data $e)  {echo "<h5>", $e->getMessage(), "</h5>\n";}
?>
</body>
</html>
