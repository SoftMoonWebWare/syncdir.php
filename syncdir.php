<?php  //  charset='UTF-8'   EOL: 'UNIX'   tab spacing=2 ¡important!   word-wrap: no
	/*/ SyncDir.php   written by and Copyright © Joe Golembieski, SoftMoon WebWare
					 BETA 1.7  December 23, 2024

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

/*  This BETA release has been tested on a Windows® NT system with Apache 2.0 and PHP version 7.1.6 ; 8.1.6 ; 8.2.12
 *  I’ve been using it for a while mostly without problems, except:
 *  If there is a problem with the filesystem that causes it to “lock up”,
 *  PHP just endlessly waits… (adjust the max_execution_time below).
 *  This has happened twice to me due to hardware problems, not this software.
 *  Both times the problem was solved by removing the USB drive.
 *  Windows® was locked up also.
 *
 *  It’s never actually skrewed up anything in the filesystem, but use at your own risk!
*/


//  ↓ ↓ ↓ for ease of use, you can customize this if you don't have access to your php.ini file
//  ↓ ↓ ↓ or if your PHP installation for some reason ignores this ini setting in said file
//ini_set("date.timezone", "");

ini_set("max_execution_time", 0);  // 0 = ∞     1500 =25 min     1800 =30 min
clearstatcache(true);

define ('TRASH_FOLDER_NAME', ".trash".DIRECTORY_SEPARATOR);  //this could be a “hidden” file as given, or have a full name.ext
define ('TRASH_NAME_EXT', ".trash");  //should generally match the above’s extension.

define("MS_WINDOWS", stripos(php_uname('s'), 'Win')!==FALSE);
define('POSIX_WILDCARDS_PATTERNS_SUPPORTED', (!MS_WINDOWS  or  phpversion()>="5.3.0"));

define ('INTERNATIONAL_CHARS_SUPPORTED', function_exists('mb_strtolower'));
if (INTERNATIONAL_CHARS_SUPPORTED)  {
	Function uncase($s) {return mb_strtolower($s, 'UTF-8');}  }
else  {
	Function uncase($s) {return strtolower($s);}  }

// these are for checking user input
define('EXT', '#^[^\\\\/?:;*"<>|]+$#');
if (MS_WINDOWS)  { // MS Windows® directory separator
	define('NAME', '#^\\\\$|^(\\\\)?[^\\\\/?:;*"<>|]+(?(1)[^\\\\/?:;*"<>|]|[^/?:;*"<>|])$#');
	define('PATH', '#^(([A-Z]:\\\\)?[^/:;?*"<>|]+)|([A-Z]:\\\\)$#i');  }
else  { // Mac OS/LINUX/UNIX directory separator
	define('NAME', '#^(/)?[^\\\\/:;?*"<>|]+(?(1)|/)$#');
	define('PATH', '#^[^\\\\:;?*"<>|]+(/)?$#');  }
define ('REGEX', '/^(.).+\\1[igme]{0,4}$/');   //


//define ('CHKD', "checked='checked' ");
define ('CHKD', "checked ");
define ('EXPANDER',
	'<span class="expand" onclick="this.parentNode.classList.add(\'expand\')">▼</span><span class="collapse" onclick="this.parentNode.classList.remove(\'expand\')">▲</span>'	);

if (!defined('FNM_CASEFOLD'))  define ('FNM_CASEFOLD', 16);  // for Function filter()

define ('FOLDERS_FIRST', true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name='author' content='Joe Golembieski, SoftMoon-WebWare'>
<meta name='copyright' content='Copyright © 2021, 2024 Joe Golembieski, SoftMoon-WebWare'>
<title>SyncDir.php</title>
<style type="text/css">
body {
	color: lime;
	background-color: black;
	font-family: sans-serif;
	padding-bottom: 1.618em; }
body.dragging {
	cursor: not-allowed; }
body.dragging ul.drag-target {
	cursor: copy; /* normal */
}
body.dragging li.drag-entry {
	position: fixed;  /*placement controlled by JS*/
	color: Chartreuse;
	background-color: black; }
body.dragging ul.drag-target li:hover {
	border-top: 3px double white; }
pre {
	font-size: 1.2em;}
h1 {
	font-weight: bold;
	font-size: 2em;
	color: aqua;
	margin: 0.162em 7em 1em 0;
	padding: 0; }
size,
path,
filename {
	font-family: monospace;
	font-weight: bold;
	white-space: nowrap; }
size {
	color: white;
	margin-right: 1em;
	white-space: pre; }
path.replacement {
	border-left: 4px double red;
	border-right: 4px double red;
	background-color: #620000; }
path.replaced {
	border-right: 4px double red;
	color: DarkMagenta; }
path.destiny {
	border-right: 4px double cyan;
	color: cyan; }
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
arrow,
.helplink,
help span {
	font-size: 1.618em; }
arrow {
	line-height: 0.618em;
	vertical-align: -10.618%; }
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
mark, note, p strong {
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
fieldset > p {
	text-align: justify;
	margin: 0.618em 0.382em; }
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

ul {
	list-style: none; }

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
label.folder {
	display: block; }
#dirInput label.folder {
	text-indent: -0.618em; }
#dirInput fieldset {
	margin: 0.618em 0;
	border: none; }
#dirInput span {
	font-size: 1.618em;  }
label.folder input {
	width: calc(100% - 8.2em); }
fieldset {
	display: block;
	border: none;
	padding: 0;
	margin: 0;
	position: relative; }
div fieldset,
#options div,
fieldset fieldset,
section fieldset:first-child,
table {
	border: 1px solid yellow;
	width: 100%;
	margin: 0 0 2em 0; }
#filetime label,
#options > fieldset:last-child label,
#options div fieldset,
#options div fieldset:last-child label:last-child,
fieldset#Filter_Order label {
	display: block; }
#filetime label:last-of-type {
	margin-left: 4em; }
#sort_opts {
	position: relative;
	padding-right: 1.618em;
	padding-bottom: 0.2em; }

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
#verifier ul.drag_target {
	outline: 3px dotted white; }
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
#verifier div.expand > span {
	display: inline; }
#verifier .expand > ul {
	display: block; }

#verifier div {
	margin-left: 1em;
	border-left: 1px dotted; }
#verifier div.similar {
	display: inline-block;
	vertical-align: top; }
#verifier path.replaced + div.similar,
#verifier div.expand {
	display: block; }
#verifier div li:last-child path,
#verifier .replaced path {
	padding-bottom: 0.162em;
	border-bottom: 1px solid; }
.destiny {
	text-align: right; }

