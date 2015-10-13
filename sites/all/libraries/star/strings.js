// strings.js:
// A variety of handy functions for working with strings.

function write(str)
{
  document.write(str);
}

function writeln(str)
{
  write(str + "\n");
}

function writebr(str)
{
  writeln(str + "<br>");
}

// returns n left-most characters from str:
function left(str, n)
{
  return str.substr(0, n);
}

// returns n right-most characters from str:
function right(str, n)
{
  return str.substr(str.length - n, n);
}

// functions to add characters to start of a string until desired length
// reached:
function padLeft(value, width, padString)
{
  // convert to a string:
  var result = String(value);
  // add pad characters until desired width is reached:
  while (result.length < width) {
    result = padString + result;
  }
  return right(result, width);
}

// functions to add characters to end of a string until desired length reached:
function padRight(value, width, padString)
{
  // convert to a string:
  var result = String(value);
  // add pad characters until desired width is reached:
  while (result.length < width) {
    result = result + padString;
  }
  return left(result, width);
}

// ///////////////////////////////////////////////////////////////////////////////////////
// Basic string and character functions:

// returns true if str is a string with length > 0
function isValidString(str)
{
  return (typeof str == 'string') && (str.length > 0);
}

// returns true if character ch is in string str:
function isIn(ch, str)
{
  return str.indexOf(ch) >= 0;
}

// ///////////////////////////////////////////////////////////////////////////////////////
// check ASCII codes:

var ASCII_A = 65;
var ASCII_Z = 90;
var ASCII_a = 97;
var ASCII_z = 122;
var ASCII_0 = 48;
var ASCII_9 = 57;

function isUpperCaseLetterCode(code)
{
  return (code >= ASCII_A) && (code <= ASCII_Z);
}

function isLowerCaseLetterCode(code)
{
  return (code >= ASCII_a) && (code <= ASCII_z);
}

function isLetterCode(code)
{
  return isUpperCaseLetterCode(code) || isLowerCaseLetterCode(code);
}

function isDigitCode(code)
{
  return (code >= ASCII_0) && (code <= ASCII_9);
}

function isAlphanumericCode(code)
{
  return isDigitCode(code) || isLetterCode(code);
}

// ///////////////////////////////////////////////////////////////////////////////////////
// check ASCII characters:
function isUpperCaseLetter(ch)
{
  return isUpperCaseLetterCode(ch.charCodeAt(0));
}

function isLowerCaseLetter(ch)
{
  return isLowerCaseLetterCode(ch.charCodeAt(0));
}

function isLetter(ch)
{
  return isLetterCode(ch.charCodeAt(0));
}

function isDigit(ch)
{
  return isDigitCode(ch.charCodeAt(0));
}

function isAlphanumeric(ch)
{
  return isAlphanumericCode(ch.charCodeAt(0));
}

function isQuoteChar(ch)
{
  return ch == '"' || ch == "'" || ch == '`' || ch == '�';
}

function isWhitespace(ch)
{
  return ch == ' ' || ch == '\n' || ch == '\t' || ch == '\r';
}

// ///////////////////////////////////////////////////////////////////////////////////////
// check strings of digits or letters:

function isAllDigits(str)
{
  // check str:
  if (!isValidString(str)) {
    return false;
  }
  // check that each character is a digit:
  for ( var j = 0; j < str.length; j++) {
    if (!isDigitCode(str.charCodeAt(j))) {
      return false;
    }
  }
  // all ok:
  return true;
}

function isAllLetters(str)
{
  // check str:
  if (!isValidString(str)) {
    return false;
  }
  // check that each character is a letter:
  for ( var j = 0; j < str.length; j++) {
    if (!isLetterCode(str.charCodeAt(j))) {
      return false;
    }
  }
  // all ok:
  return true;
}

function isAllAlphanumeric(str)
{
  // check str:
  if (!isValidString(str)) {
    return false;
  }
  // check that each character is a letter:
  var ch;
  for ( var j = 0; j < str.length; j++) {
    ch = str.charCodeAt(j);
    if (!isDigitCode(ch) && !isLetterCode(ch)) {
      return false;
    }
  }
  // all ok:
  return true;
}

