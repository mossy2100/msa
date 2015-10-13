var $ = jQuery;
var db_find_replace_current_match = [];

$(function() {
  
  // Resize the textareas to match the HTML areas:
  $('.db-find-replace-html').each(function() {
    // Get the dimensions of the display text:
    var h = $(this).height();
    var w = $(this).width();
    
    // Update the size of the textarea:
    var n = this.id.substr('db-find-replace-html-'.length);
    $('#edit-modified-' + n).height(h).width(w);
    
    // Add highlighting to HTML:
    db_find_replace_update_display_text(n);

    // Go to the first match:
    db_find_replace_highlight_match(n, 0);
  });
  
  // Edit button behaviour:
  $('.db-find-replace-edit').click(function() {
    var n = this.id.substr('db-find-replace-edit-'.length);
    db_find_replace_edit_mode(n);
  });
  
  // Click on display HTML:
  $('.db-find-replace-html').click(function() {
    var n = this.id.substr('db-find-replace-html-'.length);
    db_find_replace_edit_mode(n);
  });
  
  // Done button behaviour:
  $('.db-find-replace-done').click(function() {
    var n = this.id.substr('db-find-replace-done-'.length);
    db_find_replace_display_mode(n);
  });

  // Click off textarea:
  $('.db-find-replace-result textarea.form-textarea').blur(function() {
    var n = this.id.substr('edit-modified-'.length);
    db_find_replace_display_mode(n);
  });

  // Next button behaviour:
  $('.db-find-replace-next').click(function() {
    var n = this.id.substr('db-find-replace-next-'.length);
    db_find_replace_highlight_next_match(n);
  });
  
  // "Replace one" button behaviour:
  $('.db-find-replace-one').click(function() {
    var n = this.id.substr('db-find-replace-one-'.length);
    db_find_replace_replace_one(n);
  });
  
  // "Replace all" button behaviour:
  $('.db-find-replace-all').click(function() {
    var n = this.id.substr('db-find-replace-all-'.length);
    db_find_replace_replace_all(n);
  });

  // Undo button behaviour:
  $('.db-find-replace-undo').click(function() {
    var n = this.id.substr('db-find-replace-undo-'.length);
    var original_text = $('input:hidden[name=original-' + n + ']').val();

    // Update the textarea and display text:
    $('#edit-modified-' + n).val(original_text);
    db_find_replace_update_display_text(n);
    
    // Go to the first match:
    db_find_replace_highlight_match(n, 0);
  });
  
});

/**
 * Switch to edit mode.
 */
function db_find_replace_edit_mode(n) {
  // Hide the display text:
  $('#db-find-replace-html-' + n).css('display', 'none');

  // Display the edit field and give it focus:
  $('#edit-modified-' + n).css('display', 'block').focus();

  // Hide the Edit buttons:
  $('#db-find-replace-edit-' + n).hide();
  $('#db-find-replace-next-' + n).hide();
  $('#db-find-replace-one-' + n).hide();
  $('#db-find-replace-all-' + n).hide();
  $('#db-find-replace-undo-' + n).hide();

  // Show the Done button:
  $('#db-find-replace-done-' + n).show();
}

/**
 * Show the display text and hide the textarea.
 */
function db_find_replace_display_mode(n) {
  // Hide the textarea:
  $('#edit-modified-' + n).css('display', 'none');

  // Update and show the display text:
  db_find_replace_update_display_text(n);
  $('#db-find-replace-html-' + n).css('display', 'block');

  // Hide the Done buttons:
  $('#db-find-replace-done-' + n).hide();

  // Show the Edit buttons:
  $('#db-find-replace-edit-' + n).show();
  $('#db-find-replace-next-' + n).show();
  $('#db-find-replace-one-' + n).show();
  $('#db-find-replace-all-' + n).show();
  $('#db-find-replace-undo-' + n).show();
}

/**
 * Convert a search string to a PCRE (Perl-Compatible Regular Expression).
 * 
 * @param string $pattern
 * @param bool $case_sensitive
 * @return string
 */
function db_find_replace_string_to_regex(str) {
  var settings = Drupal.settings.db_find_replace;
  
  // Escape special characters:
  var pattern = quotemeta(str);

  // Determine flags:
  var flags = 'g';
  if (!settings.case_sensitive) {
    flags += 'i';
  }

  return new RegExp(pattern, flags);
}

