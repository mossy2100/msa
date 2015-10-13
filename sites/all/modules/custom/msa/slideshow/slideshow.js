var slides;
var n_slides;
var current_slide;
var slideshow_width;
var slideshow_x;

var slide_width = 520;
var slide_height = 180;
var slide_wait_time = 5000; // ms
var slide_move_time = 500;

var playing = true;
var timeout;

/**
 * Go to the next slide.
 */
function next_slide(force) {
  clearTimeout(timeout);
  
  current_slide++;
  if (current_slide == n_slides) {
    current_slide = 0;
  }

  // Get the far right hand slide ready:
  var right_hand_slide = current_slide + 2;
  if (right_hand_slide >= n_slides) {
    right_hand_slide -= n_slides;
  }
  var right_hand_slide_x = slideshow_x + (3 * slide_width);
  slides.eq(right_hand_slide).css({left: right_hand_slide_x + 'px'});
  
  // Slide the slides left:
  slides.animate({
    left: '-=' + slide_width
  }, slide_move_time);
  
  // If playing, set timer to go to the next slide:
  if (playing) {
    timeout = setTimeout(next_slide, slide_wait_time);
  }
}

/**
 * Go to the previous slide.
 */
function prev_slide(force) {
  clearTimeout(timeout);

  current_slide--;
  if (current_slide < 0) {
    current_slide = n_slides - 1;
  }

  // Get the far left hand slide ready:
  var left_hand_slide = current_slide - 2;
  if (left_hand_slide < 0) {
    left_hand_slide += n_slides;
  }
  var left_hand_slide_x = slideshow_x - slide_width;
  slides.eq(left_hand_slide).css({left: left_hand_slide_x + 'px'});
  
  // Slide the slides left:
  slides.animate({
    left: '+=' + slide_width
  }, slide_move_time);

  // If playing, set timer to go to the next slide:
  if (playing) {
    timeout = setTimeout(next_slide, slide_wait_time);
  }
}

/**
 * Play/pause the slideshow.
 */
function play_slideshow() {
  if (playing) {
    // Pause the slideshow:
    playing = false;
    // Cancel any scheduled slide change:
    clearTimeout(timeout);
    // Change the button to play:
    jQuery('#slideshow-pause').addClass('slideshow-play');
  }
  else {
    // Play the slideshow:
    playing = true;
    // Change the button to pause:
    jQuery('#slideshow-pause').removeClass('slideshow-play');
    // Schedule the next slide change:
    timeout = setTimeout(next_slide, slide_wait_time);
  }
}

/**
 * Update the slide positions.
 */
function position_slides() {
  slideshow_width = jQuery('#slideshow').width();
  slideshow_x = Math.round((slideshow_width - slide_width) / 2) - slide_width;
  
  // Set initial positions of slides:
  var j;
  for (var i = 0; i < n_slides; i++) {
    j = i - current_slide;
    if (j < 0) {
      j += n_slides;
    }
    slides.eq(i).css({top: 0, left: slideshow_x + (j * slide_width) + 'px'});
  }
}


function init_slideshow() {
  // Initialise slides:
  slides = jQuery('#slideshow a');
  n_slides = slides.length;
  current_slide = 0;

  position_slides();
  jQuery(window).resize(position_slides);

  // Schedule first slide change:
  timeout = setTimeout(next_slide, slide_wait_time);
}

jQuery(init_slideshow);