function quote_replace(str)
{
  return str_replace("'", "''", str);
}

function uniformBreakTags(str)
{
  // replaces all versions of break tags with <br />
  str = str_replace('<BR />', '<br />', str);
  str = str_replace('<BR>', '<br />', str);
  str = str_replace('<br>', '<br />', str);
  return str;
}

/**
 * Removes html tags from a string.
 * 
 * @param string str
 * @return string
 * 
 * @todo this fn should use regex
 */
function stripHtmlTags(str)
{
  // check str:
  if (!isValidString(str)) {
    return false;
  }
  var intag = false;
  var ch;
  var result = '';
  for ( var j = 0; j < str.length; j++) {
    ch = str.charAt(j);
    if (!intag && ch == '<') {
      intag = true;
    } else if (intag && ch == '>') {
      intag = false;
    } else if (!intag) {
      result += ch;
    }
  }
  return result;
}

function buildPhoneNumberString(phHome, phWork, phMobile)
{
  // make a string for the phone numbers:
  phoneNumbers = '';
  if (phHome != "") {
    if (phoneNumbers != '') {
      phoneNumbers += ', ';
    }
    phoneNumbers += "H: " + phHome;
  }
  if (phWork != "") {
    if (phoneNumbers != '') {
      phoneNumbers += ', ';
    }
    phoneNumbers += "W: " + phWork;
  }
  if (phMobile != "") {
    if (phoneNumbers != '') {
      phoneNumbers += ', ';
    }
    phoneNumbers += "M: " + phMobile;
  }
  return phoneNumbers;
}

function getExtension(filename)
{
  var dotpos = filename.lastIndexOf(".");
  if (dotpos == -1) {
    return "";
  }
  return filename.substr(dotpos + 1);
}

function attributeString(attribs)
{
  var result = "";
  var value;
  if (typeof attribs == 'object') {
    for (var attrib in attribs) {
      if (attribs.hasOwnProperty(attrib)) {
        value = attribs[attrib];
        result += " " + attrib + "='" + htmlspecialchars(value, ENT_QUOTES) + "'";
      }
    }
  }
  return result;
}

function nl2commas(str) {
  str = str_replace('\r\n', '\n', str);
  str = str_replace('\r', '\n', str);
  str = str_replace(',\n', ', ', str);
  str = str_replace('\n', ', ', str);
  return str;
}

/**
 * Replaces the 5 main XML/HTML syntactical characters with their HTML character entity equivalents.
 * 
 * Similar to the PHP function of the same name, except that '&apos;' is used instead of '&#039;',
 * single-quotes are converted, and the optional parameters in the PHP function aren't supported.
 * 
 * @param str string
 * @return string
 */
function htmlspecialchars(str) {
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').
    replace(/'/g, '&apos;');
}

/**
 * Reverse of htmlspecialchars().
 * 
 * Similar to the PHP function of the same name, except that '&apos;' is used instead of '&#039;',
 * single-quotes are converted, and the optional parameters in the PHP function aren't supported.
 * 
 * @param str string
 * @return string
 */
function htmlspecialchars_decode(str) {
  return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').
    replace(/&apos;/g, "'");
} 

/**
 * Escapes special regular expression characters.
 * Mimics the PHP function of the same name.
 *
 * @param str string
 * @return string
 */
function quotemeta(str) {
  return str.replace(/\./g, '\\.').replace(/\\/g, '\\\\').replace(/\+/g, '\\+').replace(/\*/g, '\\*').
    replace(/\?/g, '\\?').replace(/\[/g, '\\[').replace(/\^/g, '\\^').replace(/\]/g, '\\]').
    replace(/\(/g, '\\(').replace(/\$/g, '\\$').replace(/\)/g, '\\)');
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Augment the String prototype:

String.prototype.trim = function() {
  return this.replace(/^\s+|\s+$/g,"");
};

String.prototype.ltrim = function() {
  return this.replace(/^\s+/,"");
};

String.prototype.rtrim = function() {
  return this.replace(/\s+$/,"");
};