#verifier div.similar,
#verifier div.similar * {
	color: orange !important; }
#verifier mark {
	color: red;
	margin: 0 1em; }

#totals spec {
	margin: 0 1.618em; }
#totals desc {
	margin-right: 1em; }

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
	left: auto;
	right: 0px;
	top: 1.382em;
	bottom: auto;
	width: 17em;
	padding: .618em;
	border: 1px solid orange;  }
#options:hover,
#options help:hover {
	z-index: 10; }
#filterOrder help:hover {
	right: auto;
	left: 20%; }
/*
#dirInput help:hover {
	right: 0px;
	left: auto;
	bottom: auto; }
	*/
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
div > fieldset,
section fieldset:first-child,
#options div,
fieldset fieldset {
	display: inline-block;
	width: auto;
	padding-left: 7px;
	vertical-align: bottom; }

#options div fieldset {
	display: block;
	margin-bottom: 0.618em; }
div#options fieldset,
div#options div,
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
	DIR_SEP= is_MS_Windows ? "\\" : "/",
	transpositions={
		keys: "1!2@%6^7&8*=+/?[{}]|\\'\"",
		ctrl: "●¡©®°☺☻♪♫×☼≈±÷¿‘“”’¦¶πφ" };

var tabbedOut=false;


function enhanceKeybrd(event)  { // typically for American QWERTY keyboards
	//  characters not allowed in filenames:  \/?*":;<>|
	if (event.altKey)  return;
	const isPath=event.target.name.match( /dir|ext|file|path/ );
	//console.log(event.keyCode," - ",event.key);
	if (event.keyCode===9)  {tabbedOut=true;  return;}  else  tabbedOut=false;
	if (event.keyCode===13) {event.preventDefault();  return;}  // ENTER key
	function addText(txt)  {
		const curPos=event.target.selectionStart;
		event.target.value=event.target.value.substr(0,curPos)+txt+event.target.value.substr(event.target.selectionEnd||curPos);
		event.target.selectionStart=
		event.target.selectionEnd=curPos+txt.length;
		event.preventDefault();  }
	var p;
	if (event.ctrlKey  &&  (p=transpositions.keys.indexOf(event.key)) >= 0)  {
		addText(transpositions.ctrl[p]);
		return;  }
	switch (event.key)  {
  case '*':
		if (isPath
		&& (!event.target.name.match(/file/)
			||  event.target.selectionStart!==event.target.value.length
			||  event.target.value.substr(-1)!==DIR_SEP))
				event.preventDefault();
		return;

	case ':':
		if (isPath  &&  (isPath[0]==='ext'  ||  isPath[0]==='file'))
							event.preventDefault();
		return;

	case "/":
		if (isPath)  {
			if (isPath[0]==='ext')  event.preventDefault();
			else if (is_MS_Windows)  addText('\\');  }
		return;

	case "\\":
		if (isPath)  {
			if (isPath[0]==='ext')  event.preventDefault();
			else if (!is_MS_Windows)  addText('/');  }
		return;

	case "<":
	case ">":
		event.preventDefault();
		return;

	case ";": if (is_MS_Windows)  return;
	case "?":
	case '"':
	case "|":
		if (isPath)  event.preventDefault();
		return;  };  }


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


RegExp.escape=function (string) {
	// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
	return string && string.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}

// ===================================================

function align_archive_mode(event)  {
	if (event.target.closest('fieldset').id==="archive_mode"
	&&  event.target.type==='radio')  {
		if (event.target.value==='off')  return;
		const
			mode=document.querySelector('input[name="archive_mode"]:checked').value,
			others=document.querySelectorAll('input[archiverMode]');
		for (const inp of others)  {
			const chkd=(inp.getAttribute('archiverMode')===mode);
			inp.checked=chkd;
			inp.parentNode.classList.toggle('checked', chkd);  }  }
	else if (event.target.getAttribute('archiverInfluence')==='off')  {
		const off=document.querySelector('input[name="archive_mode"][value="off"]');
		off.checked=true;
		off.parentNode.classList.add('checked');
		off.parentNode.previousElementSibling.classList.remove('checked');  }  }

function align_time_inputs(inp)  {
	if (inp.type!=='radio')  return;
	const flag= inp.value!=='no-set';
	document.querySelector('[name="creationDateTime"]').disabled=flag;
	document.querySelector('[name="timezone"]').disabled=flag;  }

function sync_verified_form(event)  {
	const inp=document.querySelector('input[type="hidden"][name="'+event.target.name+'"]');
	if (inp)  inp.value=event.target.value;  }

// this is called when a directory name is (un)checked in the “verify” file-tree
function check_all_in_dir(event, e)  {
	event.stopPropagation();
	for (var i=1, inps=e.closest("li").getElementsByTagName('input'); i<inps.length; i++)  {
		if (inps[i].type==='checkbox')  inps[i].checked=e.checked;  }
	check_dir(event, e.closest('ul'), e.checked);  }

// this is called when an individual file is (un)checked in the “verify” file-tree
// the onchange event bubbles up to the UL
function check_dir(event, ul, chkd)  {
	event.stopPropagation();
	get_size_totals(event);
	try {
		if (chkd===false)  throw false;
		for (const inp of ul.getElementsByTagName('input'))  {
			if (inp.name=="")  continue;
			if (!inp.checked)  throw false;  }
		throw true;  }
	catch (b) {
		ul.parentNode.querySelector('input').checked=b;
		if (ul=ul.parentNode.closest('ul'))  check_dir(event, ul, b);  }  }

function get_size_totals(event)  {
	const div=document.querySelector('div#totals');
	if (div)  {
		const chkd=event.target.form.querySelectorAll('input[name]:checked');  //folder checkboxes don’t have names
		div.firstElementChild.lastChild.data=chkd.length;
		var total=0;
		for (const inp of chkd)  {total+=parseInt(inp.nextElementSibling.firstChild.data);}
		div.lastElementChild.lastChild.data=total.toLocaleString();  }  }