/**
 * Update the display text from the textarea.
 */
function db_find_replace_update_display_text(n) {
  var settings = Drupal.settings.db_find_replace;

  var modified_textarea = $('#edit-modified-' + n);
  var modified_text = modified_textarea.val();
  var modified_html = htmlspecialchars(modified_text);

  // Wrap find text in span tags:
  var find_html = htmlspecialchars(settings.find_text);
  var find_rx = db_find_replace_string_to_regex(find_html);
  modified_html = modified_html.replace(find_rx, "<span class='db-find-replace-match db-find-replace-find'>$&</span>");
  
  // Wrap replace text in span tags:
  var replace_html = htmlspecialchars(settings.replace_text);
  var replace_rx = db_find_replace_string_to_regex(replace_html);
  modified_html = modified_html.replace(replace_rx, "<span class='db-replace-replace-match db-find-replace-replace'>$&</span>");
  
  // Update the HTML:
  $('#db-find-replace-html-' + n).html(modified_html);
}

/**
 * Highlight a match.
 */
function db_find_replace_highlight_match(n, m) {
  var settings = Drupal.settings.db_find_replace;
  if (!settings.do_replace) {
    return;
  }
  
  // Remove highlight:
  $('#db-find-replace-html-' + n + ' .db-find-replace-highlight').removeClass('db-find-replace-highlight');
  
  // Get the find matches:
  var find_matches = $('#db-find-replace-html-' + n + ' .db-find-replace-find');
  if (find_matches.length == 0) {
    m = undefined;
  }
  else if (m >= find_matches.length) {
    m = 0;
  }

  // Highlight the match:
  if (m !== undefined) {
    find_matches.eq(m).addClass('db-find-replace-highlight');
    // Scroll the display text to the highlighted match:
    var top = $('#db-find-replace-html-' + n).scrollTop() + $('#db-find-replace-html-' + n + ' .db-find-replace-highlight').position().top;
    $('#db-find-replace-html-' + n).scrollTop(top);
  }
  
  // Update current match:
  db_find_replace_current_match[n] = m;
}

/**
 * Highlight the current match.
 */
function db_find_replace_highlight_current_match(n) {
  var m = db_find_replace_current_match[n];
  if (m !== undefined) {
    db_find_replace_highlight_match(n, m);
  }
}

/**
 * Highlight the next match.
 */
function db_find_replace_highlight_next_match(n) {
  var m = db_find_replace_current_match[n];
  if (m !== undefined) {
    db_find_replace_highlight_match(n, m + 1);
  }
}

/**
 * Replace the current match.
 */
function db_find_replace_replace_one(n) {
  // Get the replacement text as html:
  var replace_html = htmlspecialchars(Drupal.settings.db_find_replace.replace_text);
  // Do the replacement and update CSS:
  $('#db-find-replace-html-' + n + ' .db-find-replace-highlight').text(replace_html).
    removeClass('db-find-replace-highlight').removeClass('db-find-replace-find').addClass('db-find-replace-replace');
  // Copy changes to the textarea:
  db_find_replace_update_textarea(n);
  // Highlight the next match:
  db_find_replace_highlight_current_match(n);
}

/**
 * Replace all matches.
 */
function db_find_replace_replace_all(n) {
  // Get the replacement text:
  var replace_html = htmlspecialchars(Drupal.settings.db_find_replace.replace_text);
  // Change find matches to replace matches:
  $('#db-find-replace-html-' + n + ' .db-find-replace-find').text(replace_html).
    removeClass('db-find-replace-find').addClass('db-find-replace-replace');
  // Remove highlight:
  $('#db-find-replace-html-' + n + ' .db-find-replace-highlight').removeClass('db-find-replace-highlight');
  // Copy changes to the textarea:
  db_find_replace_update_textarea(n);
  // No current match:
  db_find_replace_current_match[n] = undefined;
}

/**
 * Update the textarea from the display text:
 */
function db_find_replace_update_textarea(n) {
  // Clone the display element:
  var tmp = $('#db-find-replace-html-' + n).clone();
  // Remove the span tags:
  tmp.find('.db-find-replace-match').each(function() {
    $(this).replaceWith($(this).text());
  });
  // Update the textarea:
  $('#edit-modified-' + n).val(htmlspecialchars_decode(tmp.text()));
}