function drag_entry(event)  {
	if (event.target.nodeName==='INPUT'
	||  event.target.closest('span.expand')
	||  event.target.closest('span.collapse')
	||  event.detail>1
	||  event.button>0)  return;
	const
		ul=event.currentTarget,
		li=event.target.closest("li"),
		body=document.body;
	event.preventDefault();
	event.stopPropagation();
	if (!li)  return;
	body.classList.add('dragging');
	ul.classList.add('drag-target');
	li.classList.add('drag-entry');
	li.classList.remove('expanded');
	body.addEventListener('mousemove', catsEye);
	body.addEventListener('mouseup', drop);
	catsEye(event);
	function catsEye(event)  {  // onMouseMove
		if (!event.buttons)  {drop(null);  return;}
		li.style.top=event.clientY+"px";
		li.style.left=(event.clientX+10)+"px"; }
	function drop(event)  {  // onMouseUp
		body.removeEventListener('mousemove', catsEye);
		body.removeEventListener('mouseup', drop);
		body.classList.remove('dragging');
		ul.classList.remove('drag-target');
		li.classList.remove('drag-entry');
		const next_li=event.target.closest('li');
		if (event===null
		||  next_li===null
		||  event.target.closest('ul')!==ul)  return;
		ul.insertBefore(li, next_li);  }  }

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
	lbl.classList.toggle('disabled', flag);
	lbl.firstChild.disabled=flag;  }

function align_filterTables()  {
	for (const inp of document.getElementById('Filter_Order').elements)  {
		if (inp.checked)  {var fo=inp.value;  break;}  }
	const
		fit=document.getElementById('FilterIn'),
		fot=document.getElementById('FilterOut');
	switch (fo)  {
		case 'none':
			fit.disabled=true;  fit.classList.add('disabled');
			fot.disabled=true;  fot.classList.add('disabled');
		break;
		case 'in':
			fit.disabled=false;  fit.classList.remove('disabled');
			fot.disabled=true;   fot.classList.add('disabled');
			fot.parentNode.insertBefore(fit, fot);
		break;
		case 'out':
			fot.disabled=false;  fot.classList.remove('disabled');
			fit.disabled=true;   fit.classList.add('disabled');
			fit.parentNode.insertBefore(fot, fit);
		break;
		case 'in or out':
			fit.disabled=false;  fit.classList.remove('disabled');
			fot.disabled=false;  fot.classList.remove('disabled');
			fot.parentNode.insertBefore(fit, fot);
		break;
		case 'in,out':
			fit.disabled=false;  fit.classList.remove('disabled');
			fot.disabled=false;  fot.classList.remove('disabled');
			fot.parentNode.insertBefore(fit, fot);
		break;
		case 'out,in':
			fit.disabled=false;  fit.classList.remove('disabled');
			fot.disabled=false;  fot.classList.remove('disabled');
			fit.parentNode.insertBefore(fot, fit);
		break;
	}  }

function keep_help_visible(event)  {
	const pos=event.target.getBoundingClientRect();
	if (pos.left<0)  event.target.style.right=pos.left+"px";
	if (!event.target.regulated)  {
		event.target.addEventListener('mouseleave', event=>event.target.style.right="");
		event.target.regulated=true;  }  }
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

	process_common_inputs($¿keepCreationTime, $newCreationTime, $trackNumInc, $trackNumStart);  // ←values are returned

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


	$¿caseInsense= ($_POST['case_sense']==="no"  or  ($_POST['case_sense']==="auto"  and  MS_WINDOWS)) ?
						FNM_CASEFOLD : 0;  // this specific logic is used by Function filter() for POSIX Wildcards

	if ($¿caseInsense)  {
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

	$dir1=read_dir($_POST['dir_1'], $filters, $_POST['recursive']==='no', $_POST['sort']==='yes', $¿caseInsense, $_POST['comingle']==='yes');
	$dir2=read_dir($_POST['dir_2'], $filters, $_POST['recursive']==='no', $_POST['sort']==='yes', $¿caseInsense, $_POST['comingle']==='yes');
//	echo "<pre>",var_dump($dir1, $dir2),"</pre>";

	if ($_POST['archive_mode']==='on')  {
		$archiveDir=$_POST['archive_folder'] ? $_POST['archive_folder'] : $_POST['dir_1'];
		if (is_dir($archiveDir))  $archiveDir= read_archive($archiveDir, $filters, $¿caseInsense);
		else throw new bad_form_data("Bad Archive Directory — Directory not found: ".$d);  }
	else  $archiveDir=null;

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
					"<input type='hidden' name='verified[syncMethod]' value='{$_POST['sync_method']}'>\n",
					"<input type='hidden' name='preserveCreationTime' value='{$_POST['preserveCreationTime']}'>\n",
					"<input type='hidden' name='creationDateTime' value='{$_POST['creationDateTime']}'>\n",
					"<input type='hidden' name='timezone' value='{$_POST['timezone']}'>\n",
					"<input type='hidden' name='removeTrackNums' value='{$_POST['removeTrackNums']}'>\n",
					"<input type='hidden' name='addTrackNums' value='{$_POST['addTrackNums']}'>\n",
					"<input type='hidden' name='trackNumInc' value='{$_POST['trackNumInc']}'>\n",
					"<input type='hidden' name='trackNumStart' value='{$_POST['trackNumStart']}'>\n",
					"<input type='hidden' name='comingle' value='{$_POST['comingle']}'>\n",
					"<input type='hidden' name='trash' value='{$_POST['trash']}'>\n",
					"<input type='hidden' name='show_sizes' value='{$_POST['show_sizes']}'>\n";
	break;
	default: throw new bad_form_data("internal error: Bad Submit Method");  }

	$filecount=0;

	switch ($_POST['sync_method'])  {
	case "bi-directional":
		echo "<h1>$h1 <path>",htmlentities($_POST['dir_1']),"</path> to <path>",htmlentities($_POST['dir_2']),"</path></h1>\n";
		$uniq=find_unique($dir1, $dir2, $archiveDir, $_POST['check_ages']==='no');
		$filecount+=count($uniq['Paths']);
		if ($_POST['find_similar']==='yes')  find_misplaced($uniq, $dir2);
		if ($_POST['submit']==="sync ’em")
			syncdir($_POST['dir_1'], $_POST['dir_2'], $uniq,
							$¿keepCreationTime, $newCreationTime,
							$_POST['removeTrackNums']==='yes',
							$_POST['addTrackNums']==='yes',
							$trackNumInc, $trackNumStart);
		$tree=build_dir_tree($_POST['dir_1'], $uniq['Paths']);
		show_dir($tree, $uniq,
						 $_POST['submit']==='verify first' ? 'in_dir1' : FALSE,
						 $_POST['show_sizes']==='yes',
						 $_POST['comingle']==='yes');
		//echo "<pre>",var_dump($tree, $uniq),"</pre>";
	case "uni-directional":
		echo "<h1>$h1 <path>",htmlentities($_POST['dir_2']),"</path> to <path>",htmlentities($_POST['dir_1']),"</path></h1>\n";
		$uniq=find_unique($dir2, $dir1, $archiveDir, $_POST['check_ages']==='no');
		$filecount+=count($uniq['Paths']);
		if ($_POST['find_similar']==='yes')  find_misplaced($uniq, $dir1);
		if ($_POST['submit']==="sync ’em")
			syncdir($_POST['dir_2'], $_POST['dir_1'], $uniq,
							$¿keepCreationTime, $newCreationTime,
							$_POST['removeTrackNums']==='yes',
							$_POST['addTrackNums']==='yes',
							$trackNumInc, $trackNumStart);
		$tree=build_dir_tree($_POST['dir_2'], $uniq['Paths']);
		show_dir($tree, $uniq,
						 $_POST['submit']==='verify first' ? 'in_dir2' : FALSE,
						 $_POST['show_sizes']==='yes',
						 $_POST['comingle']==='yes');
		//echo "<pre>",var_dump($tree, $uniq),"</pre>";
	break;
	default: throw new bad_form_data("internal error: Bad Sync Method");  }

	switch ($_POST['submit'])  {
	case "verify first":
		if ($filecount>0)  {
			if ($_POST['show_sizes']==='yes')
				echo "\n<div id='totals'><spec><desc>total selected files:</desc>0</spec><spec><desc>total selected bytes:</desc>0</spec></div>";
			echo "\n<input type='submit' name='submit' value='verified'>\n";  }
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

<h1>Create, Copy, Backup, Synchronize, &amp; Archive Directory Folders</h1>
<?php echo $errorHTML; ?>
<aside>Click<span> or hover</span> on <span class='helplink'>☻</span>s for help</aside>
<form action="syncdir.php" method='post' onkeydown='enhanceKeybrd(event);'>
<div id='options' onchange='align_archive_mode(event)'>
	<fieldset id='dirInput'>
	<label class='folder'>folder1 path <input type='text' name='dir_1' value='<?php echo htmlentities($_POST['dir_1']);?>'></label>
	<fieldset>
		Synchronize files:&nbsp;
		<label title="bi-directional">between both <arrow>&#x21F3;</arrow> folders<arrow>&#x21D5;</arrow><input type='radio' name='sync_method' value="bi-directional"
			archiverInfluence='off' archiverMode='off' <?php
			if ($_POST['sync_method']=="bi-directional")  echo CHKD; ?>></label>
		<label title="uni-directional"><input type='radio' name='sync_method' value="uni-directional"
			archiverInfluence='on' archiverMode='on' <?php
			if ($_POST['sync_method']!="bi-directional")  echo CHKD; ?>><arrow>&#x21D1;</arrow>from folder2 to <arrow>&#x21E7;</arrow> folder1</label>
	</fieldset>
	<label class='folder'>folder2 path <input type='text' name='dir_2' value='<?php echo htmlentities($_POST['dir_2']);?>'></label>
	<help><span>☻</span><p>It is best to define paths from the root <?PHP echo MS_WINDOWS ? "drive (or user," : "user (or drive,"; ?>
	depending on <abbr title='Operating System'>OS</abbr>)
	and not to rely on <abbr>PHP</abbr> relative or “include” paths.</p></help>
	</fieldset>
	<fieldset id='archive_mode'><legend>Archive Mode</legend>
	<p>Archive mode is primarily intended to help you keep backups of completed projects
	as well as of files that do not change once created:
	pictures, music, etc.&nbsp;
	<strong>Archive mode is only uni-directional (from folder2 <arrow>&#x21DB</arrow> to folder1).</strong>&nbsp;
	It ignores files in the source directory/folder (and optionally its subfolders recursively)
	that are found to match another file <em>anywhere</em> in the archive directory/folder and recursively its subfolders.&nbsp;
	However, archive files and subfolders are still subject to all “filters”,
	and therefore may be either filtered out or not filtered in of consideration, depending on your “filter” options below.&nbsp;
	Contrast “matching” files with “similar” files:
	“matching” files have both the same name and size, whereas
	“similar” files have the same name and/or the same size.&nbsp;
	In archive mode, files found in the archive folder do <em>not</em> have their
	file update times compared with the source files’ update times.&nbsp;
	The archive folder may be any directory/folder;
	if you leave it blank, the destination folder (folder1) becomes the archive folder by default.&nbsp;
	As usual, files in the source folder that are found in the destination folder
	with a matching file-path may also be ignored depending on other options’ settings.</p>
	<label><input type='radio' name='archive_mode' value='on' <?php
		if ($_POST['archive_mode']=="on")  echo CHKD; ?>>on</label>
	<label><input type='radio' name='archive_mode' value='off' <?php
		if ($_POST['archive_mode']!="on")  echo CHKD; ?>>off</label>
	<label class='folder'>archive folder path <input type='text' name='archive_folder' value='<?php echo htmlentities($_POST['archive_folder']);?>'</label>
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
	<fieldset onchange='sync_verified_form(event)'><legend>Show file sizes?</legend>
	<label><input type='radio' name='show_sizes' value='yes' <?php
		if ($_POST['show_sizes']!=="no")  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='show_sizes' value='no' <?php
		if ($_POST['show_sizes']=="no")  echo CHKD; ?>>No</label>
	</fieldset>
	<fieldset id='filetime' onchange='sync_verified_form(event); align_time_inputs(event.target);'>
	<legend>Preserve original file “last-modified” time for copied file?</legend>
	<label><input type='radio' name='preserveCreationTime' value='yes' <?php
		if (!str_starts_with($_POST['preserveCreationTime'], "no"))  echo CHKD; ?>>Yes</label>
	<label><input type='radio' name='preserveCreationTime' value='no-current' <?php
		if ($_POST['preserveCreationTime']=="no-current")  echo CHKD; ?>>No - use current time</label>
	<label><input type='radio' name='preserveCreationTime' value='no-set' <?php
		if ($_POST['preserveCreationTime']=="no-set")  echo CHKD; ?>>No - use this time:
		<input type='datetime-local' name='creationDateTime' value='<?php echo $_POST["creationDateTime"]; ?>'></label>
	<label>in this time zone:<input type='text' list='tzones' name='timezone' value='<?php
		if ($_POST['timezone'])  echo $_POST['timezone'];
		else  echo date_default_timezone_get();	?>'>
		<a class='helplink' href="https://www.php.net/manual/en/timezones.php" target='php'>☻</a></label>
	<datalist id='tzones'>
		<option value='<?php echo date_default_timezone_get()?>'></option>
		<option value='America/Denver'>America/Albuquerque</option>
		<option value='America/New_York'>America/Atlanta</option>
		<option value='America/Chicago'></option>
		<option value='America/Denver'></option>
		<option value='America/Detroit'></option>
		<option value='America/Los_Angeles'></option>
		<option value='America/Chicago'>America/Minnesota</option>
		<option value='America/Denver'>America/Montana</option>
		<option value='America/New_York'></option>
		<option value='America/Phoenix'></option>
		<option value='America/Puerto_Rico'></option>
		<option value='America/Los_Angeles'>America/Seattle</option>
	</datalist>
	</fieldset>
	<div id='sort_opts'>
		<fieldset><legend>Sort the copied files?</legend>
		<label><input type='radio' name='sort' value='yes' <?php
			if ($_POST['sort']!="no")  echo CHKD; ?>>Yes</label>
		<label><input type='radio' name='sort' value='no' <?php
			if ($_POST['sort']=="no")  echo CHKD; ?>>No</label>
		</fieldset>
		<label><input type='checkbox' name='comingle' value='yes' onchange='sync_verified_form(event)'<?php
			if ($_POST['comingle']=="yes")  echo CHKD; ?>>Comingle folders with files?</label>
		<help onmouseenter='keep_help_visible(event)'><p>These features are for car radios and such
		that play songs in the order they were physically copied to a <abbr>USB</abbr> thumb-drive.&nbsp;
		If you choose to “verify first” before copying,
		you may further hand-sort the order files are <strong><em>physically copied</em></strong>
		to the destination drive using the mouse to drag-&amp;-drop them.&nbsp;
		You may <em>not</em> move them to another folder, only reorder them within their respective folders.&nbsp;
		For Windows® systems, this will not affect the order that files are displayed to the user;
		however it may for Linux users.</p></help>
	</div>
	<div>
		<fieldset onchange='sync_verified_form(event)'><legend>Remove existing track numbers?</legend>
		<label><input type='radio' name='removeTrackNums' value='yes' <?php
			if ($_POST['removeTrackNums']=="yes")  echo CHKD; ?>>Yes</label>
		<label><input type='radio' name='removeTrackNums' value='no' <?php
			if ($_POST['removeTrackNums']!="yes")  echo CHKD; ?>>No</label>
		</fieldset>
		<fieldset onchange='sync_verified_form(event)'><legend>Add track numbers?</legend>
		<label><input type='radio' name='addTrackNums' value='yes' <?php
			if ($_POST['addTrackNums']=="yes")  echo CHKD; ?>>Yes: </label>
		<label>increment: <input type='number' name='trackNumInc' min='1' max='100' step='1' size='7' value='<?php
			echo  is_numeric($_POST['trackNumInc']) ? $_POST['trackNumInc'] : "1"; ?>'></label>
		<label>begin at: <input type='number' name='trackNumStart' min='0' step='1' size='7' value='<?php
			echo  is_numeric($_POST['trackNumStart']) ? $_POST['trackNumStart'] : "1"; ?>'></label>
		<label><input type='radio' name='addTrackNums' value='no' <?php
			if ($_POST['addTrackNums']!="yes")  echo CHKD; ?>>No</label>
		</fieldset>
	</div>
	<fieldset onchange='sync_verified_form(event)'><legend>Use trash folder for overwritten files?</legend>
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
(such as Microsoft Windows’® <abbr>NT</abbr> File System (<abbr>NTFS</abbr>) and <acronym>exFAT</acronym> formats)
for use with International characters when case-<em>in</em>sensitive.&nbsp;
<acronym>FAT</acronym>, <acronym>FAT16</acronym>, &amp; <acronym>FAT32</acronym> file systems
(typically found on <abbr>USB</abbr> “thumb drives”) often use the
Windows’® “<abbr title='Original Equipment Manufacturer'>OEM</abbr>” character-set
which typically differs from <abbr>UTF-8</abbr>.&nbsp;
<?php else: ?>
<p>This installation of <abbr>PHP</abbr> does not support International characters,
so files and directory folder names that have them
can not be properly compared in a case-<em>in</em>sensitive way.&nbsp;
Also, <acronym>FAT</acronym>, <acronym>FAT16</acronym>, &amp; <acronym>FAT32</acronym> file systems
(typically found on <abbr>USB</abbr> “thumb drives”) often use the
Windows® “<abbr title='Original Equipment Manufacturer'>OEM</abbr>” character-set encoding
which typically differs from Microsoft Windows’® <abbr>NT</abbr> File System (<abbr>NTFS</abbr>)
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
	<legend onclick='this.parentNode.classList.toggle("open");'
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
		and  FALSE !== ($k=@array_search(DIRECTORY_SEPARATOR, $_POST['filter_in']['files'], true)))  {
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
		$_POST['filter_out']['files']=array_diff($_POST['filter_out']['files'], array("Thumbs.db", "desktop.ini", "trash".DIRECTORY_SEPARATOR));
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
if (document.getElementById('recursive').elements[1].checked)  disable_AllFolders(true);

for (const inp of document.querySelectorAll('input[type="radio"]'))  {
	inp.parentNode.classList.toggle('checked', inp.checked);  }

const comingleInp= document.querySelector('input[name="comingle"]');
comingleInp.parentNode.classList.toggle('checked', comingleInp.checked);

align_time_inputs(document.querySelector('input[name="preserveCreationTime"]:checked'));
align_filterTables();

document.body.addEventListener('change', function(event)  {
	if (event.target===comingleInp)  {
		comingleInp.parentNode.classList.toggle('checked', comingleInp.checked);
		return;  }
	if (event.target.type!=='radio')  return;
	for (const inp of event.target.closest('fieldset').elements)  {
		if (inp.type==='radio')  inp.parentNode.classList.toggle('checked', inp.checked);  }  });
</script>
</body>
</html>

<?php  exit;

Function process_common_inputs(&$¿keepCreationTime, &$newCreationTime, &$trackNumInc, &$trackNumStart)  {
  if ($_POST['timezone']  and  !date_default_timezone_set($_POST['timezone']))
		throw new bad_form_data("Invalid time-zone: ".$_POST['timezone']);

	$¿keepCreationTime= ($_POST['preserveCreationTime']==='yes');
	if ($_POST['preserveCreationTime']==='no-set'  and  $_POST['creationDateTime'])  {
		// see:  https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/datetime-local
		switch(strlen($_POST['creationDateTime']))  {
			case 16: $_POST['creationDateTime']= $_POST['creationDateTime'].":00";
			case 19: $_POST['creationDateTime']= $_POST['creationDateTime'].".00";  }
		$newCreationTime= date_create_immutable_from_format(
			"Y-m-d H:i:s.v", str_replace("T", " ", $_POST['creationDateTime']) );
		if ($newCreationTime)  $newCreationTime=$newCreationTime->getTimestamp();
		else  throw new bad_form_data('Invalid file creation time: '.$_POST['creationDateTime']);  }
	else  $newCreationTime=null;

	$trackNumInc=  max(1, min(100, round(floatval($_POST['trackNumInc']))));
	$trackNumStart=max(0, round(floatval($_POST['trackNumStart'])));  }


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

Function array_keys_deep(&$A)  {
	$keys=array();
	array_walk_recursive($A, function($v, $k) use (&$keys)  {array_push($keys, $k);});
	return $keys;  }

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

Function check_array(&$a, $pattern=FALSE, $err_msg="")  { if ($a==NULL)  return array();
	if (!is_array($a))  throw new bad_form_data("internal error: Bad Filter Info Structure");
	$a=array_filter($a, "strlen");
//	if ($pattern  and  count(preg_grep($pattern, $a, PREG_GREP_INVERT))>0)  throw new bad_form_data($err_msg);
	if ($pattern
	and	$temp=preg_grep($pattern, $a, PREG_GREP_INVERT)
	and is_array($temp)
	and count($temp)>0)  throw new bad_form_data($err_msg);
	return ($a);  }


Function read_dir($dir, &$filters, $¿shallow=false, $¿sort=true, $¿caseInsense=true, $¿comingleFolders=true)  {
	if (substr($dir, -1, 1)!==DIRECTORY_SEPARATOR)  $dir.=DIRECTORY_SEPARATOR;
	$files = array('.'=>$dir, 'Names'=>array(), '?names'=>array(), 'Paths'=>array(), '~subdirs'=>array());
	if (!$¿comingleFolders)
		$folders = array('Names'=>array(), '?names'=>array(), 'Paths'=>array(), '~subdirs'=>array());
	$d = dir($dir);
	while (false !== ($entry = $d->read()))  {
		if ($entry==='.'  or  $entry==='..'  or  $entry===""
		or  ( ($¿isDir=is_dir($dir.$entry))  and  $¿shallow )
		or  filter_file($entry, $dir.$entry, $filters, $¿caseInsense, $srch_val))  continue;
		if ($comingleFolders  or  (!$¿isDir))  {                      // ↑↑↑ \\ $srch_val is returned
			$files['Names'][] = $entry;
			$files['?names'][] = $srch_val;  // lowercase if case-insensitive
			$files['Paths'][] = $dir.$entry;
			$files['~subdirs'][]= ($¿isDir ? read_dir($dir.$entry, $filters, $¿shallow, $¿sort, $¿caseInsense, $¿comingleFolders) : null);  }
		else  {
			$folders['Names'][] = $entry;
			$folders['?names'][] = $srch_val;  // lowercase if case-insensitive
			$folders['Paths'][] = $dir.$entry;
			$folders['~subdirs'][]= read_dir($dir.$entry, $filters, $¿shallow, $¿sort, $¿caseInsense, $¿comingleFolders);  }  }
	$d->close();
	if ($¿sort)  {
		if (count($files['Names']))
			array_multisort(
				$files['Names'],
				SORT_ASC,
				SORT_NATURAL | ($¿caseInsense ? SORT_FLAG_CASE : 0),
				$files['?names'], $files['Paths'], $files['~subdirs']);
		if ((!$¿comingleFolders)  and  count($folders['Names']))
			array_multisort(
				$folders['Names'],
				SORT_ASC,
				SORT_NATURAL | ($¿caseInsense ? SORT_FLAG_CASE : 0),
				$folders['?names'], $folders['Paths'], $folders['~subdirs']);  }
	if ((!$¿comingleFolders)  and  count($folders['Names']))
		$files= (FOLDERS_FIRST ? array_merge_recursive($folders, $files) : array_merge_recursive($files, $folders));
	return $files;  }


Function read_archive($dir, &$filters, $¿caseInsense, &$filelist=array(), &$filepaths=array(), $¿recursing=false)  {
	if (substr($dir, -1, 1)!==DIRECTORY_SEPARATOR)  $dir.=DIRECTORY_SEPARATOR;
	$d = dir($dir);
	while (false !== ($entry = $d->read()))  {
		if ($entry==='.'  or  $entry==='..'  or  $entry===""
		or  filter_file($entry, null, $filters, $¿caseInsense, $srch_val))  continue;
		if (is_dir($dir.$entry))                               // ↑↑↑ \\ value is returned
			read_archive($dir.$entry, $filters, $¿caseInsense, $filelist, $filepaths, true);
		else {
			$filelist[] = $srch_val;  // lowercase if case-insensitive
			$filepaths[] = $dir.$entry;  }  }
	$d->close();
	if ($¿recursing  or  count($filelist)<1)
		return null;
	else
		return array('?names'=>$filelist, 'Paths'=>$filepaths);  }

// find directory entries that are in $dir1 that are not anywhere in the $archiveDir or recursively its subfolders;
// and not in $dir2 on the same path
// (make sure they are the same file - length -)
// or possibly if the age of a file in $dir1 is - younger - than the same file in $dir2
Function find_unique(&$dir1, &$dir2, $archiveDir=null, $¿ignore_age=true, $subpath=DIRECTORY_SEPARATOR)  {
	// NOTE: filesize() is accurate up to 2GB on 32bit systems, 4GB on 64bit systems; but still works for comparisons up to 4GB.
	$unique=array('Names'=>array(), '?names'=>array(), 'sizes'=>array(), 'Paths'=>array());
	if (is_array($dir1['?names']))  foreach ($dir1['?names'] as $k => $filename)  {
		if ($subdir1=$dir1['~subdirs'][$k])  {
			$subdir2= (is_array($dir2['?names'])
								 and  is_numeric($k2=(array_search($filename, $dir2['?names'], true)))
								 and  $dir2['~subdirs'][$k2])  ?
					$dir2['~subdirs'][$k2]
				: array();  //we need to gather filesizes as well as merge, so we pass a dummy
			$unique=array_merge_recursive( $unique,
					find_unique($subdir1, $subdir2, $archiveDir, $¿ignore_age, $subpath.$dir1['Names'][$k].DIRECTORY_SEPARATOR) );
			continue;  }
		$path1=$dir1['.'].$dir1['Names'][$k];
		$k2=false;
		if ( ( is_array($dir2['?names'])
			and  is_numeric($k2=array_search($filename, $dir2['?names'], true))  /* could be false or null or 0 */
			and  $dir2['~subdirs'][$k2]==null
			and  filesize($path1)===filesize($path2 = $dir2['.'].$dir2['Names'][$k2])
			and  ($¿ignore_age
						or  filemtime($path1)<=filemtime($path2)) )
		or  (is_array($archiveDir)  and  in_archiveDir($filename, $path1, $archiveDir)))
			continue;
		$unique['Names'][]=$dir1['Names'][$k];
		$unique['?names'][]=$filename;
		$unique['sizes'][]=filesize($path1);
		$unique['Paths'][]=$path1;
		$unique['SubPaths'][]=$subpath;
		$unique['replaced'][]= is_numeric($k2);  }
	return $unique;  }

Function in_archiveDir($filename, $path, $archiveDir)  {
	if (count($keys=array_keys($archiveDir['?names'], $filename, true)))
		foreach ($keys as $k)  {
			if (filesize($path)===filesize($archiveDir['Paths'][$k]))  return true;  }
	return false;  }


Function find_misplaced(&$unique, &$dir, $subpath=DIRECTORY_SEPARATOR)  {
	if (!is_array($unique['similars']))  $unique['similars']=array();
	if (is_array($dir['Names']))
		foreach ($dir['Names'] as $dk => $fileName)  {
			if ($dir['~subdirs'][$dk])  {
				find_misplaced($unique, $dir['~subdirs'][$dk], $subpath.$fileName.DIRECTORY_SEPARATOR);
				continue;  }
			$path=$dir['.'].$fileName;
			if (count($keys=array_keys($unique['?names'], $dir['?names'][$dk])))
				foreach ($keys as $uk)  {
					if ($subpath===$unique['SubPaths'][$uk])  continue;
					if (!is_array($unique['similars'][$uk]))  $unique['similars'][$uk]=array();
					if (!in_array($path, $unique['similars'][$uk]))  $unique['similars'][$uk][]=$path;  }
			if (count($keys=array_keys($unique['sizes'], filesize($path))))
				foreach ($keys as $uk)  {
					if ($subpath===$unique['SubPaths'][$uk])  continue;
					if (!is_array($unique['similars'][$uk]))  $unique['similars'][$uk]=array();
					if (!in_array($path, $unique['similars'][$uk]))  $unique['similars'][$uk][]=$path;  }  }  }


Function filter_file($filename, $path, &$filters, $¿caseInsense, &$srch_val)  {
	//return TRUE if file is to be - ignored -
	//                                   ↓ strtolower, or mb_strtolower when supported
	$srch_val= ($¿caseInsense ?  uncase($filename) : $filename);
	if ($¿caseInsense)  $path=uncase($path);
	switch ($_POST['filter_order'])  {
		case "none":    return false;
		case "in":      return filter($srch_val, $path, $filters['in'],  $¿caseInsense, false);
		case "out":     return filter($srch_val, $path, $filters['out'], $¿caseInsense,  true);
		case "in,out":  return filter($srch_val, $path, $filters['in'],  $¿caseInsense, false) or filter($srch_val, $path, $filters['out'], $¿caseInsense,  true);
		case "out,in":  return filter($srch_val, $path, $filters['out'], $¿caseInsense,  true) or filter($srch_val, $path, $filters['in'],  $¿caseInsense, false);
		case "in or out":
			return filter($srch_val, $path, $filters['in'], $¿caseInsense, true) ? false : filter($srch_val, $path, $filters['out'], $¿caseInsense, true);  }  }

Function filter($filename, $path, &$filter, $¿caseInsense, $logic_bool)  {
	//return TRUE if file is to be - ignored - EXCEPT “filter in or out” returns true if the file is filtered -in-
	if (($path  and  ($isdir=is_dir($path))  and  $filter['pass_all_folders'])
	or  in_array($filename, $filter['files'])
	or  ($path  and  in_array($path, $filter['paths']))
	or  in_array($name= ($isdir ? $filename.DIRECTORY_SEPARATOR : DIRECTORY_SEPARATOR.$filename), $filter['files']))
		return $logic_bool;
	if ($path  and  $isdir)  foreach ($filter['super-folders'] as $folder)  {
		if (str_contains($path, $folder))  return $logic_bool;  }
	foreach ($filter['exts'] as $ext)  {
		if ($ext===substr($filename, -strlen($ext)))  return $logic_bool;  }
	foreach ($filter['regex'] as $pcre)  {
		if (@preg_match($pcre, $name)
		or  ($path  and  @preg_match($pcre, $path)))  return $logic_bool;  }
	foreach ($filter['POSIX_wildcards'] as $wc)  {
		if (@fnmatch($wc, $name, $¿caseInsense)
		or  @fnmatch($wc, $path, $¿caseInsense))  return $logic_bool;  }
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

Function show_dir(&$tree, &$files, $verify, $show_size, $comingle=false, $expander=EXPANDER, $tabs="", $size=0)  {
	if (count($tree)==0)  {echo "<h2>—none—</h2>\n";  return;}
	$html="";  ksort($tree, SORT_STRING);
	echo $tabs,"<ul";
	if ($verify)  echo " onchange='check_dir(event, this)' onmousedown='drag_entry(event)'";
	echo ">\n";
	foreach ($tree as $path => $k)  {
		if (is_array($k))  {  // $path is a sub-directory in this case
			ob_start();
			$dirSize=show_dir($k, $files, $verify, $show_size, $comingle, $expander, $tabs."\t");
			$size+=$dirSize;
			$dirHTML=ob_get_clean();
			echo $tabs, '<li class="expand">',$expander;
			if ($verify)  echo '<label><input type="checkbox" onchange="check_all_in_dir(event, this)">';
			if ($show_size)  echo '<size>', sprintf('%10d', $dirSize), '</size>';
			echo '<path>', htmlentities($path),DIRECTORY_SEPARATOR, '</path>';
			if ($verify)  echo '</label>';
			echo "\n", $dirHTML, $tabs,"</li>\n";
			continue;  }
		$html.= $tabs . '<li>';
		if ($verify)  $html.= '<label><input type="checkbox" name="verified[' . $verify . '][]" value="' . htmlentities($files['Paths'][$k]) . '">';
		if ($show_size)  $html.='<size>'.sprintf('%10d', $files['sizes'][$k]).'</size>';
		$size+=$files['sizes'][$k];
		$class="";
		if ($files['replaced'][$k]  AND  !is_string($files['destinations'][$k]))  $class.='replacement ';
		if (substr($path, 0,1)===chr(24))  $class.='failed-copy';
		if ($class)  $class=' class="'.$class.'"';
		$html.= '<path' . $class . '>' . htmlentities($path) . '</path>';  // $files['Names'][$k]
		if ($verify)  $html.= '</label>';
		if (is_string($files['destinations'][$k]))
			$html.= "\n".$tabs.'<div class="destiny"><path class="destiny'. ($files['replaced'][$k] ? ' replacement' : "") .'">' . htmlentities($files['destinations'][$k]) . '</path></div>';
		if (is_string($files['replaced'][$k]))
			$html.= "\n".$tabs.'<div class="replaced"><mark>&rArr;</mark><path'.(substr($files['replaced'][$k], 0,1)===chr(24) ? ' class="failed-copy"' : "").'>' . htmlentities($files['replaced'][$k]) . '</path></div>';
		if (isset($files['similars'])  and  $files['similars'][$k])  {
			$html.= "\n".$tabs.'<div class="similar">'.EXPANDER."Similar files found:\n <ul>\n";
			foreach ($files['similars'][$k] as $spath)  {$html.= ' <li><path>'.htmlentities($spath)."</path></li>\n";}
			$html.= $tabs." </ul></div>";  }
		$html.= "</li>\n";
		if ($comingle)  {echo $html;  $html="";}  }
	if (!$comingle)  echo $html;
	echo $tabs,"</ul>\n";
	return $size;  }


Function syncdir($src_dir, $dest_dir, &$uniq, $¿keepOrgCreationTime, $newCreationTime,
								 $¿removeTrackNums, $¿addTrackNums, $trackNumInc=1, $trackNumStart=1)  {
	$sd_len=strlen($src_dir);
	$uniq['destinations']=array();
	$uniq['replaced']=array();
	$i=$trackNumStart-$trackNumInc;
	$c=$trackNumStart+count($uniq['Paths'])*$trackNumInc;
	$trackDigits= ($c<10) ? 1 : (($c<100) ? 2 : (($c<1000) ? 3 : (($c<10000) ? 4 : 5)));
	foreach ($uniq['Paths'] as $k => &$path)  {
		$file=basename($path);  $dest_path=dirname($path).DIRECTORY_SEPARATOR;
		if (substr($dest_path, 0, $sd_len)!==$src_dir)  throw new bad_form_data('internal error — Verified Pathname does not match Source Directory');
		$subpath=substr($dest_path, $sd_len);
		$dest_path=$dest_dir.$subpath;
		$¿adjusted=adjustTrackNum($file, $¿removeTrackNums, $¿addTrackNums, $i+=$trackNumInc, $trackDigits);
		$dest=$dest_path.$file;
		$uniq['replaced'][$k]=FALSE;
		if (!is_dir($dest_path))  {mkdir($dest_path, 0777, true);  chmod($dest_path, 0777);}
		else
		if (is_file($dest))  $uniq['replaced'][$k]=trash($file, $subpath, $dest_dir);
		// note if copy fails, any matching file at the destination was still trashed!
		if (copy($path, $dest))  {
			if ($¿keepOrgCreationTime)  touch($dest, filemtime($path));
			else if ($newCreationTime)  touch($dest, $newCreationTime);
			$uniq['destinations'][$k]=($¿adjusted ? $file : FALSE);  }
		else  $path=chr(24).$path;  }  //  ASCII CAN  “cancel”
	return $uniq;  }

// note this scheme will/may not work well with subfolders!
Function adjustTrackNum(&$file, $¿remove, $¿add, $i, $trackDigits)  {
	//  the ↓ space following a track number digit must be present if not followed by . - )
	//  "409 title_and_performer.wav"
	//  "409.02 title_and_performer.wav"
	//  "409-02 title_and_performer.wav"
	//  the  ↓  space following a track number (or disk/track combo) is optional if the number is followed by . - )
	//  "409. title_and_performer.wav"
	//  "409- title_and_performer.wav"
	//  "409 . title_and_performer.wav"
	//  "409 - title_and_performer.wav"
	//  "409) title_and_performer.wav"
	//  "409.02) title_and_performer.wav"
	//  "409-02) title_and_performer.wav"
	//  "(409) title_and_performer.wav"
	//  "(409.02) title_and_performer.wav"
	//  "(409-02) title_and_performer.wav"
	$¿adjusted=FALSE;
	if ($¿remove  AND  preg_match('/^(?:\d+\s*[-.]\s*|\(?\d+(?:[-.]\d+)?\)\s*|\d+[-.]\d+[-.]\s*|\d+[-.]\d+\s+|\d+\s+)/', $file, $matches))  {
		$¿adjusted=TRUE;
		$file=substr($file, strlen($matches[0]));  }
	if ($¿add)  {
		$¿adjusted=TRUE;
		$file=str_pad($i, $trackDigits, "0", STR_PAD_LEFT).". ".$file;  }
	return $¿adjusted;  }

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

	process_common_inputs($¿keepCreationTime, $newCreationTime, $trackNumInc, $trackNumStart);  // ←values are returned

	echo "<div id='verified'><h1>Verified Files Synchronized ",
		($_POST['verified']['syncMethod']==="bi-directional") ? "between" : "from",
		"<path>",htmlentities($_POST['verified']['dir2']),"</path>",
		($_POST['verified']['syncMethod']==="bi-directional") ? "and" : "to",
		"<path>",htmlentities($_POST['verified']['dir1']),"</path></h1>\n";

	if (count($_POST['verified']['in_dir1'])>0)  {
		$uniq=array('Paths'=> &$_POST['verified']['in_dir1']);
		syncdir($_POST['verified']['dir1'], $_POST['verified']['dir2'], $uniq,
						$¿keepCreationTime, $newCreationTime,
						$_POST['removeTrackNums']==='yes',
						$_POST['addTrackNums']==='yes',
						$trackNumInc, $trackNumStart);
		$tree=build_dir_tree($_POST['verified']['dir1'], $uniq['Paths']);
		show_dir($tree, $uniq, FALSE, FALSE, $_POST['comingle']==='yes', "");  }

	if (count($_POST['verified']['in_dir2'])>0)  {
		$uniq=array('Paths'=> &$_POST['verified']['in_dir2']);
		syncdir($_POST['verified']['dir2'], $_POST['verified']['dir1'], $uniq,
						$¿keepCreationTime, $newCreationTime,
						$_POST['removeTrackNums']==='yes',
						$_POST['addTrackNums']==='yes',
						$trackNumInc, $trackNumStart);
		$tree=build_dir_tree($_POST['verified']['dir2'], $uniq['Paths']);
		show_dir($tree, $uniq, FALSE, FALSE, $_POST['comingle']==='yes', "");  }

	echo "</div>";

} catch (bad_form_data $e)  {echo "<h5>", $e->getMessage(), "</h5>\nTry the browser’s “back” button.";}
?>
</body>
</html>
